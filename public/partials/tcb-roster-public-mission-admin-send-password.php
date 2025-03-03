<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * This file is part of the TCB Roster plugin.
 *
 * @param array $args An array of arguments for sending the password.
 */
function tcb_roster_public_mission_send_password_email( $args ) {
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
		tcb_roster_admin_post_to_discordDM( '3CB-Bot', $discord_id_list, $msg );
	}
}

/**
 * This file is part of the TCB Roster plugin.
 *
 * @param int $post_id The ID of the post for which the password is being sent.
 */
function tcb_roster_public_mission_send_password( $post_id ) {

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
