<?php

function tcb_roster_public_mission_send_announcement ($post_id, $type, $args, $form, $action) {
	
	// Retrieve data
	$message = get_field('message', $post_id);
	$title = get_the_title($post_id);
	$date = tribe_format_date ( tribe_get_start_date($post_id), true, 'D M j @ H:i:s T' );

	$announcement = $title . '\n' . $date . '\n' . $message;

	error_log( print_r("Announcement: " . $announcement, TRUE ));

	//tcb_roster_admin_post_to_discord ( 'Mission Bot', 'announcements', $announcement );
}
