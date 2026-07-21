<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: application.php
 * Description: Handles the code associated with the application form in the tcb plugin.
 */

add_shortcode( 'tcbp_public_edit_application', 'tcbp_public_edit_application' );

/**
 * Determines whether a user already has an application awaiting initial review. Candidates are
 * allowed to re-apply, but only one open "Submission phase" application at a time - once a
 * Recruitment Manager moves it on to interview/candidate/recruit/selection, or closes it out as
 * archived/rejected/retired, the user is free to submit a new one.
 *
 * @param string $profile_id The ACF user profile ID (e.g. "user_123").
 * @return bool True if the user has an application still in the Submission phase.
 */
function tcbp_public_has_pending_application( $profile_id ) {
	$post_id = get_field( 'application', $profile_id );
	if ( ! $post_id ) {
		return false;
	}

	// A deleted application is moved to Trash rather than removed outright, and its terms stay
	// attached while it sits there - without this check a trashed (or otherwise hard-deleted)
	// application would still read as "pending" via the stale post ID left on the profile.
	$status = get_post_status( $post_id );
	if ( ! $status || 'trash' === $status ) {
		return false;
	}

	return (bool) has_term( 'Submission phase', 'tcb-selection', $post_id );
}

/**
 * Determines whether a user is permanently barred from submitting a new application - unlike
 * the Submission-phase check, this doesn't clear on its own once the application is resolved
 * (or even deleted): a "banned" application is a deliberate, standing decision, not a
 * transient in-review state, so it isn't given the same trash/deleted escape hatch as
 * tcbp_public_has_pending_application().
 *
 * Note this only looks at the single application currently linked from the user's profile
 * (the "application" field), matching how tcbp_public_has_pending_application() works - if a
 * user has re-applied since the banned application, and the profile link has moved on to the
 * newer post, that older banned post won't be found here.
 *
 * @param string $profile_id The ACF user profile ID (e.g. "user_123").
 * @return bool True if the user is banned from applying.
 */
function tcbp_public_user_is_banned_from_applying( $profile_id ) {
	$post_id = get_field( 'application', $profile_id );
	if ( ! $post_id ) {
		return false;
	}
	return (bool) has_term( 'banned', 'tcb-selection', $post_id );
}

/**
 * Shortcode to allow editing of user profile.
 */
function tcbp_public_edit_application() {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id    = $user->ID;
	$profile_id = 'user_' . $user_id;

	// Check Steam ID.
	$steam_id       = false;
	$saved_steam_id = get_field( 'steam_id', $profile_id );
	if ( $saved_steam_id ) {
		$steam_info = tcb_roster_get_steam_user_info( $saved_steam_id );
		if ( $steam_info ) {
			$steam_id = $steam_info['SteamId'];
		}
	}

	ob_start();

	echo '<div class="tcb_edit_application">';

	// tcbp_public_submit_application_action() sets this transient once a submission of this
	// form has actually completed. ACFE's own success/render state can't be trusted here (see
	// tcbp_public_submit_application_action() docblock) - it appears to process the submission
	// via a separate internal request, so a $GLOBALS flag doesn't survive to this render pass.
	// The transient does, since it's stored in the database rather than process memory.
	$tcbp_transient_key  = 'tcbp_app_submitted_' . $user_id;
	$tcbp_just_submitted = (bool) get_transient( $tcbp_transient_key );

	if ( $tcbp_just_submitted ) {
		delete_transient( $tcbp_transient_key );
		echo '<div id="message" class="updated">
			<p>Thank you for submitting an application to join 3CB.</p>
			<p>A Recruitment Manager will be in contact via Discord.</p>
			<p>If you have not already done so, please join the <a href="https://discord.gg/5pCCQf9jPQ">3CB Discord</a></p>.
		</div>';
	} elseif ( tcbp_public_user_is_banned_from_applying( $profile_id ) ) {
		// A display-only gate, not the real enforcement - see tcbp_public_validate_application_submission()
		// for the server-side check that actually blocks the submission.
		echo '<div id="message" class="error">
			<p>You are not permitted to submit an application.</p>
		</div>';
	} elseif ( tcbp_public_has_pending_application( $profile_id ) ) {
		// A display-only gate, not the real enforcement - see tcbp_public_validate_application_submission()
		// for the server-side check that actually blocks a second Submission-phase application.
		echo '<div id="message" class="updated">
			<p>You already have an application awaiting initial review.</p>
			<p>A Recruitment Manager will be in contact via Discord.</p>
		</div>';
	} else {
		acfe_form(
			array(
				'name'         => 'submit-application',
				'map'          => array(
					'field_6365c195143e6' => array( 'value' => get_the_author_meta( 'first_name', $user_id ) ),
					'field_6365c23b143e9' => array( 'value' => get_field( 'discord_username', $profile_id ) ),
					'field_67bb543da97fc' => array( 'value' => get_the_author_meta( 'user_email', $user_id ) ),
					'field_67e82b57d2cd7' => array( 'value' => $steam_id ),
					'field_6365c24d143ea' => array( 'value' => get_field( 'user-location', $profile_id ) ),
				),
			)
		);
	}

	echo '</div>';

	return ob_get_clean();
}

add_action( 'acfe/form/validation/form=submit-application', 'tcbp_public_validate_application_submission' );

/**
 * Rejects a new application submission while the user already has one awaiting initial review.
 * acfe/form/validation fires before ACFE creates/saves the post, so this is the actual
 * enforcement - the render-time check in tcbp_public_edit_application() only avoids showing the
 * form to begin with, and can't stop a forged/replayed direct POST on its own.
 */
