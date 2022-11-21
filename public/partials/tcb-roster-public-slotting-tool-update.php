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

		// Retrieve the user at the specific location
		$fields = get_field('slots', $post_id);
		$slottedMemberName = $fields[$slotArray[0]]['unit'][$slotArray[1]]['slot'][$slotArray[2]]['slot_member'];

		$oldUser = get_user_by('login', $slottedMemberName);
		if ($oldUser) {
			$oldUser_id = $oldUser->ID;

			if ($oldUser_id == $user_id) {
				// Delete user
				update_sub_field (array('slots',$slotArray[0],'unit',$slotArray[1],'slot',$slotArray[2],'slot_member'), '', $post_id);
				return wp_send_json_success('Removed user ' . $user_id);
			}
			else {
				// Do nothing, slot already taken
				return wp_send_json_success('Slot already taken by ' . $oldUser_id);
			}
		} else {
			// Add user
			$user = get_user_by('id', $user_id);

			update_sub_field (array('slots',$slotArray[0],'unit',$slotArray[1],'slot',$slotArray[2],'slot_member'), $user->user_login, $post_id );
			return wp_send_json_success('Added user ' . $user_id . ' ' . $slotArray[0]. ' ' . $slotArray[1]. ' ' . $slotArray[2]);
		}

		//$fields = get_fields( $post_id );


		// $post_id = $_POST['post-id'];
		// $memberName = $_POST['member-name'];
		// $acfPath = $_POST['acf-path'];
	
		// Wrangle the array into the right format, thanks Ewan
		// $combined = explode(",", $acfPath);
	
		// Update DB using ACF function
		// update_sub_field($combined, $memberName, $post_id); 

		/*$registeredUsers = [];
		$fields = get_field('rsvp', $post_id);
		if (!$fields)
			return wp_send_json_error('No fields in post');

		foreach ($fields as $field) {
			if ($field['user'])
				$registeredUsers = array_merge($registeredUsers, $field['user']);
		}

		// New user, add to the appropriate list
		if (!in_array($user_id, $registeredUsers)) {
			add_sub_row(array('rsvp', $selection, 'user'), $user_id, $post_id);

			// Check if user has previously registered
			$previousUsers = [];
			$fields = get_field('time_stamp', $post_id);
			if ($fields) {
				foreach ($fields as $field) {
					$previousUsers[] = $field['user'];
				}
			}

			// If a new user, then register the time
			if (!in_array($user_id, $previousUsers)) {
				$date = getdate();
				add_row('time_stamp', array( 'user' => $user_id, 'time' => $date ), $post_id);
				return wp_send_json_success('New user added, with timestamp');
			}

			return wp_send_json_success('New user added');
		}

		// Find and remove user
		$rowIndex = 0;
		$deleteOnly = false;
		while( have_rows('rsvp', $post_id) ) : the_row();
			$users = get_sub_field('user');	
			if ($users) {
				// Check if user in list
				if (in_array($user_id, $users)) {

					// Check if already registered in this list
					if ($selection == $rowIndex)
						$deleteOnly = true;

					// Remove the user
					$update_users = array_filter($users, static function ($element) {
						return $element == $user_id;
					});
					update_sub_field(array('rsvp', $rowIndex, 'user'), $update_users, $post_id);
					break;
				}
			}
			$rowIndex++;
		endwhile;

		if (!$deleteOnly) {
			add_sub_row(array('rsvp', $selection, 'user'), $user_id, $post_id);
		}
		*/
		//return wp_send_json_success('Success ' . $post_id . ', ' . $user_id . ', [' . $slot . ']');
	}

	do_work();
	wp_die();
}
