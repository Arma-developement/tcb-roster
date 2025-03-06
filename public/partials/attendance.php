<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to remove a user from the attendance roster.
 * The finally param controls if the user is removed from the slotting tool.
 *
 * @param int  $post_id The ID of the post.
 * @param int  $user_id The ID of the user.
 * @param bool $remove_slotting Flag to remove user from slotting tool.
 */
function tcbp_public_attendance_remove_user( $post_id, $user_id, $remove_slotting ) {

	while ( have_rows( 'rsvp', $post_id ) ) :
		the_row();

		// Extract list of users and check if list is empty.
		$user_ids = get_sub_field( 'user' );
		if ( ! $user_ids ) {
			continue;
		}

		// Check if user is in the list.
		if ( ! in_array( $user_id, $user_ids, true ) ) {
			continue;
		}

		// Remove the user from list.
		$update_users = array_filter(
			$user_ids,
			function ( $element ) use ( $user_id ) {
				return $element !== $user_id;
			}
		);

		// Update the database.
		$rc = update_sub_field( array( 'rsvp', get_row_index(), 'user' ), $update_users, $post_id );
		if ( ! $rc ) {
			return wp_send_json_error( 'Failed to update user' );
		}

		// Remove from slotting tool.
		if ( $remove_slotting && ( 1 === get_row_index() ) ) {
			tcb_roster_public_remove_from_slotting( $post_id, $user_id );
		}
		break;
	endwhile;
}

/**
 * Function to register a user on the attendance roster.
 *
 * @param int  $post_id The ID of the post.
 * @param int  $user_id The ID of the user.
 * @param int  $selection The selection data for the roster update.
 * @param bool $remove_slotting Flag to remove user from slotting tool.
 */
function tcbp_public_attendance_register_user( $post_id, $user_id, $selection, $remove_slotting ) {

	$fields = get_field( 'rsvp', $post_id );
	if ( ! $fields ) {
		return wp_send_json_error( 'No fields in post' );
	}

	// Create a list of all registered users across all attendance fields.
	$registered_users = array();
	foreach ( $fields as $field ) {
		if ( $field['user'] ) {
			$registered_users = array_merge( $registered_users, $field['user'] );
		}
	}

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// error_log( print_r( 'user_id: ' . $user_id, true ) );
	// error_log( print_r( 'registered_users: ' . implode( ',', $registered_users ), true ) );
	// error_log( print_r( 'remove_slotting: ' . ( $remove_slotting ? 1 : 0 ), true ) );
	// .

	// Check for registered user, removing from old list and adding to new list.
	if ( in_array( $user_id, $registered_users, true ) ) {
		tcbp_public_attendance_remove_user( $post_id, $user_id, $remove_slotting );
		add_sub_row( array( 'rsvp', $selection, 'user' ), $user_id, $post_id );
		return wp_send_json_success( 'Existing user moved' );
	}

	// New user, add to the appropriate list.
	add_sub_row( array( 'rsvp', $selection, 'user' ), $user_id, $post_id );

	// Check if user has previously registered.
	$found  = false;
	$fields = get_field( 'stamp', $post_id );
	if ( $fields ) {
		foreach ( $fields as $field ) {
			if ( $field['stamp_user'] === $user_id ) {
				$found = true;
				break;
			}
		}
	}

	// User has previously registered, so use original time stamp.
	if ( $found ) {
		return wp_send_json_success( 'Existing user added' );
	}

	// New user, so add to a new time stamp.
	add_row(
		'stamp',
		array(
			'stamp_user' => $user_id,
			'stamp_date' => gmdate( 'd-m-Y' ),
			'stamp_time' => time(),
		),
		$post_id
	);
	return wp_send_json_success( 'New user added' );
}

// Important: These labels must match the function below, and also include "wp_ajax_".
add_action( 'wp_ajax_tcbp_public_attendance_update', 'tcbp_public_attendance_update' );

/**
 * Function to handle attendance roster updates.
 * Called from AJAX, when a user registers or unregisters from an event.
 */
function tcbp_public_attendance_update() {

	/**
	 * Function to handle attendance roster updates.
	 */
	function do_work() {
		if ( ! isset( $_POST['postId'] ) || ! isset( $_POST['userId'] ) || ! isset( $_POST['selection'] ) || ! isset( $_POST['unregister'] ) || ! isset( $_POST['nounce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return wp_send_json_error( 'Parameters missing' );
		}

		$post_id    = (int) sanitize_text_field( wp_unslash( $_POST['postId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$user_id    = (int) sanitize_text_field( wp_unslash( $_POST['userId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selection  = (int) sanitize_text_field( wp_unslash( $_POST['selection'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$unregister = (int) sanitize_text_field( wp_unslash( $_POST['unregister'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nounce     = sanitize_text_field( wp_unslash( $_POST['nounce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Security check.
		if ( ! wp_verify_nonce( $nounce, 'attendance_roster_update_nounce' ) ) {
			return wp_send_json_error( 'Nounce failed' );
		}

		// Check for unregister, and remove user.
		if ( $unregister ) {
			tcbp_public_attendance_remove_user( $post_id, $user_id, true );
			return wp_send_json_success( 'User unregistered' );
		}

		// Register the user.
		return tcbp_public_attendance_register_user( $post_id, $user_id, $selection, true );
	}

	do_work();
	wp_die();
}
