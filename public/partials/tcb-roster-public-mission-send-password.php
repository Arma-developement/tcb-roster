<?php

function tcb_roster_public_mission_send_password($postId, $password, $delay) {
	
	function signup_early($postId, $userId, $thresholdTime) {
		$fields = get_field('time_stamp', $post_id);
		if (!$fields)
			return false;
		foreach ($fields as $field) {
			if ($userId == $field['user'])
				return $field['time'] < $thresholdTime;
		return false;
	}
		
	// Set the threshold 24 hours previous
	$dateTime = new DateTimeImmutable(); 
	$dateTime = $dateTime->sub(new DateInterval('P1D'));
	$thresholdTime = $dateTime->getTimestamp();
		
	$earlyEmail = [];
	$lateEmail = [];
	while( have_rows('rsvp', $post_id) ) : the_row();
		$i = get_row_index();
		$users = get_sub_field('user');
		if (!$users)
			continue;
		
		foreach ($users as $memberName) {
			$user = get_user_by('login', $memberName);
			$userId = $user->ID;
			// Add to early list if signed up as attending and early
			if (($i == 1) && signup_early($postId, $userId, $thresholdTime)) 
				$earlyEmail[] = $userId;
			else 
				$lateEmail[] = $userId;
		}
	endwhile;
		
	// send early email
	// Wait
	// Send late email
}
