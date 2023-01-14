<?php

function tcb_roster_public_attendance_roster_update() {

	function do_work() {
		$post_id = $_POST['postId'];
		$user_id = $_POST['userId'];
		$selection = $_POST['selection'];

		// Security check
		$nounce = $_POST['nounce'];
		if ( !wp_verify_nonce( $nounce, "attendance_roster_update_nounce"))
			return wp_send_json_error('Nounce failed');

		$registeredUsers = [];
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
		$i = 0;
		$deleteOnly = false;
		while( have_rows('rsvp', $post_id) ) : the_row();
			$i = get_row_index();
			$users = get_sub_field('user');
			if ($users) {
				// Check if user in list
				if (in_array($user_id, $users)) {

					// Check if already registered in this list
					if ($selection == $i)
						$deleteOnly = true;

					// Remove the user
					$update_users = array_filter($users, function ($element) {
						global $user_id;
						return $element == $user_id;
					});
					update_sub_field(array('rsvp', $i, 'user'), $update_users, $post_id);
					break;
				}
			}
		endwhile;

		if (!$deleteOnly) {
			add_sub_row(array('rsvp', $selection, 'user'), $user_id, $post_id);
		}

		// Remove from slotting 
		if ($i == 1) {
			$user = get_user_by('id', $user_id);
			$slottedMemberName = $user->user_login;
			tcb_roster_public_remove_from_slotting($post_id, $slottedMemberName);
		}

		return wp_send_json_success('Existing user updated');
	}

	do_work();
	wp_die();
}

// Called from slotting tool
function  tcb_roster_public_addToRSVP($post_id, $user_id, $selection) {
	$registeredUsers = [];
	$fields = get_field('rsvp', $post_id);
	if (!$fields)
		return;

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
		}
		return;
	}

	// Find and remove user
	$deleteOnly = false;
	while( have_rows('rsvp', $post_id) ) : the_row();
		$i = get_row_index();
		$users = get_sub_field('user');
		if ($users) {
			// Check if user in list
			if (in_array($user_id, $users)) {

				// Check if already registered in this list
				if ($selection == $i)
					return;

				// Remove the user
				$update_users = array_filter($users, function ($element) {
					global $user_id;
					return $element == $user_id;
				});
				update_sub_field(array('rsvp', $i, 'user'), $update_users, $post_id);
				break;
			}
		}
	endwhile;
	add_sub_row(array('rsvp', $selection, 'user'), $user_id, $post_id);
}