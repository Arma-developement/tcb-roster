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
 * Gets the user currently occupying a specific slot position.
 *
 * @param int $post_id The post ID for the slotting tool.
 * @param int $i       The slots row index.
 * @param int $j       The unit row index.
 * @param int $k       The slot row index.
 * @return int|string The user ID occupying the slot, or an empty string if unoccupied/not found.
 */
function tcbp_public_slotting_get_slot_member( $post_id, $i, $j, $k ) {
	while ( have_rows( 'slots', $post_id ) ) :
		the_row();
		if ( $i !== get_row_index() ) {
			continue;
		}
		while ( have_rows( 'unit', $post_id ) ) :
			the_row();
			if ( $j !== get_row_index() ) {
				continue;
			}
			while ( have_rows( 'slot', $post_id ) ) :
				the_row();
				if ( $k !== get_row_index() ) {
					continue;
				}
				return get_sub_field( 'slot_member' );
			endwhile;
		endwhile;
	endwhile;
	return '';
}

/**
 * Gets the attendance_threshold configured for a specific slot position - the minimum total
 * attendance count required before that slot can be claimed.
 *
 * @param int $post_id The post ID for the slotting tool.
 * @param int $i       The slots row index.
 * @param int $j       The unit row index.
 * @param int $k       The slot row index.
 * @return int The attendance threshold, or 0 (unlocked) if the slot can't be found.
 */
function tcbp_public_slotting_get_attendance_threshold( $post_id, $i, $j, $k ) {
	while ( have_rows( 'slots', $post_id ) ) :
		the_row();
		if ( $i !== get_row_index() ) {
			continue;
		}
		while ( have_rows( 'unit', $post_id ) ) :
			the_row();
			if ( $j !== get_row_index() ) {
				continue;
			}
			while ( have_rows( 'slot', $post_id ) ) :
				the_row();
				if ( $k !== get_row_index() ) {
					continue;
				}
				return (int) get_sub_field( 'attendance_threshold' );
			endwhile;
		endwhile;
	endwhile;
	return 0;
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
		if ( ! isset( $_POST['postId'] ) || ! isset( $_POST['slot'] ) || ! isset( $_POST['nounce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return wp_send_json_error( 'Parameters missing' );
		}

		$post_id  = (int) sanitize_text_field( wp_unslash( $_POST['postId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$slot_str = sanitize_text_field( wp_unslash( $_POST['slot'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nounce   = sanitize_text_field( wp_unslash( $_POST['nounce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Security check.
		if ( ! wp_verify_nonce( $nounce, 'attendance_slotting_update_nounce' ) ) {
			return wp_send_json_error( 'Nounce failed' );
		}

		// Always act on the currently authenticated user - never trust a client-submitted user ID.
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return wp_send_json_error( 'Not logged in' );
		}

		// The mission page only shows the slotting tool to subscribers when the mission isn't
		// private/miniop/patrolop, but that's a display gate, not an access control - re-check
		// it here so a direct POST can't bypass it.
		$current_user_roles = wp_get_current_user()->roles;
		if ( tcbp_public_mission_is_restricted_for_user( $post_id, $current_user_roles ) ) {
			return wp_send_json_error( 'Not authorized for this mission' );
		}

		// Convert the slot string to an array of ints. The client's 4th (ownership) value is
		// ignored - it's derived below from the slot's actual current occupant, so a request
		// can't be forged to remove or overwrite another member's slot.
		$slot_array = explode( ',', $slot_str );
		$i          = (int) $slot_array[0];
		$j          = (int) $slot_array[1];
		$k          = (int) $slot_array[2];

		// The slotting tool only renders an active claim/release control once total attendance
		// meets the slot's own attendance_threshold - that's a display decision, not an access
		// control, so re-check it here too. Matches the render's is_disabled logic: a locked
		// slot can't be touched at all, even by its current owner.
		$attendance_threshold = tcbp_public_slotting_get_attendance_threshold( $post_id, $i, $j, $k );
		$attendance           = tcbp_public_get_attendance_count( $post_id );
		if ( $attendance < $attendance_threshold ) {
			return wp_send_json_error( 'Slot is locked' );
		}

		$user                = get_user_by( 'id', $user_id );
		$display_name        = $user->display_name;
		$slot_index_array    = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );
		$current_slot_member = tcbp_public_slotting_get_slot_member( $post_id, $i, $j, $k );

		// Refuse to touch a slot already held by someone else.
		if ( $current_slot_member && ( $current_slot_member !== $user_id ) ) {
			return wp_send_json_error( 'Slot already occupied' );
		}

		// If the user is in the current slot, remove the user.
		if ( $current_slot_member === $user_id ) {
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
