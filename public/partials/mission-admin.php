<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

add_shortcode( 'tcb_roster_public_mission_admin', 'tcb_roster_public_mission_admin' );

/**
 * Function to handle the mission admin.
 */
function tcb_roster_public_mission_admin() {

	$allowed_roles = array( 'mission_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id_ = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id_ ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_mission_admin">';

	acfe_form(
		array(
			'name'    => 'send-announcement',
			'post_id' => $post_id_,
		)
	);

	acfe_form(
		array(
			'name'    => 'send-password',
			'post_id' => $post_id_,
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title( $post_id_ ) . ' via the Mission Admin Panel' );
	}

	echo '</div>';

	return ob_get_clean();
}

add_shortcode( 'tcb_roster_public_mission_news', 'tcb_roster_public_mission_news' );

/**
 * Function to handle the mission news panel.
 */
function tcb_roster_public_mission_news() {

	$allowed_roles = array( 'mission_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id_ = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id_ ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_mission_news">';

	acfe_form(
		array(
			'name'            => 'submit_mission_news',
			'post_id'         => $post_id_,
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title( $post_id_ ) . ' via the Mission News Panel' );
	}

	echo '</div>';

	return ob_get_clean();
}

add_action( 'tcb_roster_public_mission_send_announcement_discord_action', 'tcbp_public_mission_send_announcement_discord' );

/**
 * Sends an announcement to the mission admin.
 *
 * @param string $announcement The announcement message to be sent.
 */
function tcbp_public_mission_send_announcement_discord( $announcement ) {

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// error_log( print_r( 'Announcement: ' . $announcement, true ) );
	// .

	tcb_roster_admin_post_to_discord_channel( 'announcements', $announcement );
}

add_action( 'acfe/form/submit/post/form=send-announcement', 'tcbp_public_mission_send_announcement', 10, 1 );

/**
 * Sends an announcement for a mission.
 *
 * @param int $post_id The ID of the post.
 */
function tcbp_public_mission_send_announcement( $post_id ) {

	// Retrieve data.
	$message  = get_field( 'message', $post_id );
	$schedule = get_field( 'schedule', $post_id );

	// Build message.
	$title            = get_the_title( $post_id );
	$event_start_date = get_field( 'event_start_date', $post_id ); // Return format: Y-m-d.
	$event_start_time = get_field( 'event_start_time', $post_id ); // Return format: g:i a.
	// Submitted event times are entered as British wall-clock time (BST/GMT); attach the zone
	// explicitly so the derived Unix timestamp is the correct UTC instant, not a naive UTC read.
	$start_time       = DateTimeImmutable::createFromFormat( 'Y-m-d g:i a', $event_start_date . ' ' . $event_start_time, new DateTimeZone( 'Europe/London' ) );

	if ( ! $start_time ) {
		error_log( 'Could not parse event start date/time for mission ' . $post_id );
		return;
	}

	// Fixed UK anchor time, plus Discord's <t:...:t> tag which renders in each reader's own
	// timezone client-side. UK readers will see the same time twice - Discord has no way to
	// vary message content per reader, so that's an accepted, unavoidable trade-off for giving
	// non-UK readers both a stable reference time and their own local time.
	$announcement = "{@members} {@recruits} {@candidate}\n\n" . $title . "\n" . $start_time->format( 'l j F Y, H:i T' ) . "\n(<t:" . $start_time->getTimestamp() . ":t> your local time)\n\n" . $message;

	// Schedule the announcements.
	$current_time = new DateTimeImmutable();

	if ( in_array( 'now', $schedule, true ) ) {
		tcbp_public_mission_send_announcement_discord( $announcement );
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

add_action( 'acfe/form/submit/post/form=submit_mission_news', 'tcbp_public_mission_send_news', 10, 1 );

/**
 * Function to send mission news.
 *
 * @param int $post_id The ID of the post.
 */
function tcbp_public_mission_send_news( $post_id ) {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id = $user->ID;

	// Retrieve data.
	$title = get_field( 'title', $post_id );

	// set_post_thumbnail() needs an attachment ID, not a URL - get_the_post_thumbnail_url() was
	// being used here, which meant the fallback thumbnail could never actually resolve to a
	// real attachment.
	$brief_image             = get_post_thumbnail_id( $post_id );
	$brief_situation         = get_field( 'brief_situation', $post_id );
	$brief_mission           = get_field( 'brief_mission', $post_id );
	$post_op_summary         = get_field( 'post_op_summary', $post_id );
	$post_op_image           = get_field( 'post_op_image', $post_id );
	$post_op_secondary_image = get_field( 'post_op_secondary_image', $post_id );

	// Build content.
	$content = '<h2>Situation</h2>' . $brief_situation . '<h2>Mission</h2>' . $brief_mission;

	if ( '' !== $post_op_summary ) {
		$content .= '<h2>AAR</h2><div>' . $post_op_summary . '</div>';
	}

	// post_op_secondary_image is an ACF Image field - it returns an array (or false/null when
	// empty), never an empty string, so comparing it to '' was always true regardless of whether
	// an image was actually set, producing a broken <img src=""> when it wasn't.
	if ( $post_op_secondary_image ) {
		$content .= '<p><img src="' . esc_url( $post_op_secondary_image['url'] ) . '" ></p>';
	}

	$new_post    = array(
		'post_title'    => 'After Action Report: ' . $title,
		'post_content'  => $content,
		'post_status'   => 'publish',
		'post_author'   => $user_id,
		'post_type'     => 'post',
		'post_category' => array( get_cat_ID( 'After Action Report' ) ),
	);
	$new_post_id = wp_insert_post( $new_post );

	if ( $new_post_id ) {
		// Add post thumbnail. Same issue as post_op_secondary_image above: post_op_image is an
		// ACF Image field, so '' !== $post_op_image was always true - meaning the fallback to
		// the mission's own banner image (brief_image) below was unreachable whenever
		// post_op_image wasn't set, leaving the article with no featured image at all.
		if ( $post_op_image ) {
			$image_id = $post_op_image['ID'];
			if ( $image_id ) {
				set_post_thumbnail( $new_post_id, $image_id );
			}
		} elseif ( ! empty( $brief_image ) ) {
			set_post_thumbnail( $new_post_id, $brief_image );
		}
	}

	// The password Discord thread (group_6a71d24f69be1, field thread_id - see
	// tcbp_public_mission_send_password()) has served its purpose once the mission's news
	// write-up is submitted - clean it up rather than leaving it around indefinitely.
	$thread_id = get_field( 'thread_id', $post_id );
	if ( $thread_id ) {
		tcb_roster_admin_delete_discord_thread( $thread_id );
	}
}

// Hook name kept as-is (despite the rename below) so any password-send already scheduled via
// as_schedule_single_action() under this hook, from before this change, still fires correctly.
add_action( 'tcb_roster_public_mission_send_password_notifications_action', 'tcbp_public_mission_send_password_notifications' );

/**
 * Sends the mission password to each user via their preferred communication method(s) - email,
 * and/or a shared Discord thread. This replaces individually DMing each user, which was hitting
 * Discord's rate limits when messaging many different users in a short window; see
 * tcb_roster_admin_create_discord_thread().
 *
 * Email is identical on both calls. The Discord message differs: the first (early) call mentions
 * each Discord-preferring recipient individually (<@id>), since this is most people's only
 * notification; the second (late) call - for the smaller group who signed up after the early
 * cutoff - just uses a single {@members} role mention instead, rather than re-enumerating names.
 *
 * @param array $args {
 *     @type int[]  0 List of user IDs to notify.
 *     @type string 1 The mission password.
 *     @type string 2 The Discord thread ID to post the Discord notification into.
 *     @type bool   3 True if this is the first (early) call, false for the second (late) call.
 * }
 */
function tcbp_public_mission_send_password_notifications( $args ) {
	$list_of_user_ids = $args[0];
	$password         = $args[1];
	$thread_id        = $args[2];
	$is_first_call    = $args[3];

	$msg             = "\nThe password for today's 3CB Operation is: " . $password . "\n";
	$discord_id_list = array();
	foreach ( $list_of_user_ids as $user_id ) {
		$user    = get_user_by( 'id', $user_id );
		$profile = 'user_' . $user_id;

		$preference = get_field( 'communication_preference', $profile );
		if ( ! $preference ) {
			continue;
		}

		if ( in_array( 'discord', $preference, true ) ) {
			$discord_id = get_field( 'discord_id', $profile );
			if ( $discord_id ) {
				$discord_id_list[] = $discord_id;
			}
		}

		if ( in_array( 'email', $preference, true ) ) {
			$email = $user->user_email;
			wp_mail( $user->user_email, '3CB Operation password', $msg );
		}
	}

	if ( $discord_id_list ) {
		$msg = "\nThe password is:\n`" . $password . "`\n";

		if ( $is_first_call ) {
			// Mention every recipient directly in the shared thread, rather than DMing each one
			// individually. Discord's own mention syntax (<@id>) is required to actually notify
			// them - a plain "@id" is just literal text and won't ping anyone.
			$mentions = '';
			foreach ( $discord_id_list as $discord_id ) {
				$mentions .= '<@' . $discord_id . '> ';
			}
			tcb_roster_admin_post_to_discord_channel( $thread_id, $mentions . $msg );
		} else {
			// Second wave - a single role mention rather than individually naming this smaller,
			// later group.
			tcb_roster_admin_post_to_discord_channel( $thread_id, '{@members} ' . $msg );
		}
	}
}

add_action( 'acfe/form/submit/post/form=send-password', 'tcbp_public_mission_send_password', 10, 5 );

/**
 * This file is part of the TCB Roster plugin.
 *
 * @param int $post_id The ID of the post for which the password is being sent.
 */
function tcbp_public_mission_send_password( $post_id ) {

	/**
	 * Sends a password to the user for mission admin access.
	 *
	 * @param int $post_id The ID of the post for which the password is being sent.
	 * @param int $user_id The ID of the user to send the password to.
	 * @param int $threshold_time The time threshold for sending the password.
	 */
	function signup_early( $post_id, $user_id, $threshold_time ) {
		while ( have_rows( 'stamp', $post_id ) ) :
			the_row();
			if ( $user_id === get_sub_field( 'stamp_user' ) ) {
				// stamp_date (d/m/Y) and stamp_time (H:i:s) are two separate ACF fields -
				// combine them into one timestamp for comparison, rather than comparing
				// stamp_time alone (a time-of-day-only value) against a full Unix timestamp
				// threshold, which silently ignored which day the user actually signed up on.
				$stamp_date = get_sub_field( 'stamp_date' );
				$stamp_time = get_sub_field( 'stamp_time' );

				// TEMPORARY DEBUG - remove once the parsing issue is confirmed/fixed.
				error_log( 'signup_early debug: user=' . $user_id . ' stamp_date=[' . $stamp_date . '] stamp_time=[' . $stamp_time . ']' );

				$stamp_datetime = DateTimeImmutable::createFromFormat( 'd/m/Y H:i:s', $stamp_date . ' ' . $stamp_time );
				if ( ! $stamp_datetime ) {
					// Can't determine when they signed up - fail safe by not treating them as early.
					error_log( 'signup_early debug: parse FAILED for user=' . $user_id );
					return false;
				}

				error_log( 'signup_early debug: user=' . $user_id . ' stamp_datetime=' . $stamp_datetime->getTimestamp() . ' threshold_time=' . $threshold_time );

				return $stamp_datetime->getTimestamp() < $threshold_time;
			}
		endwhile;
		return false;
	}

	// Retrieve data.
	$title    = get_field( 'title', $post_id );
	$password = get_field( 'password', $post_id );
	$delay    = get_field( 'delay', $post_id );

	// Set the threshold 24 hours previous.
	$date_time      = new DateTimeImmutable();
	$date_time      = $date_time->sub( new DateInterval( 'P1D' ) );
	$threshold_time = $date_time->getTimestamp();

	$early_email = array();
	$late_email  = array();
	while ( have_rows( 'rsvp', $post_id ) ) :
		the_row();
		$i     = get_row_index();
		$users = get_sub_field( 'user' );
		if ( ! $users ) {
			continue;
		}

		foreach ( $users as $user_id ) {
			error_log( 'signup debug: user=' . $user_id . ' slot=' . $i . ' early=' . ( signup_early( $post_id, $user_id, $threshold_time ) ? 'true' : 'false' ) );

			// Add to early list if signed up as attending and early.
			if ( ( 1 === $i ) && signup_early( $post_id, $user_id, $threshold_time ) ) {
				$early_email[] = $user_id;
			} else {
				$late_email[] = $user_id;
			}
		}
	endwhile;

	$now   = new DateTimeImmutable();
	$later = $now->add( new DateInterval( 'PT' . $delay . 'S' ) );

	// error_log( print_r( 'early_email: ' . json_encode( $early_email ), true ) );
	// .
	// error_log( print_r( 'late_email: ' . json_encode( $late_email ), true ) );
	// .

	// One shared thread for this mission's password notifications - both the immediate (early)
	// and delayed (late) waves post into it, rather than each getting its own thread. Stored on
	// the event post (group_6a71d24f69be1, field thread_id) so it can be cleaned up later once
	// the mission's news write-up is submitted - see tcbp_public_mission_send_news().
	
	//$thread_id = tcb_roster_admin_create_discord_thread( '384646672874995712', $title . ' - Password' );  // Sends to Arma 3 channel.
	$thread_id = tcb_roster_admin_create_discord_thread( '494511486715297794', $title . ' - Password' );  // Test channel for announcements, to avoid spamming the real channel during development.
	update_field( 'thread_id', $thread_id, $post_id );

	tcbp_public_mission_send_password_notifications( array( $early_email, $password, $thread_id, true ) );

	as_schedule_single_action( $later->getTimestamp(), 'tcb_roster_public_mission_send_password_notifications_action', array( array( $late_email, $password, $thread_id, false ) ) );
}
