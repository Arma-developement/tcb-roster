<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: service-record.php
 * Description: Handles the code associated with the service-record form in the tcb plugin.
 */

// tcb-rank / tcb-duty term IDs. Role derivation below matches against these fixed IDs rather
// than a term's name or slug, because both are editable by anyone with the manage_categories
// capability (WordPress's default Editor role included) - matching by name/slug would let a
// renamed term retroactively change what WP role it grants, without the post/term relationship
// ever changing. A term's ID can't be altered this way, only its display text.
define( 'TCBP_RANK_RESERVE', 56 );
define( 'TCBP_RANK_RECRUIT', 55 );
define( 'TCBP_RANK_MARINE', 53 );
define( 'TCBP_RANK_LANCE_CORPORAL', 52 );
define( 'TCBP_RANK_CORPORAL', 51 );
define( 'TCBP_RANK_SERGEANT', 57 );
define( 'TCBP_RANK_COLOUR_SERGEANT', 50 );
define( 'TCBP_RANK_OFFICER', 54 );

define( 'TCBP_DUTY_RM', 59 );
define( 'TCBP_DUTY_RTI', 60 );
define( 'TCBP_DUTY_ATI', 61 );
define( 'TCBP_DUTY_OM', 58 );
define( 'TCBP_DUTY_CM', 62 );

/**
 * Creates a service record for a user if they don't already have one - linking it via the
 * "service_record"/"user_id" ACF fields and promoting their WP role from subscriber to
 * limited_member. Extracted from tcbp_public_sr_form()'s manual "Create Service Record" flow so
 * the same creation logic can also run automatically from a genuine authenticated state change
 * (e.g. an application reaching Archived, via tcbp_public_sr_promote_to_marine()) - safe to call
 * without an extra confirmation step in that context, unlike tcbp_public_sr_form()'s own
 * nonce-gated button, because it's triggered by an already-authenticated form save rather than a
 * bare page GET a forged link could trigger.
 *
 * @param int $user_id The user to create a service record for.
 * @return int The service record post ID (existing or newly created), or 0 on failure.
 */
function tcbp_public_sr_create_if_missing( $user_id ) {

	$profile_id = 'user_' . $user_id;
	$post_id    = get_field( 'service_record', $profile_id );

	// The referenced post ID can go stale if the service record was deleted after being
	// linked - WordPress doesn't clean up unrelated postmeta (like this profile field)
	// pointing at a deleted post. Trusting a stale ID here meant this returned early thinking
	// a record already existed, so neither a new one got created nor (since the role
	// promotion below only ever runs as part of that creation) was the user promoted to
	// limited_member - same check tcbp_public_has_pending_application() (application.php)
	// already does for the equivalent stale-application-reference case.
	if ( $post_id ) {
		$status = get_post_status( $post_id );
		if ( $status && 'trash' !== $status && 'service-record' === get_post_type( $post_id ) ) {
			return $post_id;
		}
	}

	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return 0;
	}

	$display_name = $user->get( 'display_name' );
	$page_slug    = 'service-record-' . $user_id;

	if ( get_page_by_path( $page_slug, OBJECT, 'service-record' ) ) {
		// A page with this slug already exists but isn't linked from the profile - don't
		// create a duplicate; leave it for a human to sort out.
		return 0;
	}

	$new_page = array(
		'post_type'    => 'service-record',
		'post_title'   => $display_name . "'s Service Record",
		'post_content' => 'Test Page Content',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_name'    => $page_slug,
	);

	$post_id = wp_insert_post( $new_page );
	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	update_field( 'service_record', $post_id, $profile_id );
	update_field( 'user_id', $user_id, $post_id );

	$user->remove_role( 'subscriber' );
	$user->add_role( 'limited_member' );

	return $post_id;
}

