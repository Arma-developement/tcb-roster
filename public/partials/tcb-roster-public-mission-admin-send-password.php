<?php

function tcb_roster_public_mission_send_password($postId, $type, $args, $form, $action) {
	
	function signup_early($postId, $userId, $thresholdTime) {
		$fields = get_field('time_stamp', $postId);
		if (!$fields)
			return false;
		foreach ($fields as $field)
			if ($userId == $field['user'])
				return $field['time'] < $thresholdTime;
		return false;
	}
		
	function send_email($listOfUserIDs, $password) {
		$msg = "\nThe password for today's 3CB Operation is: " . $password . "\n";
		foreach ($listOfUserIDs as $userId) {
			$user = get_user_by('id', $userId);
			$email = $user->user_email;
			mail($user->user_email, "3CB Operation password", $msg);
		}
	}
		
	// Retrieve data
	$password = get_field('password', $postId);
	$delay = get_field('delay', $postId);

	// Set the threshold 24 hours previous
	$dateTime = new DateTimeImmutable(); 
	$dateTime = $dateTime->sub(new DateInterval('P1D'));
	$thresholdTime = $dateTime->getTimestamp();
		
	$earlyEmail = [];
	$lateEmail = [];
	while( have_rows('rsvp', $postId) ) : the_row();
		$i = get_row_index();
		$users = get_sub_field('user');
		if (!$users)
			continue;
		
		foreach ($users as $memberName) {
			$user = get_user_by('login', $memberName);
			$userId = $user ? $user->ID : 1;
			// Add to early list if signed up as attending and early
			if (($i == 1) && signup_early($postId, $userId, $thresholdTime)) 
				$earlyEmail[] = $userId;
			else 
				$lateEmail[] = $userId;
		}
	endwhile;
	
	send_email($earlyEmail, $password);
	sleep ($delay);
	send_email($lateEmail, $password);
}
