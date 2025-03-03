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
 * @param string $sender  The name of the sender.
 * @param string $receivers The Discord user ID.
 * @param string $message The message to be sent.
 */
function tcb_roster_admin_post_to_discordDM( $sender, $receivers, $message ) {

	// Debug code.
	// error_log( print_r( 'Discord DM: ' . $sender . ' ' . json_encode( $receivers ) . ' ' . $message, true ) );
	// .

	$data = array(
		'api_key'    => $sender,
		'content'    => $message,
		'player_ids' => $receivers,
	);
	$curl = curl_init( 'http://localhost:8080/operation-password' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
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