/**
 * Fully promotes a user to Marine and notifies them, regardless of which of the two routes
 * triggered it: their application reaching Archived
 * (tcbp_public_application_stage_notifications(), application-notifications.php), or their
 * tcb-rank being set directly to Marine on the service record
 * (tcbp_public_edit_sr_info_submission_callback() below). Each route calls this, and it brings
 * both sides into sync - the application's tcb-selection status, and the WP role/tcb-rank - so
 * it doesn't matter which one happened first, then fires the Marine congratulations message
 * exactly once (tcbp_public_notify_once(), application-notifications.php, keyed on the service
 * record so it's shared regardless of entry route).
 *
 * @param int $user_id           The user being promoted.
 * @param int $service_record_id Their service record post ID.
 */
function tcbp_public_sr_promote_to_marine( $user_id, $service_record_id ) {

	if ( ! $user_id || ! $service_record_id ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return;
	}

	// Sync the application side - archive it if it isn't already, so both routes converge on
	// the same end state regardless of which one was used to get here. Matched by term ID (not
	// name/slug ambiguity) - see tcbp_public_interview_transition_status() (application.php)
	// for why.
	$application_id = get_field( 'application', 'user_' . $user_id );
	if ( $application_id && ! has_term( 'archived', 'tcb-selection', $application_id ) ) {
		$term = get_term_by( 'slug', 'archived', 'tcb-selection' );
		if ( $term && ! is_wp_error( $term ) ) {
			wp_set_post_terms( $application_id, array( (int) $term->term_id ), 'tcb-selection' );
		}
	}

	// Sync the rank/role side - idempotent regardless of which one (if either) is already set,
	// since this may be running as a consequence of the rank having just been set directly (in
	// which case tcbp_public_sr_assign_role_by_rank() already set the role, and this is a
	// harmless no-op). Matched by TCBP_RANK_MARINE (term ID), same reasoning as the constants
	// defined at the top of this file.
	if ( in_array( 'limited_member', $user->roles, true ) ) {
		$user->remove_role( 'limited_member' );
		$user->add_role( 'member' );
	}
	if ( ! has_term( TCBP_RANK_MARINE, 'tcb-rank', $service_record_id ) ) {
		wp_set_post_terms( $service_record_id, array( TCBP_RANK_MARINE ), 'tcb-rank' );
	}

	tcbp_public_notify_once(
		$service_record_id,
		'_tcbp_notified_marine',
		function () use ( $user_id ) {
			$onboarding_url    = home_url( '/information-centre/marine-onboarding' );
			$applicant_message = "Congratulations, you're now a Marine!\n\nPlease follow the onboarding instructions: " . $onboarding_url;
			tcbp_public_notify_user_by_preference( $user_id, $applicant_message, '3CB Application - Marine', $applicant_message );
		}
	);
}

add_shortcode( 'tcbp_public_sr_form', 'tcbp_public_sr_form' );

/**
 * Called buy a shortcode to display the service record form. Assumes a service record already
 * exists - see tcbp_public_sr_create_if_missing() for how one gets created automatically.
 * Data is preloaded and 3 separate ACF groups are used to manage the form.
 */
function tcbp_public_sr_form() {

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$user_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	$display_name = $user->get( 'display_name' );
	$profile_id   = 'user_' . $user_id;
	$post_id      = get_field( 'service_record', $profile_id );

	$allowed_roles = array( 'recruit_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	ob_start();

	echo '<h2>' . esc_html( $display_name ) . '</h2>';

	// A service record should already exist by the time this page is reached - it's now
	// created automatically as soon as an applicant reaches the Recruit stage (or, for the
	// enrol-existing-member path, Archived/Marine directly) - see
	// tcbp_public_sr_create_if_missing(), called from application-notifications.php. The
	// manual "Create Service Record" nonce-confirmed button that used to live here has been
	// removed along with it; if a record genuinely doesn't exist yet, there's nothing to edit.
	if ( ! $post_id ) {
		echo '<p>No service record exists yet for ' . esc_html( $display_name ) . '.</p>';
		return ob_get_clean();
	}

	echo '<div class="tcb_service_record_form">';
	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_635697195a971', 'group_6356984d2ce21', 'group_6356980addb3c' ),
			'submit_value'    => 'Update ' . $display_name . "'s Service Record",
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);
	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $display_name . "'s Service Record" );
	}

	return ob_get_clean();
}


