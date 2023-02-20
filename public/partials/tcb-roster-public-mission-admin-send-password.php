<?php

function tcb_roster_public_mission_send_password_email($args) {
	$listOfUserIDs = $args[0];
	$password = $args[1];

	// error_log( print_r("send email", TRUE));
	// error_log( print_r("args: " . json_encode($args), TRUE ));
	//error_log( print_r("listOfUserIDs: " . json_encode($listOfUserIDs), TRUE ));
	// error_log( print_r("password: " . $password, TRUE ));

	$msg = "\nThe password for today's 3CB Operation is: " . $password . "\n";
	foreach ($listOfUserIDs as $userId) {
		$user = get_user_by('id', $userId);
		$userProfile = 'user_' . $userId;

		$preference = get_field( 'communication_preference', $userProfile );
		if (!$preference)
			continue;

		error_log( print_r("preference: " . json_encode($preference), TRUE ));

		if (in_array ("discord", $preference)) {
			$discordID = get_field( 'discord_id', $userProfile );
			if ($discordID) {
				tcb_roster_admin_post_to_discordDM ("3CB-Bot", $discordID, $msg);
			}
		}

		if (in_array ("email", $preference)) {
			$email = $user->user_email;
			wp_mail($user->user_email, "3CB Operation password", $msg);
		}
	}
}

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
		
		foreach ($users as $userId) {
			//$userId = get_user_by('login', $memberName);

			// Add to early list if signed up as attending and early
			if (($i == 1) && signup_early($postId, $userId, $thresholdTime)) 
				$earlyEmail[] = $userId;
			else 
				$lateEmail[] = $userId;
		}
	endwhile;

	$now = new DateTimeImmutable ();
	$later = $now->add( new DateInterval('PT' . $delay . 'S') );

	// error_log( print_r("button press", TRUE ));
	error_log( print_r("earlyEmail: " . json_encode($earlyEmail), TRUE ));
	error_log( print_r("lateEmail: " . json_encode($lateEmail), TRUE ));
	// error_log( print_r("delay: " . $delay, TRUE ));
	// error_log( print_r("password: " . $password, TRUE ));
	// error_log( print_r("timeStamp: " . $now->format('H:i:s'), TRUE ));
	// error_log( print_r("timeStamp: " . $later->format('H:i:s'), TRUE ));

	as_enqueue_async_action('tcb_roster_public_mission_send_password_email_action', array(array($earlyEmail, $password)));
	as_schedule_single_action( DateTime::createFromImmutable($later), 'tcb_roster_public_mission_send_password_email_action', array(array($lateEmail, $password)));
}
