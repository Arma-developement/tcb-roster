<?php

function tcb_roster_public_mission_send_announcement_discord ($announcement) {
	
	error_log( print_r("Announcement: " . $announcement, TRUE ));
	
	// tcb_roster_admin_post_to_discord ( 'Mission Bot', 'announcements', $announcement );
}

function tcb_roster_public_mission_send_announcement ($post_id, $type, $args, $form, $action) {
	
	// Retrieve data
	$message = get_field('message', $post_id);
	$schedule = get_field('schedule', $post_id);

	//error_log( print_r("schedule: " . json_encode($schedule), TRUE ));

	// Build message
	$title = get_the_title($post_id);
	$startTimeStr = tribe_get_start_date($post_id, true, DateTimeInterface::RFC850);
	$announcement = $title . '\n' . $startTimeStr . '\n' . $message;

	//error_log( print_r("startTimeStr: " . $startTimeStr, TRUE ));

	// Schedule the announcements
	$currentTime = new DateTimeImmutable();
	$startTime = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC850, $startTimeStr);

	//error_log( print_r("startTime: " . json_encode($startTime), TRUE ));
	//error_log( print_r("currentTime: " . json_encode($currentTime), TRUE ));

	if (in_array( 'now', $schedule)) {
		as_enqueue_async_action('tcb_roster_public_mission_send_announcement_discord_action', array($announcement));
	}

	if (in_array( 'hour', $schedule)) {
		$scheduleTime = $startTime->sub(new DateInterval('PT1H'));
		if ($currentTime < $scheduleTime) {
			as_schedule_single_action( DateTime::createFromImmutable($scheduleTime), 'tcb_roster_public_mission_send_announcement_discord_action', array($announcement));
		}
	}

	if (in_array( 'day', $schedule)) {
		$scheduleTime = $startTime->sub(new DateInterval('P1D'));
		if ($currentTime < $scheduleTime) {
			as_schedule_single_action( DateTime::createFromImmutable($scheduleTime), 'tcb_roster_public_mission_send_announcement_discord_action', array($announcement));
		}
	}

	if (in_array( 'week', $schedule)) {
		$scheduleTime = $startTime->sub(new DateInterval('P7D'));
		if ($currentTime < $scheduleTime) {
			as_schedule_single_action( DateTime::createFromImmutable($scheduleTime), 'tcb_roster_public_mission_send_announcement_discord_action', array($announcement));
		}
	}
}