function tcbp_public_validate_application_submission() {

	$user = wp_get_current_user();

	if ( ! $user->exists() || ! is_user_logged_in() ) {
		return;
	}

	$profile_id = 'user_' . $user->ID;

	if ( tcbp_public_user_is_banned_from_applying( $profile_id ) ) {
		acf_add_validation_error( '', 'You are not permitted to submit an application.' );
		return;
	}

	if ( tcbp_public_has_pending_application( $profile_id ) ) {
		acf_add_validation_error( '', 'You already have an application awaiting initial review. A Recruitment Manager will be in contact via Discord.' );
	}
}

add_action( 'acfe/form/submit_post/form=submit-application', 'tcbp_public_submit_application_action', 20, 1 );

/**
 * Handles the submission callback for the public application form.
 *
 * ACF Extended's own success/render state for this form isn't reliable (it can report the
 * actions as done via its hooks while still re-rendering the empty form to the visitor), so
 * this sets a short-lived transient that tcbp_public_edit_application() uses to override the
 * rendered output directly rather than trusting ACFE's success flag. A transient (not a
 * $GLOBALS flag) is required because ACFE appears to run this submission pipeline via a
 * separate internal request/process from the one that renders the page.
 *
 * @param int $post_id_ The ID of the post being processed.
 */
function tcbp_public_submit_application_action( $post_id_ ) {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id    = $user->ID;
	$profile_id = 'user_' . $user_id;

	update_field( 'application', $post_id_, $profile_id );

	// Replace email name with app email if the user's profile does not show evidence that the email has been previously edited.
	if ( ! get_field( 'fe_email', $profile_id ) ) {
		$email     = esc_attr( get_field( 'app_email', $post_id_ ) );
		$user_data = wp_update_user(
			array(
				'ID'         => $user_id,
				'user_email' => $email,
			)
		);
		if ( is_wp_error( $user_data ) ) {
			error_log( 'Failure: Email not updated to: ' . $email . ' for userID ' . $user_id );
		}
	}

	// Replace first name with app first name if the user's profile does not show evidence that the first name has been previously edited.
	if ( ! get_field( 'fe_first_name', $profile_id ) ) {
		update_user_meta( $user_id, 'first_name', get_field( 'app_first_name', $post_id_ ) );
	}

	// Replace location with app location if the user's profile does not show evidence that the location has been previously edited.
	if ( ! get_field( 'fe_user_location', $profile_id ) ) {
		update_field( 'user-location', get_field( 'app_country', $post_id_ ), $profile_id );
	}

	// Check Steam ID in the profile.
	update_field( 'steam_id', get_field( 'app_steam_id', $post_id_ ), $profile_id );

	wp_set_post_terms( $post_id_, 'Submission phase', 'tcb-selection' );

	// Check Discord Username in the profile.
	$discord_username = get_field( 'app_discord_username', $post_id_ );
	update_field( 'discord_username', $discord_username, $profile_id );
	if ( $discord_username ) {
		$discord_id = tcb_roster_admin_query_discord_username( $discord_username );
		if ( $discord_id ) {
			update_field( 'discord_id', $discord_id, $profile_id );

			// DM applicant.
			tcb_roster_admin_post_to_discord_dm( array( $discord_id ), 'Your application has been submitted. A Recruitment Manager will be in contact.' );
		}
	}

	// DM recruitment manager's channel.
	$message  = "{@Recruitment Manager}\nA new application has been submitted by " . $user->display_name . "\n";
	$message .= "\nPlease check the application and update the status <" . home_url( '/application-archive' ) . "> \n";
	$message .= "\nThe applicant's discord ID is " . $discord_username . "\n";
	tcb_roster_admin_post_to_discord_channel( 'recruitment-managers', $message );

	set_transient( 'tcbp_app_submitted_' . $user_id, true, 60 );
}

// add_filter( 'acfe/form/submit/email_args/action=application_form_email', 'tcbp_public_application_form_email', 10, 1 );

/**
 * DEPRECATED: Handles the email after application form submission.
 * Handles the email after application form submission.
 *
 * @param int $args Arguments for the application partial.
 */
/*function tcbp_public_application_form_email( $args ) {

	$query = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
		'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'duties',
				'value'   => 'rm',
				'compare' => 'LIKE',
			),
		),
	);

	$list_of_posts = get_posts( $query );
	if ( $list_of_posts ) {
		foreach ( $list_of_posts as $post ) {
			setup_postdata( $post );

			$user_id  = get_field( 'user_id', $post );
			$user     = get_user_by( 'id', $user_id );
			$emails[] = $user->user_email;
		}
		$args['to'] = implode( ', ', $emails );
	}

	wp_reset_postdata();

	tcb_roster_admin_post_to_discord_channel( 'recruitment-managers', '{@Recruitment Manager}' . $args['subject'] );

	// Find and replace the tag with a link to the application.
	// $contents = explode ('XXXXX', $args['content'] );
	// if (count($contents) == 3) {
	// $args['content'] = $contents[0] . '<a href="'. home_url() .'/application/' . $contents[1] . '">Authorize application</a>' . $contents[2];
	// }.

	return $args;
}*/

add_shortcode( 'tcbp_public_edit_app_interview', 'tcbp_public_edit_app_interview' );

/**
 * Called buy a shortcode to edit the status portion of the ribbons form.
 */
function tcbp_public_edit_app_interview() {

	$allowed_roles = array( 'recruit_admin', 'snco', 'officer', 'administrator' );
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
	$post_id      = get_field( 'application', $profile_id );

	if ( ! $post_id ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_interview">';
	echo '<h2>' . esc_html( $display_name ) . '</h2>';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_67c439b282575' ),
			'return'          => wp_get_referer(),
			'submit_value'    => 'Updated ' . $display_name . "'s Interview",
			'updated_message' => false,
		)
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $display_name . "'s Commendations" );
	}

	return ob_get_clean();
}