add_shortcode( 'tcbp_public_edit_sr_info', 'tcbp_public_edit_sr_info' );

/**
 * Called buy a shortcode to edit the status portion of the service record form.
 */
function tcbp_public_edit_sr_info() {

	$allowed_roles           = array( 'officer', 'administrator', 'snco', 'recruit_admin' );
	$officer_roles           = array( 'officer', 'administrator' );
	$requires_officer_rights = array( 'officer', 'snco', 'nco' );
	$current_user_roles      = wp_get_current_user()->roles;

	if ( ! array_intersect( $allowed_roles, $current_user_roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$user_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	ob_start();

	// Early out for no user.
	if ( ! $user ) {
		echo '<p>Error: Selected user does not exist ' . esc_attr( $user_id ) . '</p>';
		return ob_get_clean();
	}

	$display_name = $user->get( 'display_name' );
	$profile_id   = 'user_' . $user_id;
	$post_id_     = get_field( 'service_record', $profile_id );

	if ( ! $post_id_ ) {
		echo '<p>profile ' . esc_attr( $profile_id ) . '</p>';
		echo '<p>Error: No service record ' . esc_attr( $post_id_ ) . '</p>';
		return ob_get_clean();
	}

	if ( ! array_intersect( $officer_roles, $current_user_roles ) ) {
		if ( array_intersect( $requires_officer_rights, $user->roles ) ) {
			echo '<p class="negative">Error: Not authorised to edit ' . esc_attr( $display_name ) . "'s service record</p>";
			return ob_get_clean();
		}
	}

	echo '<div class="tcb_edit_status">';

	acf_form(
		array(
			'post_id'         => $post_id_,
			'field_groups'    => array( 'group_635697195a971' ),
			'submit_value'    => 'Update ' . $display_name . "'s User Info",
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $display_name . "'s Service Record" );
	}

	return ob_get_clean();
}


add_action( 'acf/validate_save_post', 'tcbp_public_sr_validate_rank_submission' );

/**
 * Rejects an SR info submission outright if a non-officer tries to set one of the leadership
 * ranks, instead of letting ACF write the tcb-rank term and only skipping the role-sync
 * afterwards. acf/validate_save_post runs before any save happens, so acf_add_validation_error()
 * here stops the write entirely rather than needing to correct it after the fact.
 */
function tcbp_public_sr_validate_rank_submission() {

	$rank_field_key = 'field_67b8e1472f7bb';

	if ( empty( $_POST['acf'][ $rank_field_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$submitted_term_id = (int) sanitize_text_field( wp_unslash( $_POST['acf'][ $rank_field_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// Matched by term_id, not name - a term's name/slug can be renamed by anyone with
	// manage_categories (WordPress's default Editor role included), which would otherwise let a
	// renamed low-rank term retroactively read as "Officer" and slip through both this check and
	// tcbp_public_sr_assign_role_by_rank()'s role grant, without the post/term relationship ever
	// actually changing.
	$requires_officer_rights = array( TCBP_RANK_LANCE_CORPORAL, TCBP_RANK_CORPORAL, TCBP_RANK_SERGEANT, TCBP_RANK_COLOUR_SERGEANT, TCBP_RANK_OFFICER );
	if ( ! in_array( $submitted_term_id, $requires_officer_rights, true ) ) {
		return;
	}

	$officer_roles = array( 'officer', 'administrator' );
	if ( array_intersect( $officer_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	// Only block an actual attempted change, not an unchanged resubmission of the existing
	// rank - HTML forms resubmit every field's current value regardless of whether it was
	// edited. Compared by term_id, not name, for the same reason as above. If the target post
	// can't be determined, fail safe and reject anyway.
	$post_id = isset( $_POST['_acf_post_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['_acf_post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( $post_id ) {
		$current_terms   = get_the_terms( $post_id, 'tcb-rank' );
		$current_term_id = ( $current_terms && ! is_wp_error( $current_terms ) && isset( $current_terms[0] ) ) ? (int) $current_terms[0]->term_id : 0;
		if ( $current_term_id === $submitted_term_id ) {
			return;
		}
	}

	acf_add_validation_error( 'acf[' . $rank_field_key . ']', 'Only an officer can set this rank.' );
}

add_action( 'acf/validate_save_post', 'tcbp_public_sr_validate_duty_submission' );

/**
 * Rejects an SR info submission outright if a non-officer tries to set a duty term, instead of
 * letting ACF write the tcb-duty term and only skipping the role-sync afterwards. Same issue and
 * same fix as tcbp_public_sr_validate_rank_submission() above.
 */
function tcbp_public_sr_validate_duty_submission() {

	$duty_field_key = 'field_6365a88ca963d';

	if ( empty( $_POST['acf'][ $duty_field_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$officer_roles = array( 'officer', 'administrator' );
	if ( array_intersect( $officer_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	// Only block an actual attempted change, not an unchanged resubmission of the existing
	// duties - HTML forms resubmit every field's current value regardless of whether it was
	// edited. If the target post can't be determined, fail safe and reject anyway.
	$post_id = isset( $_POST['_acf_post_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['_acf_post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( $post_id ) {
		$submitted_duty_ids = wp_parse_id_list( wp_unslash( $_POST['acf'][ $duty_field_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$current_duty_terms = get_the_terms( $post_id, 'tcb-duty' );
		$current_duty_ids   = ( $current_duty_terms && ! is_wp_error( $current_duty_terms ) ) ? wp_parse_id_list( wp_list_pluck( $current_duty_terms, 'term_id' ) ) : array();
		sort( $submitted_duty_ids );
		sort( $current_duty_ids );
		if ( $submitted_duty_ids === $current_duty_ids ) {
			return;
		}
	}

	acf_add_validation_error( 'acf[' . $duty_field_key . ']', 'Only an officer can set duties.' );
}

add_action( 'acf/save_post', 'tcbp_public_edit_sr_info_submission_callback', 20, 1 );

/**
 * Callback function for editing the SR information.
 *
 * @param int $post_id_ The ID of the post being processed.
 */
function tcbp_public_edit_sr_info_submission_callback( $post_id_ ) {

	// Only set for post_type = post!
	if ( 'service-record' !== get_post_type( $post_id_ ) ) {
		return;
	}

	$user_id = get_field( 'user_id', $post_id_ );

	tcbp_public_sr_check_sr_name( $user_id, $post_id_ );
	tcbp_public_sr_assign_role_by_rank( $user_id, $post_id_ );
	tcbp_public_sr_assign_role_by_duty( $user_id, $post_id_ );

	// Setting rank directly to Marine here is the second of the two routes to becoming a
	// Marine (the other being an application reaching Archived) - keep both routes in sync,
	// see tcbp_public_sr_promote_to_marine() above.
	if ( has_term( TCBP_RANK_MARINE, 'tcb-rank', $post_id_ ) ) {
		tcbp_public_sr_promote_to_marine( $user_id, $post_id_ );
	}
}


add_shortcode( 'tcbp_public_edit_sr_training', 'tcbp_public_edit_sr_training' );

/**
 * Called buy a shortcode to edit the status portion of the training form.
 */
function tcbp_public_edit_sr_training() {

	$allowed_roles = array( 'training_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$user_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	$display_name = $user->get( 'display_name' );
	$profile_id   = 'user_' . $user_id;
	$post_id      = get_field( 'service_record', $profile_id );

	if ( ! $post_id ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_training">';
	echo '<h2>' . esc_html( $display_name ) . '</h2>';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_6356984d2ce21' ),
			'return'          => wp_get_referer(),
			'submit_value'    => 'Update ' . $display_name . "'s Training Record",
			'updated_message' => false,
		),
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $display_name . "'s Training Record" );
	}

	return ob_get_clean();
}


add_shortcode( 'tcbp_public_edit_sr_ribbons', 'tcbp_public_edit_sr_ribbons' );

/**
 * Called buy a shortcode to edit the status portion of the ribbons form.
 */
function tcbp_public_edit_sr_ribbons() {

	$allowed_roles = array( 'commendation_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$user_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	$display_name = $user->get( 'display_name' );
	$profile_id   = 'user_' . $user_id;
	$post_id      = get_field( 'service_record', $profile_id );

	if ( ! $post_id ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_ribbons">';
	echo '<h2>' . esc_html( $display_name ) . '</h2>';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_6356980addb3c' ),
			'return'          => wp_get_referer(),
			'submit_value'    => 'Update ' . $display_name . "'s Commendations",
			'updated_message' => false,
		)
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $display_name . "'s Commendations" );
	}

	return ob_get_clean();
}


/**
 * Utility function to ensure SR has the correct name.
 *
 * @param int $user_id The user id containing the service record information.
 * @param int $post_id_ The post id of the service record.
 */
function tcbp_public_sr_check_sr_name( $user_id, $post_id_ ) {

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	$display_name = $user->get( 'display_name' );

	$update = array(
		'ID'         => $post_id_,
		'post_title' => $display_name . "'s Service Record",
	);

	wp_update_post( $update );
}



/**
 * Utility function to demote a user to Subscriber.
 *
 * @param int $user_id The user id containing the service record information.
 * @param int $post_id_ The post id of the service record.
 */
function tcbp_public_sr_check_demotion_to_subscriber( $user_id, $post_id_ ) {

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	// Filter out any users who are actually banned
	$application_id = get_field( 'application', 'user_' . $user_id );
	if ( in_array( 'banned', $user->roles, true ) ) {
		if ( $application_id ) {
			wp_set_post_terms( $application_id, 'banned', 'tcb-selection' );
		}
		return;
	} 

	// Re-verify the user's application has actually been rejected, rather than trusting the
	// caller - this strips the user's WP roles down to subscriber, so it shouldn't rely solely
	// on whatever gate happens to sit in front of it today.
	if ( ! $application_id || ! has_term( 'rejected', 'tcb-selection', $application_id ) ) {
		return;
	}

	$found = false;
	$roles = $user->roles;
	foreach ( $roles as $role ) {
		if ( 'subscriber' === $role ) {
			$found = true;
			continue;
		}
		$user->remove_role( $role );
	}

	if ( ! $found ) {
		$user->add_role( 'subscriber' );

		if ( $post_id_ ) {
			wp_set_post_terms( $post_id_, '', 'tcb-rank' );
		}
	}
}


/**
 * Utility function to assign a user role based on rank.
 *
 * @param int $user_id The user id containing the service record information.
 * @param int $post_id_ The post id of the service record.
 */
function tcbp_public_sr_assign_role_by_rank( $user_id, $post_id_ ) {
	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	// Early out no rank.
	$terms = get_the_terms( $post_id_, 'tcb-rank' );
	if ( ! $terms || ! $terms[0] ) {
		return;
	}
	// Matched by term_id, not name - see tcbp_public_sr_validate_rank_submission() for why: a
	// term's name/slug can be renamed by anyone with manage_categories (WordPress's default
	// Editor role included), which would otherwise let a renamed term grant whatever role its
	// new name happens to match.
	$rank_term_id = (int) $terms[0]->term_id;

	$officer_roles           = array( 'officer', 'administrator' );
	$requires_officer_rights = array( TCBP_RANK_LANCE_CORPORAL, TCBP_RANK_CORPORAL, TCBP_RANK_SERGEANT, TCBP_RANK_COLOUR_SERGEANT, TCBP_RANK_OFFICER );
	$current_user_roles      = wp_get_current_user()->roles;

	// Check if user has the required role to promote
	if ( ! array_intersect( $officer_roles, $current_user_roles ) ) {
		if ( in_array( $rank_term_id, $requires_officer_rights, true ) ) {
			return;
		}
	}

	$all_roles = array( 'subscriber', 'limited_member', 'member', 'nco', 'snco', 'officer' );

	switch ( $rank_term_id ) {
		case TCBP_RANK_RESERVE:
			$allowed_roles = array( 'member' );
			array_push( $all_roles, 'editor' );
			break;
		case TCBP_RANK_RECRUIT:
			$allowed_roles = array( 'limited_member' );
			array_push( $all_roles, 'editor' );
			break;
		case TCBP_RANK_MARINE:
			$allowed_roles = array( 'member' );
			array_push( $all_roles, 'editor' );
			break;
		case TCBP_RANK_LANCE_CORPORAL:
		case TCBP_RANK_CORPORAL:
			$allowed_roles = array( 'nco', 'member' );
			array_push( $all_roles, 'editor' );
			break;
		case TCBP_RANK_SERGEANT:
		case TCBP_RANK_COLOUR_SERGEANT:
			$allowed_roles = array( 'snco', 'member' );
			break;
		case TCBP_RANK_OFFICER:
			$allowed_roles = array( 'officer', 'member' );
			break;
		default:
			$allowed_roles = array( 'subscriber' );
			array_push( $all_roles, 'editor' );
			break;
	}

	// Remove all rank related roles.
	$roles = $user->roles;
	foreach ( $roles as $role ) {
		if ( in_array( $role, $all_roles, true ) ) {
			$user->remove_role( $role );
		}
	}

	// Add new rank related roles.
	foreach ( $allowed_roles as $role ) {
		$user->add_role( $role );
	}
}


/**
 * Utility function to assign a user role based on rank.
 *
 * @param int $user_id The user id containing the service record information.
 * @param int $post_id_ The post id of the service record.
 */
function tcbp_public_sr_assign_role_by_duty( $user_id, $post_id_ ) {
	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	$officer_roles           = array( 'officer', 'administrator' );
	$current_user_roles      = wp_get_current_user()->roles;

	// Check if user has the required role to set duties
	if ( ! array_intersect( $officer_roles, $current_user_roles ) ) {
		return;
	}

	$allowed_roles = array();
	$terms         = get_the_terms( $post_id_, 'tcb-duty' );
	if ( $terms ) {
		foreach ( $terms as $term ) {
			// Matched by term_id, not slug - a term's slug is editable by anyone with
			// manage_categories (WordPress's default Editor role included), which would
			// otherwise let a renamed duty term grant whatever role its new slug happens to
			// match.
			switch ( (int) $term->term_id ) {
				case TCBP_DUTY_RM:
					array_push( $allowed_roles, 'recruit_admin' );
					break;
				case TCBP_DUTY_RTI:
				case TCBP_DUTY_ATI:
					array_push( $allowed_roles, 'training_admin' );
					break;
				case TCBP_DUTY_OM:
					array_push( $allowed_roles, 'mission_admin' );
					break;
				case TCBP_DUTY_CM:
					array_push( $allowed_roles, 'commendation_admin' );
					break;
				default:
					break;
			}
		}
	}

	$all_roles = array( 'recruit_admin', 'training_admin', 'mission_admin', 'commendation_admin' );

	// Remove all duty related roles.
	$roles = $user->roles;
	foreach ( $roles as $role ) {
		if ( in_array( $role, $all_roles, true ) ) {
			$user->remove_role( $role );
		}
	}

	// Add new duty related roles.
	foreach ( $allowed_roles as $role ) {
		$user->add_role( $role );
	}
}
