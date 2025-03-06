<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Utility function to find if a user is slotted the slotting tool.
 *
 * @param int    $post_id The post ID for the slotting tool.
 * @param string $user_id The login name of the user.
 */
function tcbp_public_slotting_find_user( $post_id, $user_id ) {
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
				if ( get_sub_field( 'slot_member' ) === $user_id ) {
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
 * @param array $user_id An associative array containing user data to be updated.
 */
function tcbp_public_slotting_remove_user( $post_id, $user_id ) {
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
				if ( get_sub_field( 'slot_member' ) === $user_id ) {
					$slot_index_array = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );
					update_sub_field( $slot_index_array, '', $post_id );
					return true;
				}
			endwhile;
		endwhile;
	endwhile;
	return false;
}

// Important: These labels must match the function below, and also include "wp_ajax_".
add_action( 'wp_ajax_tcbp_public_slotting_update', 'tcbp_public_slotting_update' );

/**
 * Function to handle attendance roster updates.
 */
function tcbp_public_slotting_update() {

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
		$owner      = (bool) $slot_array[3];

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// error_log( print_r( 'i: ' . $i, true ) );
		// error_log( print_r( 'j: ' . $j, true ) );
		// error_log( print_r( 'k: ' . $k, true ) );
		// .

		$user             = get_user_by( 'id', $user_id );
		$display_name     = $user->display_name;
		$slot_index_array = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );

		// If the user is in the current slot, remove the user.
		if ( $owner ) {
			if ( update_sub_field( $slot_index_array, '', $post_id ) ) {
				return wp_send_json_success( 'Removed user ' . $display_name . ', ' . $i . ', ' . $j . ', ' . $k );
			} else {
				return wp_send_json_error( 'Removed user ' . $display_name . ', ' . $i . ', ' . $j . ', ' . $k );
			}
		}

		// Remove the user from all other slots.
		tcbp_public_slotting_remove_user( $post_id, $user_id );

		// Add the user to the current slot.
		if ( update_sub_field( $slot_index_array, $user_id, $post_id ) ) {
			return wp_send_json_success( 'Added user ' . $display_name . ', ' . $i . ', ' . $j . ', ' . $k );
		} else {
			return wp_send_json_error( 'Added user ' . $display_name . ', ' . $i . ', ' . $j . ', ' . $k );
		}
	}

	do_work();
	wp_die();
}
