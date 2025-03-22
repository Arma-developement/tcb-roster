<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: service-record.php
 * Description: Handles the code associated with the service-record form in the tcb plugin.
 */

add_shortcode( 'tcbp_public_sr_form', 'tcbp_public_sr_form' );

/**
 * Called buy a shortcode to display the service record form.
 * This function will create a new service record post if one does not exist.
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

	// Create a new page if one does not exist.
	if ( ! $post_id ) {

		$page_slug = 'service-record-' . $user_id; // Slug of the Post.
		$new_page  = array(
			'post_type'    => 'service-record',
			'post_title'   => $display_name . "'s Service Record",
			'post_content' => 'Test Page Content',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_name'    => $page_slug,
		);

		if ( ! get_page_by_path( $page_slug, OBJECT, 'service-record' ) ) { // Check If Page Not Exits.
			$post_id = wp_insert_post( $new_page );
			update_field( 'service_record', $post_id, $profile_id );
			update_field( 'user_id', $user_id, $post_id ); // Required to link service record to user.

			// Update the user's roles.
			$user->remove_role( 'subscriber' );
			$user->add_role( 'limited_member' );
		}
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
 * Utility function to promote a user to Marine.
 *
 * @param int $user_id The user id containing the service record information.
 * @param int $post_id_ The post id of the service record.
 */
function tcbp_public_sr_check_promotion_to_marine( $user_id, $post_id_ ) {

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	if ( in_array( 'limited_member', $user->roles, true ) ) {
		$user->remove_role( 'limited_member' );
		$user->add_role( 'member' );

		wp_set_post_terms( $post_id_, 'Marine', 'tcb-rank' );
	}
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
	$rank_name = $terms[0]->name;

	$officer_roles           = array( 'officer', 'administrator' );
	$requires_officer_rights = array( 'Lance Corporal', 'Corporal', 'Sergeant', 'Colour Sergeant', 'Officer' );
	$current_user_roles      = wp_get_current_user()->roles;

	// Check if user has the required role to promote, if not then default to Marine.
	if ( ! array_intersect( $officer_roles, $current_user_roles ) ) {
		if ( in_array( $rank_name, $requires_officer_rights, true ) ) {
			$rank_name = 'Marine';
			wp_set_post_terms( $post_id_, $rank_name, 'tcb-rank' );
		}
	}

	$all_roles = array( 'subscriber', 'limited_member', 'member', 'nco', 'snco', 'officer' );

	switch ( $rank_name ) {
		case 'Reserve':
			$allowed_roles = array( 'member' );
			array_push( $all_roles, 'editor' );
			break;
		case 'Recruit':
			$allowed_roles = array( 'limited_member' );
			array_push( $all_roles, 'editor' );
			break;
		case 'Marine':
			$allowed_roles = array( 'member' );
			array_push( $all_roles, 'editor' );
			break;
		case 'Lance Corporal':
		case 'Corporal':
			$allowed_roles = array( 'nco', 'member' );
			array_push( $all_roles, 'editor' );
			break;
		case 'Sergeant':
		case 'Colour Sergeant':
			$allowed_roles = array( 'snco', 'member' );
			break;
		case 'Officer':
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

	$allowed_roles = array();
	$terms         = get_the_terms( $post_id_, 'tcb-duty' );
	if ( $terms ) {
		foreach ( $terms as $term ) {
			switch ( $term->slug ) {
				case 'rm':
					array_push( $allowed_roles, 'recruit_admin' );
					break;
				case 'rti':
				case 'ati':
					array_push( $allowed_roles, 'training_admin' );
					break;
				case 'om':
					array_push( $allowed_roles, 'mission_admin' );
					break;
				case 'cm':
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
