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

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// tcb_roster_admin_post_to_discord ( 'Mission Bot', 'announcements', $announcement );
	// .
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
	$title                   = get_field( 'title', $post_id );
	$brief_image             = get_the_post_thumbnail_url( $post_id, 'large' );
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

	if ( '' !== $post_op_secondary_image ) {
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
		// Add post thumbnail.
		if ( '' !== $post_op_image ) {
			$image_id = $post_op_image['ID'];
			if ( $image_id ) {
				set_post_thumbnail( $new_post_id, $image_id );
			}
		} elseif ( ! empty( $brief_image ) ) {
			set_post_thumbnail( $new_post_id, $brief_image );
		}
	}
}

add_action( 'tcb_roster_public_mission_send_password_email_action', 'tcbp_public_mission_send_password_email' );

/**
 * This file is part of the TCB Roster plugin.
 *
 * @param array $args An array of arguments for sending the password.
 */
function tcbp_public_mission_send_password_email( $args ) {
	$list_of_user_ids = $args[0];
	$password         = $args[1];

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
		tcb_roster_admin_post_to_discord_dm( $discord_id_list, $msg );
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
		$fields = get_field( 'time_stamp', $post_id );
		if ( ! $fields ) {
			return false;
		}
		foreach ( $fields as $field ) {
			if ( $user_id === $field['user'] ) {
				return $field['time'] < $threshold_time;
			}
		}
		return false;
	}

	// Retrieve data.
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

	as_enqueue_async_action( 'tcb_roster_public_mission_send_password_email_action', array( array( $early_email, $password ) ) );
	as_schedule_single_action( DateTime::createFromImmutable( $later ), 'tcb_roster_public_mission_send_password_email_action', array( array( $late_email, $password ) ) );
}
