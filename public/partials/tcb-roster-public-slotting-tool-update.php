<?php

function tcb_roster_public_slotting_tool_update() {
	
	function find_user($user, $post_id) {
		while( have_rows('slots', $post_id) ) : the_row();
			if (!have_rows ('unit', $post_id) )
				continue;
			while( have_rows('unit', $post_id) ) : the_row();
				if ( !have_rows('slot', $post_id) )
					continue;
				while( have_rows('slot', $post_id) ) : the_row(); 	
					if (get_sub_field('slot_member') == $user)
						return true;					
				endwhile;
			endwhile;
		endwhile;
		return false;
	}

	function do_work() {
		$post_id = $_POST['postId'];
		$user_id = $_POST['userId'];
		$slotStr = $_POST['slot'];

		// Security check
		$nounce = $_POST['nounce'];
		if ( !wp_verify_nonce( $nounce, "attendance_slotting_update_nounce"))
			return wp_send_json_error('Nounce failed');

		// Convert the slot string to an array of ints
		$slotArray = explode(',', $slotStr);
		$i = (int)$slotArray[0]; $j = (int)$slotArray[1]; $k = (int)$slotArray[2];

		// Check if the user is already slotted
		$user = get_user_by('id', $user_id);
		$slottedMemberName = $user->user_login;
		$alreadySlotted = find_user($slottedMemberName, $post_id);
	
		// Retrieve the user at the specific location
		$fields = get_field('slots', $post_id);
		$slotIndexArray = array('slots',$i+1,'unit',$j+1,'slot',$k+1,'slot_member');
		$oldSlottedMemberName = $fields[$i]['unit'][$j]['slot'][$k]['slot_member'];
		$oldUser = get_user_by('login', $oldSlottedMemberName);
		$oldUser_id = $oldUser->ID;

		if ($oldUser) {
			if ($oldUser_id == $user_id) {
				// Delete user
				if (update_sub_field ($slotIndexArray, '', $post_id))
					return wp_send_json_success('Removed user ' . $oldSlottedMemberName. ', ' . $i. ', ' . $j. ', ' . $k);
				else
					return wp_send_json_error('Removed user ' . $oldSlottedMemberName. ', ' . $i. ', ' . $j. ', ' . $k);
			}
			else {
				// Do nothing, slot already taken
				return wp_send_json_success('Slot already taken by ' . $oldSlottedMemberName);
			}
		} else {
			if ($alreadySlotted) {
				// Do nothing, already slotted
				return wp_send_json_success('Existing user ' . $slottedMemberName);
			}
			else {
				// Add user
				if (update_sub_field ($slotIndexArray, $slottedMemberName, $post_id ))
					return wp_send_json_success('Added user ' . $slottedMemberName . ', ' . $i. ', ' . $j. ', ' . $k);
				else
					return wp_send_json_error('Added user ' . $slottedMemberName . ', ' . $i. ', ' . $j. ', ' . $k);
			}
		}
	}

	do_work();
	wp_die();
}
