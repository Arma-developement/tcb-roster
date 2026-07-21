<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * DEPRECATED: Use tcb_roster_admin_post_to_discord_channel instead.
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
// function tcb_roster_admin_post_to_discord( $sender, $channel, $message ) {

// 	switch ( $channel ) {
// 		case 'recruitment-managers':
// 			$webhook = getenv( 'DISCORD_WEBHOOK_RECRUITMENT_MANAGERS' );
// 			break;
// 		case 'announcements':
// 			$webhook = getenv( 'DISCORD_WEBHOOK_ANNOUNCEMENTS' );
// 			break;
// 		default:
// 			return false;
// 	}

// 	$data = array(
// 		'content'  => $message,
// 		'username' => $sender,
// 	);
// 	$curl = curl_init( $webhook );
// 	curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_POST, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_HEADER, 0 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_setopt( $curl, CURLOPT_TIMEOUT, 5 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
// 	curl_exec( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
// 	$curl_errno = curl_errno( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_errno
// 	$http_code  = curl_getinfo( $curl, CURLINFO_HTTP_CODE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
// 	if ( $curl_errno ) {
// 		error_log( 'Discord webhook post failed: ' . curl_error( $curl ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_error
// 	} elseif ( $http_code >= 300 ) {
// 		error_log( 'Discord webhook post to ' . $channel . ' returned HTTP ' . $http_code );
// 	}
// 	curl_close( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
// 	return ! $curl_errno && $http_code < 300;
// }

/**
 * Sends a message to a specified Discord channel.
 *
 * @param string $sender  The name of the sender.
 * @param string $channel The Discord channel ID.
 * @param string $message The message to be sent.
 */
function tcb_roster_admin_post_to_discord_channel( $channel, $message ) {

	$key = getenv( 'WP_3CB_KEY' );

	switch ( $channel ) {
		case 'recruitment-managers':
			$channel_id = '384647101277274112';
			break;
		case 'announcements':
			//$channel_id = '384647504937091072';
			$channel_id = '494511486715297794';  // test channel for announcements, to avoid spamming the real channel during development.
			break;
		default:
			return false;
	}

	$data = array(
		'api_key'    => $key,
		'message'    => $message,
		'channel_id' => $channel_id,
	);

	$discordbot_url = getenv( 'DISCORDBOT_URL' );
	if ( ! $discordbot_url ) {
		error_log( 'Discord channel-message bridge call skipped: DISCORDBOT_URL is not set' );
		return false;
	}

	// wp_remote_post(), not wp_safe_remote_post(): the bridge lives at a private LAN address
	// that the "safe" variant's SSRF protection would block outright.
	$response = wp_remote_post(
		rtrim( $discordbot_url, '/' ) . '/3cb-channel-message',
		array(
			'timeout' => 5,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Discord channel-message bridge call failed: ' . $response->get_error_message() );
		return false;
	}

	$http_code = wp_remote_retrieve_response_code( $response );
	if ( $http_code >= 300 ) {
		error_log( 'Discord channel-message bridge call returned HTTP ' . $http_code );
	}
	return $http_code < 300;
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

	$discordbot_url = getenv( 'DISCORDBOT_URL' );
	if ( ! $discordbot_url ) {
		error_log( 'Discord DM bridge call skipped: DISCORDBOT_URL is not set' );
		return false;
	}

	// wp_remote_post(), not wp_safe_remote_post(): the bridge lives at a private LAN address
	// that the "safe" variant's SSRF protection would block outright.
	$response = wp_remote_post(
		rtrim( $discordbot_url, '/' ) . '/3cb-message',
		array(
			'timeout' => 5,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Discord DM bridge call failed: ' . $response->get_error_message() );
		return false;
	}

	$http_code = wp_remote_retrieve_response_code( $response );
	if ( $http_code >= 300 ) {
		error_log( 'Discord DM bridge call returned HTTP ' . $http_code );
	}

	return $http_code < 300;
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

	$discordbot_url = getenv( 'DISCORDBOT_URL' );
	if ( ! $discordbot_url ) {
		error_log( 'Discord username lookup bridge call skipped: DISCORDBOT_URL is not set' );
		return false;
	}

	// wp_remote_post(), not wp_safe_remote_post(): the bridge lives at a private LAN address
	// that the "safe" variant's SSRF protection would block outright.
	$response = wp_remote_post(
		rtrim( $discordbot_url, '/' ) . '/3cb-id',
		array(
			'timeout' => 5,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Discord username lookup bridge call failed: ' . $response->get_error_message() );
		return false;
	}

	$get_info = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! $get_info ) {
		return false;
	}

	if ( ! isset( $get_info['id'] ) || ! isset( $get_info['username'] ) ) {
		return false;
	}

	if ( strcasecmp( $get_info['username'], $username ) !== 0 ) {
		return false;
	}

	return $get_info['id'];
}
