<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: discord.php
 * Description: Handles the code associated with the messaging Discord.
 */

/**
 * Function: get_steam_api
 *
 * @param  string $endpoint http endpoint of Steam API.
 * @param  string $steam_api_key your Steam API key.
 * @param  string $parameter_query parameters for your request.
 *
 * @return array body contains an array of requested data and http_code contain the http code of the request
 */
function get_steam_api( $endpoint, $steam_api_key, $parameter_query = '' ) {
	$curl = curl_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
	curl_setopt_array( // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
		$curl,
		array(
			CURLOPT_URL            => 'http://api.steampowered.com/' . $endpoint . '?key=' . $steam_api_key . $parameter_query,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_VERBOSE        => true,
			CURLOPT_HEADER         => true,
			CURLOPT_SSL_VERIFYPEER => false,
		)
	);
	$output      = curl_exec( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
	$header_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
	$header      = substr( $output, 0, $header_size );
	$body        = json_decode( substr( $output, $header_size ) );
	$httpcode    = curl_getinfo( $curl, CURLINFO_HTTP_CODE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
	curl_close( $curl ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

	return (array) array(
		'body'      => $body,
		'http_code' => $httpcode,
	);
}

/**
 * Function: steam_id32_to_steam_id64
 *
 * @param  string $steam_id steam_id32 of steam player.
 *
 * @return string steam_id64
 */
function steam_id32_to_steam_id64( $steam_id ) {
	$parts    = explode( ':', str_replace( 'STEAM_', '', $steam_id ) );
	$universe = (int) $parts[0];
	$universe = 0 === $universe ? 1 : $universe;
	$steam_id = ( $universe << 56 ) | ( 1 << 52 ) | ( 1 << 32 ) | ( (int) $parts[2] << 1 ) | (int) $parts[1];
	return $steam_id;
}

/**
 * Function: steam_custom_url_to_steam_id64
 *
 * @param  string $steam_api_key your Steam API key.
 * @param  string $steam_custom_url custom URL of steam player.
 *
 * @return string steam_id64
 */
function steam_custom_url_to_steam_id64( $steam_api_key, $steam_custom_url ) {
	return (array) get_steam_api( 'ISteamUser/ResolveVanityURL/v0001/', $steam_api_key, '&vanityurl=' . $steam_custom_url );
}

/**
 * Function: check_ban
 *
 * @param  string $steam_api_key your Steam API key.
 * @param  string $steam_ids Comma-delimited list of steam_ids.
 *
 * @return array body contains an array of players and http_code contain the http code of the request
 */
function check_ban( $steam_api_key, $steam_ids ) {
	return (array) get_steam_api( 'ISteamUser/GetPlayerBans/v1/', $steam_api_key, '&steamids=' . $steam_ids );
}

/**
 * Function: format_steam_ids
 *
 * @param  string $steam_api_key your Steam API key.
 * @param  array  $steam_ids Array of steam_ids.
 *
 * @return string Comma-delimited list of steam_ids
 */
function format_steam_ids( $steam_api_key, $steam_ids ) {
	$formatted_steam_ids = '';

	foreach ( $steam_ids as $steam_id ) {
		$formatted_steam_ids .= empty( $formatted_steam_ids ) ? '' : ',';

		switch ( $steam_id ) {
			// steam_id32.
			case ( preg_match( '~^STEAM_0:[01]:[0-9]{7,8}$~', $steam_id ) ? true : false ):
				$formatted_steam_ids .= steam_id32_to_steam_id64( $steam_id );
				break;
			// steam_id64.
			case ( preg_match( '~^(https://steamcommunity\.com/profiles/[0-9/]{17})|([0-9]{17})$~', $steam_id ) ? true : false ):
				$formatted_steam_ids .= $steam_id;
				break;
			// SteamCustomURL.
			case ( preg_match( '~^(https://steamcommunity\.com/id/[a-zA-Z0-9/]{1,})|([a-zA-Z0-9]{1,})$~', $steam_id ) ? true : false ):
				$steam_id      = preg_match( '~^https://steamcommunity\.com/id/[a-zA-Z0-9/]{1,}$~', $steam_id ) ? str_replace( 'https://steamcommunity.com/id/', '', substr( $steam_id, 0, -1 ) ) : $steam_id;
				$resolved_body = steam_custom_url_to_steam_id64( $steam_api_key, $steam_id )['body'];
				// Steam returns a response with no steamid property when the vanity URL doesn't resolve to a player.
				$formatted_steam_ids .= isset( $resolved_body->response->steamid ) ? $resolved_body->response->steamid : '';
				break;
			// Default.
			default:
				$formatted_steam_ids .= $steam_id;
				break;
		}
	}

	return (string) $formatted_steam_ids;
}


/**
 * Contact Steam API to check if a user is VAC banned.
 * DEPRECATED: Use tcb_roster_get_steam_user_info() instead.
 *
 * @param string $user  The name of the user.
 */
function tcb_roster_admin_steam_query_vac( $user ) {

	$steam_api_key = getenv( 'STEAM_3CB_KEY' );
	$steam_ids     = array( $user ); // Array of Steam IDs to check.

	// Format steam_id if needed.
	$formatted_steam_ids = format_steam_ids( $steam_api_key, $steam_ids );

	// Get player's bans.
	$result = check_ban( $steam_api_key, $formatted_steam_ids );

	// Check if the request was successful.
	if ( ! isset( $result['body']->players ) || empty( $result['body']->players ) ) {
		return false;
	}
	// Check if the first player has a SteamId.
	if ( ! isset( $result['body']->players[0]->SteamId ) ) {
		return false;
	}

	// Prepare results.
	foreach ( $result['body']->players as $player ) {
		$response = array(
			'SteamId'          => $player->SteamId,
			'CommunityBanned'  => $player->CommunityBanned,
			'VACBanned'        => $player->VACBanned,
			'NumberOfVACBans'  => $player->NumberOfVACBans,
			'DaysSinceLastBan' => $player->DaysSinceLastBan,
			'NumberOfGameBans' => $player->NumberOfGameBans,
			'EconomyBan'       => $player->EconomyBan,
		);
		break;
	}

	// echo '<pre>';
	// print_r( $response );
	// echo '</pre>';

	return $response;
}

/**
 * Function: tcb_roster_get_steam_user_info
 *
 * Look up a Steam user's public profile info and ban status from a single Steam ID.
 * Accepts steam_id32, steam_id64, a profile URL, or a vanity URL/name.
 *
 * @param  string $steam_id Steam ID in any format supported by format_steam_ids().
 *
 * @return array|false Associative array of profile and ban info, or false on failure.
 */
function tcb_roster_get_steam_user_info( $steam_id ) {

	$steam_api_key = getenv( 'STEAM_3CB_KEY' );

	// Normalise whatever format we were given into a steam_id64.
	$formatted_steam_id = format_steam_ids( $steam_api_key, array( $steam_id ) );

	if ( empty( $formatted_steam_id ) ) {
		return false;
	}

	// Fetch the public profile summary.
	$summary_result = get_steam_api( 'ISteamUser/GetPlayerSummaries/v0002/', $steam_api_key, '&steamids=' . $formatted_steam_id );

	if ( ! isset( $summary_result['body']->response->players[0] ) ) {
		return false;
	}
	$summary = $summary_result['body']->response->players[0];

	// Fetch ban status.
	$ban_result = check_ban( $steam_api_key, $formatted_steam_id );

	if ( ! isset( $ban_result['body']->players[0] ) ) {
		return false;
	}
	$bans = $ban_result['body']->players[0];

	return array(
		'SteamId'           => $summary->steamid,
		'PersonaName'       => $summary->personaname,
		'ProfileUrl'        => $summary->profileurl,
		'Avatar'            => $summary->avatarfull,
		'ProfileVisibility' => $summary->communityvisibilitystate, // 1 = private, 3 = public.
		'CommunityBanned'   => $bans->CommunityBanned,
		'VACBanned'         => $bans->VACBanned,
		'NumberOfVACBans'   => $bans->NumberOfVACBans,
		'DaysSinceLastBan'  => $bans->DaysSinceLastBan,
		'NumberOfGameBans'  => $bans->NumberOfGameBans,
		'EconomyBan'        => $bans->EconomyBan,
	);
}
