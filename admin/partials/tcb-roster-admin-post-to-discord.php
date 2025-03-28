<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: discord.php
 * Description: Handles the code associated with the messaging Discord.
 */

/**
 * Sends a message to a specified Discord channel.
 *
 * @param string $sender  The name of the sender.
 * @param string $channel The Discord channel ID.
 * @param string $message The message to be sent.
 */
function tcb_roster_admin_post_to_discord( $sender, $channel, $message ) {

	switch ( $channel ) {
		case 'recruitment-managers':
			$webhook = getenv( 'DISCORD_WEBHOOK_RECRUITMENT_MANAGERS', true );
			break;
		case 'announcements':
			$webhook = getenv( 'DISCORD_WEBHOOK_ANNOUNCEMENTS', true );
			break;
		default:
			return false;
	}

	$data = array(
		'content'  => $message,
		'username' => $sender,
	);
	$curl = curl_init( $webhook );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_POST, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_HEADER, 0 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_exec( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
	curl_close( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
	return true;
}

/**
 * Sends a message to a specified Discord user.
 *
 * @param string $receivers The Discord user ID.
 * @param string $message The message to be sent.
 */
function tcb_roster_admin_post_to_discord_dm( $receivers, $message ) {

	// Debug code.
	// error_log( print_r( 'Discord DM: ' . $sender . ' ' . json_encode( $receivers ) . ' ' . $message, true ) );
	// .

	$key = getenv( 'WP_3CB_KEY' );

	$data = array(
		'api_key'    => $key,
		'message'    => $message,
		'player_ids' => $receivers,
	);
	$curl = curl_init( 'http://127.0.0.1:8084/3cb-message' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_POST, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_HEADER, 0 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt,WordPress.WP.AlternativeFunctions.json_encode_json_encode
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_exec( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
	curl_close( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

	return true;
}

/**
 * Queries a Discord user by their username.
 *
 * @param string $username The Discord user name.
 */
function tcb_roster_admin_query_discord_username( $username ) {

	$key = getenv( 'WP_3CB_KEY' );

	$data = array(
		'api_key'  => $key,
		'username' => $username,
	);
	$curl = curl_init( 'http://127.0.0.1:8084/3cb-id' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_POST, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_HEADER, 0 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt,WordPress.WP.AlternativeFunctions.json_encode_json_encode
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	$get_url  = curl_exec( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
	$get_info = json_decode( $get_url, true );
	curl_close( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

	if ( ! $get_info ) {
		return false;
	}

	if ( ! isset( $get_info['id'] ) || ! isset( $get_info['username'] ) ) {
		return false;
	}

	if ( $get_info['username'] !== $username ) {
		return false;
	}

	return $get_info['id'];
}
