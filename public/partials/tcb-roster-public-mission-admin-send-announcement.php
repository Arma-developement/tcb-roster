<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Sends an announcement to the mission admin.
 *
 * @param string $announcement The announcement message to be sent.
 */
function tcb_roster_public_mission_send_announcement_discord( $announcement ) {

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// error_log( print_r( 'Announcement: ' . $announcement, true ) );
	// .

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// tcb_roster_admin_post_to_discord ( 'Mission Bot', 'announcements', $announcement );
	// .
}

/**
 * Sends an announcement for a mission.
 *
 * @param int $post_id The ID of the post.
 */
function tcb_roster_public_mission_send_announcement( $post_id ) {

	// Retrieve data.
	$message  = get_field( 'message', $post_id );
	$schedule = get_field( 'schedule', $post_id );

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// error_log( print_r("schedule: " . json_encode($schedule), TRUE ));
	// .

	// Build message.
	$title          = get_the_title( $post_id );
	$start_time_str = tribe_get_start_date( $post_id, true, DateTimeInterface::RFC850 );
	$announcement   = $title . '\n' . $start_time_str . '\n' . $message;

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// error_log( print_r("start_time_str: " . $start_time_str, TRUE ));
	// .

	// Schedule the announcements.
	$current_time = new DateTimeImmutable();
	$start_time   = DateTimeImmutable::createFromFormat( DateTimeInterface::RFC850, $start_time_str );

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// error_log( print_r("start_time: " . json_encode($start_time), TRUE ));
	// error_log( print_r("current_time: " . json_encode($current_time), TRUE ));
	// .

	if ( in_array( 'now', $schedule, true ) ) {
		as_enqueue_async_action( 'tcb_roster_public_mission_send_announcement_discord_action', array( $announcement ) );
	}

	if ( in_array( 'hour', $schedule, true ) ) {
		$schedule_time = $start_time->sub( new DateInterval( 'PT1H' ) );
		if ( $current_time < $schedule_time ) {
			as_schedule_single_action( DateTime::createFromImmutable( $schedule_time ), 'tcb_roster_public_mission_send_announcement_discord_action', array( $announcement ) );
		}
	}

	if ( in_array( 'day', $schedule, true ) ) {
		$schedule_time = $start_time->sub( new DateInterval( 'P1D' ) );
		if ( $current_time < $schedule_time ) {
			as_schedule_single_action( DateTime::createFromImmutable( $schedule_time ), 'tcb_roster_public_mission_send_announcement_discord_action', array( $announcement ) );
		}
	}

	if ( in_array( 'week', $schedule, true ) ) {
		$schedule_time = $start_time->sub( new DateInterval( 'P7D' ) );
		if ( $current_time < $schedule_time ) {
			as_schedule_single_action( DateTime::createFromImmutable( $schedule_time ), 'tcb_roster_public_mission_send_announcement_discord_action', array( $announcement ) );
		}
	}
}
