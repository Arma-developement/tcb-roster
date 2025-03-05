<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to handle attendance roster updates.
 */
function tcb_roster_public_slotting_tool_update() {

	/**
	 * Function to handle attendance roster updates.
	 */
	function do_work() {
		if ( ! isset( $_POST['postId'] ) || ! isset( $_POST['userId'] ) || ! isset( $_POST['slot'] ) || ! isset( $_POST['nounce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return wp_send_json_error( 'Parameters missing' );
		}

		$post_id  = (int) sanitize_text_field( wp_unslash( $_POST['postId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$user_id  = (int) sanitize_text_field( wp_unslash( $_POST['userId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$slot_str = sanitize_text_field( wp_unslash( $_POST['slot'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nounce   = sanitize_text_field( wp_unslash( $_POST['nounce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Security check.
		if ( ! wp_verify_nonce( $nounce, 'attendance_slotting_update_nounce' ) ) {
			return wp_send_json_error( 'Nounce failed' );
		}

		// Convert the slot string to an array of ints.
		$slot_array = explode( ',', $slot_str );
		$i          = (int) $slot_array[0];
		$j          = (int) $slot_array[1];
		$k          = (int) $slot_array[2];

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// error_log( print_r( 'i: ' . $i, true ) );
		// error_log( print_r( 'j: ' . $j, true ) );
		// error_log( print_r( 'k: ' . $k, true ) );
		// .

		// Check if the user is already slotted.
		$user                = get_user_by( 'id', $user_id );
		$slotted_member_name = $user->user_login;
		$already_slotted     = tcb_roster_public_find_user_in_slotting( $post_id, $slotted_member_name );

		// Retrieve the user at the specific location.
		$fields           = get_field( 'slots', $post_id );
		$slot_index_array = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );

		// 1 subtracted to compensate for ACF rows starting at 1, whilst arrays start at 0.
		$old_slotted_member_name = $fields[ $i - 1 ]['unit'][ $j - 1 ]['slot'][ $k - 1 ]['slot_member'];
		$old_user                = get_user_by( 'login', $old_slotted_member_name );
		$old_user_id             = $old_user ? $old_user->ID : 1;

		if ( $old_user ) {
			if ( $old_user_id === $user_id ) {
				// Delete user.
				if ( update_sub_field( $slot_index_array, '', $post_id ) ) {
					return wp_send_json_success( 'Removed user ' . $old_slotted_member_name . ', ' . $i . ', ' . $j . ', ' . $k );
				} else {
					return wp_send_json_error( 'Removed user ' . $old_slotted_member_name . ', ' . $i . ', ' . $j . ', ' . $k );
				}
			} else {
				// Do nothing, slot already taken.
				return wp_send_json_error( 'Slot already taken by ' . $old_slotted_member_name );
			}
		} elseif ( $already_slotted ) {
				// Do nothing, already slotted.
				return wp_send_json_error( 'Existing user ' . $slotted_member_name );
		} elseif ( update_sub_field( $slot_index_array, $slotted_member_name, $post_id ) ) {
			// Add to the rvsp as attending.
			tcbp_public_attendance_register_user( $post_id, $user_id, 1, false );
			return wp_send_json_success( 'Added user ' . $slotted_member_name . ', ' . $i . ', ' . $j . ', ' . $k );
		} else {
			return wp_send_json_error( 'Added user ' . $slotted_member_name . ', ' . $i . ', ' . $j . ', ' . $k );
		}
	}

	do_work();
	wp_die();
}

/**
 * Utility function to find if a user is slotted the slotting tool.
 *
 * @param int    $post_id The post ID for the slotting tool.
 * @param string $user_login The login name of the user.
 */
function tcb_roster_public_find_user_in_slotting( $post_id, $user_login ) {
	while ( have_rows( 'slots', $post_id ) ) :
		the_row();
		if ( ! have_rows( 'unit', $post_id ) ) {
			continue;
		}
		while ( have_rows( 'unit', $post_id ) ) :
			the_row();
			if ( ! have_rows( 'slot', $post_id ) ) {
				continue;
			}
			while ( have_rows( 'slot', $post_id ) ) :
				the_row();
				if ( get_sub_field( 'slot_member' ) === $user_login ) {
					return true;
				}
			endwhile;
		endwhile;
	endwhile;
	return false;
}

/**
 * Updates the slotting tool with the provided user data.
 *
 * @param int   $post_id The post ID for the slotting tool.
 * @param array $user An associative array containing user data to be updated.
 */
function tcb_roster_public_remove_from_slotting( $post_id, $user ) {
	while ( have_rows( 'slots', $post_id ) ) :
		the_row();
		$i = get_row_index();
		if ( ! have_rows( 'unit', $post_id ) ) {
			continue;
		}
		while ( have_rows( 'unit', $post_id ) ) :
			the_row();
			$j = get_row_index();
			if ( ! have_rows( 'slot', $post_id ) ) {
				continue;
			}
			while ( have_rows( 'slot', $post_id ) ) :
				the_row();
				$k = get_row_index();
				if ( get_sub_field( 'slot_member' ) === $user ) {
					$slot_index_array = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );
					update_sub_field( $slot_index_array, '', $post_id );
					return true;
				}
			endwhile;
		endwhile;
	endwhile;
	return false;
}
