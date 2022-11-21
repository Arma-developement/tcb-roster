<?php

function tcb_roster_public_slotting_tool_update() {

	function do_work() {
		$post_id = $_POST['postId'];
		$user_id = $_POST['userId'];
		$slotStr = $_POST['slot'];

		// Security check
		$nounce = $_POST['nounce'];
		if ( !wp_verify_nonce( $nounce, "attendance_slotting_update_nounce"))
			return wp_send_json_error('Nounce failed');

		// Convert the slot string to an array of ints
		$slotArray = array_map('intval', explode(',', $slotStr));
		$i = $slotArray[0]; $j = $slotArray[1]; $k = $slotArray[2];
		//$slotIndexArray = json_decode ($slotStr);

		// Retrieve the user at the specific location
		$fields = get_field('slots', $post_id);
		$slotIndexArray = array('slots',$i,'unit',$j,'slot',$k,'slot_member');
		$slottedMemberName = $fields[$i]['unit'][$j]['slot'][$k]['slot_member'];

		$oldUser = get_user_by('login', $slottedMemberName);
		if ($oldUser) {
			$oldUser_id = $oldUser->ID;

			if ($oldUser_id == $user_id) {
				// Delete user
				update_sub_field ($slotIndexArray, '', $post_id);
				return wp_send_json_success('Removed user ' . $user_id);
			}
			else {
				// Do nothing, slot already taken
				return wp_send_json_success('Slot already taken by ' . $oldUser_id);
			}
		} else {
			// Add user
			$user = get_user_by('id', $user_id);

			update_sub_field ($slotIndexArray, $user->user_login, $post_id );
			return wp_send_json_success('Added user ' . $user_id . ' ' . $i. ' ' . $j. ' ' . $k);
		}
	}

	do_work();
	wp_die();
}
