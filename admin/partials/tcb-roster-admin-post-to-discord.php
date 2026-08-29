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
		case 'news':
			$channel_id = '1530631516219117651';
			//$channel_id = '494511486715297794';  // test channel for announcements, to avoid spamming the real channel during development.
			break;
		case 'recruitment-managers':
			$channel_id = '384647101277274112';
			break;
		case 'announcements':
			$channel_id = '384647504937091072';
			//$channel_id = '494511486715297794';  // test channel for announcements, to avoid spamming the real channel during development.
			break;
		case 'password':
			$channel_id = '1533937561804869632';
			//$channel_id = '494511486715297794';  // test channel for announcements, to avoid spamming the real channel during development.
			break;
		case 'operation_coordinators':
			$channel_id = '384646925837795328';
			break;
		case 'arma-3':
			$channel_id = '384646672874995712';
			break;
		case 'basic-training-instructors':
			$channel_id = '679687679863947264';
			break;

		default:
			// Anything else is treated as a raw Discord channel/thread ID (e.g. a thread ID
			// returned by tcb_roster_admin_create_discord_thread()) rather than one of the named
			// channels above. Discord IDs are numeric snowflakes, so reject anything that
			// clearly isn't one rather than silently posting to a bogus channel_id.
			if ( ! ctype_digit( (string) $channel ) ) {
				return false;
			}
			$channel_id = (string) $channel;
			break;
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

add_action( 'transition_post_status', 'tcbp_public_news_notify_discord_on_publish', 10, 3 );

/**
 * Posts a Discord notification to the news channel the first time a news article is published -
 * covers both tcbp_public_mission_send_news()'s automated AAR posts and a manually created and
 * published article in wp-admin, since both ultimately go through the same publish transition.
 *
 * Hooked to transition_post_status rather than the publish_post action deliberately:
 * publish_post fires on every subsequent save of an already-published post, not just the first
 * time it's published, which would spam the channel on every minor edit. transition_post_status
 * lets us check the previous status too, so this only fires on the actual move into "publish".
 *
 * @param string  $new_status The new post status.
 * @param string  $old_status The previous post status.
 * @param WP_Post $post       The post being transitioned.
 */
function tcbp_public_news_notify_discord_on_publish( $new_status, $old_status, $post ) {

	// News articles are plain "post" post_type posts on this site - see tcbp_public_mission_send_news().
	if ( 'post' !== $post->post_type ) {
		return;
	}

	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}

	$message = 'A new article has been published: "' . $post->post_title . '"' . "\n" . get_permalink( $post );
	tcb_roster_admin_post_to_discord_channel( 'news', $message );
}

/**
 * Requests a new private thread be created under a given parent channel.
 *
 * Note: this assumes the bridge's response body is JSON containing the new thread's ID under an
 * "id" key (matching the shape tcb_roster_admin_query_discord_username() already expects from
 * the bridge) - adjust the key below once the actual 3cb-thread bridge response is finalised, if
 * it turns out to differ.
 *
 * @param string $channel_id  The Discord parent channel ID to create the thread under.
 * @param string $thread_name The name to give the new thread.
 * @return string|false The new thread's ID, or false on failure.
 */
function tcb_roster_admin_create_discord_thread( $channel_id, $thread_name ) {

	$key = getenv( 'WP_3CB_KEY' );

	$data = array(
		'api_key'     => $key,
		'channel_id'  => $channel_id,
		'name' => $thread_name,
	);

	$discordbot_url = getenv( 'DISCORDBOT_URL' );
	if ( ! $discordbot_url ) {
		error_log( 'Discord thread creation bridge call skipped: DISCORDBOT_URL is not set' );
		return false;
	}

	// wp_remote_post(), not wp_safe_remote_post(): the bridge lives at a private LAN address
	// that the "safe" variant's SSRF protection would block outright.
	$response = wp_remote_post(
		rtrim( $discordbot_url, '/' ) . '/3cb-thread',
		array(
			'timeout' => 5,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Discord thread creation bridge call failed: ' . $response->get_error_message() );
		return false;
	}

	$http_code = wp_remote_retrieve_response_code( $response );
	if ( $http_code >= 300 ) {
		error_log( 'Discord thread creation bridge call returned HTTP ' . $http_code );
		return false;
	}

	$get_info = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! $get_info || ! isset( $get_info['thread_id'] ) ) {
		error_log( 'Discord thread creation bridge call returned an unexpected response: ' . wp_remote_retrieve_body( $response ) );
		return false;
	}

	return $get_info['thread_id'];
}

/**
 * Requests a previously-created Discord thread be deleted.
 *
 * @param string $thread_id The Discord thread ID to delete.
 * @return bool True on success, false on failure.
 */
function tcb_roster_admin_delete_discord_thread( $thread_id ) {

	$key = getenv( 'WP_3CB_KEY' );

	$data = array(
		'api_key'    => $key,
		'thread_id' => $thread_id,
	);

	$discordbot_url = getenv( 'DISCORDBOT_URL' );
	if ( ! $discordbot_url ) {
		error_log( 'Discord thread deletion bridge call skipped: DISCORDBOT_URL is not set' );
		return false;
	}

	// wp_remote_post(), not wp_safe_remote_post(): the bridge lives at a private LAN address
	// that the "safe" variant's SSRF protection would block outright.
	$response = wp_remote_post(
		rtrim( $discordbot_url, '/' ) . '/3cb-thread-delete',
		array(
			'timeout' => 5,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( 'Discord thread deletion bridge call failed: ' . $response->get_error_message() );
		return false;
	}

	$http_code = wp_remote_retrieve_response_code( $response );
	if ( $http_code >= 300 ) {
		error_log( 'Discord thread deletion bridge call returned HTTP ' . $http_code );
		return false;
	}

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
