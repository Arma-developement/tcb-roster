<?php

function tcb_roster_public_slotting_tool_update() {

	function do_work() {
		$post_id = $_POST['postId'];
		$user_id = $_POST['userId'];
		$slotStr = $_POST['slot'];

		// Security check
		$nounce = $_POST['nounce'];
		if ( ! wp_verify_nonce( $nounce, 'attendance_slotting_update_nounce' ) ) {
			return wp_send_json_error( 'Nounce failed' );
		}

		// Convert the slot string to an array of ints
		$slotArray = explode( ',', $slotStr );
		$i         = (int) $slotArray[0];
		$j         = (int) $slotArray[1];
		$k         = (int) $slotArray[2];

		// Check if the user is already slotted
		$user              = get_user_by( 'id', $user_id );
		$slottedMemberName = $user->user_login;
		$alreadySlotted    = tcb_roster_public_find_user_in_slotting( $post_id, $slottedMemberName );

		// Retrieve the user at the specific location
		$fields         = get_field( 'slots', $post_id );
		$slotIndexArray = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );
		// 1 subtracted to compensate for ACF rows starting at 1, whilst arrays start at 0
		$oldSlottedMemberName = $fields[ $i - 1 ]['unit'][ $j - 1 ]['slot'][ $k - 1 ]['slot_member'];
		$oldUser              = get_user_by( 'login', $oldSlottedMemberName );
		$oldUser_id           = $oldUser ? $oldUser->ID : 1;

		if ( $oldUser ) {
			if ( $oldUser_id === $user_id ) {
				// Delete user
				if ( update_sub_field( $slotIndexArray, '', $post_id ) ) {
					return wp_send_json_success( 'Removed user ' . $oldSlottedMemberName . ', ' . $i . ', ' . $j . ', ' . $k );
				} else {
					return wp_send_json_error( 'Removed user ' . $oldSlottedMemberName . ', ' . $i . ', ' . $j . ', ' . $k );
				}
			} else {
				// Do nothing, slot already taken
				return wp_send_json_error( 'Slot already taken by ' . $oldSlottedMemberName );
			}
		} elseif ( $alreadySlotted ) {
				// Do nothing, already slotted
				return wp_send_json_error( 'Existing user ' . $slottedMemberName );
		} else {
			// Add user
			if ( update_sub_field( $slotIndexArray, $slottedMemberName, $post_id ) ) {
				// Add to the rvsp as attending
				tcb_roster_public_addToRSVP( $post_id, $user_id, 1 );
				return wp_send_json_success( 'Added user ' . $slottedMemberName . ', ' . $i . ', ' . $j . ', ' . $k );
			} else {
				return wp_send_json_error( 'Added user ' . $slottedMemberName . ', ' . $i . ', ' . $j . ', ' . $k );
			}
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

// Called from RSVP tool
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
					$slotIndexArray = array( 'slots', $i, 'unit', $j, 'slot', $k, 'slot_member' );
					update_sub_field( $slotIndexArray, '', $post_id );
					return true;
				}
			endwhile;
		endwhile;
	endwhile;
	return false;
}
