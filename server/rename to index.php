<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

	$out   = fopen( '/var/log/nginx/discord-debug.log', 'a' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	$debug = true;

	require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
	use React\Http\Browser;
	use function React\Async\await;
	use Psr\Http\Message\ResponseInterface;

	fwrite( $out, 'Debug: Message Bot' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

	$header = array(
		'Authorization' => 'Bot ' . getenv( 'DISCORDBOT_TOKEN' ),
		'Content-Type'  => 'application/json',
	);

	/**
	 * Logs a debug message to the specified output stream if debugging is enabled.
	 *
	 * @param bool     $debug   Whether debugging is enabled.
	 * @param resource $out   The output stream resource.
	 * @param string   $message The message to log.
	 */
	function debug( $debug, $out, $message ) {
		if ( $debug ) {
			$formatted_message = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL;
			fwrite( $out, $formatted_message ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		}
	}

	/**
	 * Sends a message to a specified Discord channel.
	 *
	 * @param string   $channel_id The ID of the Discord channel.
	 * @param string   $message    The message to send.
	 * @param object   $client     The HTTP client instance.
	 * @param array    $header     The HTTP headers for the request.
	 * @param resource $out      The output stream resource for logging.
	 * @param bool     $debug      Whether debugging is enabled.
	 */
	function send_message( $channel_id, $message, $client, $header, $out, $debug ) {
		$channel_data = array( 'content' => $message );
		$promise      = $client->post(
			'https://discord.com/api/channels/' . $channel_id . '/messages',
			$header,
			json_encode( $channel_data ) // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		);
		try {
			debug( $debug, $out, 'Awaiting message sending' . PHP_EOL );
			$response = await( $promise );
			$body     = json_decode( (string) $response->getBody() );
			debug( $debug, $out, 'Message sent' . PHP_EOL );
		} catch ( Exception $e ) {
			debug( $debug, $out, 'Error ' . $e . PHP_EOL );
		}
	}

	/**
	 * Retrieves or creates a channel for a specific user and sends a message to it.
	 *
	 * @param string   $user_id  The ID of the user.
	 * @param string   $message  The message to send.
	 * @param object   $client   The HTTP client instance.
	 * @param array    $header   The HTTP headers for the request.
	 * @param resource $out      The output stream resource for logging.
	 * @param bool     $debug    Whether debugging is enabled.
	 */
	function get_channel( $user_id, $message, $client, $header, $out, $debug ) {
		$channel_data = array( 'recipient_id' => $user_id );

		debug( $debug, $out, 'https://discord.com/api/users/@me/channels' . PHP_EOL );
		debug( $debug, $out, var_export( $header, true ) . PHP_EOL ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		debug( $debug, $out, json_encode( $channel_data ) . PHP_EOL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

		$promise = $client->post(
			'https://discord.com/api/users/@me/channels',
			$header,
			json_encode( $channel_data ) // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		);
		try {
			debug( $debug, $out, 'Awaiting channel id' . PHP_EOL );
			$response = await( $promise );
			$body     = json_decode( (string) $response->getBody() );
			send_message( $body->id, $message, $client, $header, $out, $debug );
			debug( $debug, $out, 'Channel id:' . (string) $body->id . PHP_EOL );
		} catch ( Exception $e ) {
			debug( $debug, $out, 'Error ' . $e . PHP_EOL );
		}
	}

	/**
	 * Sends a message to a specified Discord channel.
	 *
	 * @param string   $channel_id The ID of the Discord channel.
	 * @param string   $message    The message to send.
	 * @param object   $client     The HTTP client instance.
	 * @param array    $header     The HTTP headers for the request.
	 * @param resource $out        The output stream resource for logging.
	 * @param bool     $debug      Whether debugging is enabled.
	 */
	function send_channel_message( $channel_id, $message, $client, $header, $out, $debug ) {
		$channel_data = array( 'content' => $message );
		$promise      = $client->post(
			'https://discord.com/api/channels/' . $channel_id . '/messages',
			$header,
			json_encode( $channel_data ) // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
		);
		try {
			debug( $debug, $out, 'Awaiting message sending' . PHP_EOL );
			$response = await( $promise );
			$body     = json_decode( (string) $response->getBody() );
			debug( $debug, $out, 'Message sent' . PHP_EOL );
		} catch ( Exception $e ) {
			debug( $debug, $out, 'Error ' . $e . PHP_EOL );
		}
	}

	/**
	 * Retrieves the user ID and username for a given Discord username.
	 *
	 * @param string   $username The Discord username to search for.
	 * @param object   $client   The HTTP client instance.
	 * @param array    $header   The HTTP headers for the request.
	 * @param resource $out      The output stream resource for logging.
	 * @param bool     $debug    Whether debugging is enabled.
	 * @return string|false      JSON-encoded user data or false if not found.
	 */
	function get_userid( $username, $client, $header, $out, $debug ) {

		$promise = $client->get( 'https://discord.com/api/guilds/288668512241582081/members/search?query=' . $username, $header );
		try {
			debug( $debug, $out, 'Awaiting guild query' . PHP_EOL );
			$response = await( $promise );
			$body     = ( (string) $response->getBody() );
			debug( $debug, $out, 'Guild query received: ' . $body . PHP_EOL );
			$result = json_decode( $body );
			if ( 1 === count( $result ) ) {
				$response      = array(
					'username' => $result[0]->user->username,
					'id'       => $result[0]->user->id,
				);
				$response_json = json_encode( $response ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				debug( $debug, $out, $response_json . PHP_EOL );
				return $response_json;
			}
			return false;

		} catch ( Exception $e ) {
			debug( $debug, $out, 'Error ' . $e . PHP_EOL );
			return false;
		}
	}

	$request = $_SERVER['REQUEST_URI'];
	switch ( $request ) {

		case '/3cb-message':
			debug( $debug, $out, "Received an operation password request\n" );
			$request_data = json_decode( file_get_contents( 'php://input' ) );
			if ( hash_equals( hash( 'sha256', $request_data->api_key ), getenv( '3CB_API_KEY_HASH' ) ) ) {
				debug( $debug, $out, "Authenticated\n" );
				$message = $request_data->message;
				$client  = new Browser();
				foreach ( $request_data->player_ids as $player_id ) {
					get_channel( $player_id, $message, $client, $header, $out, $debug );
				}
			} else {
				http_response_code( 401 );
			}
			break;

		case '/3cb-channel-message':
			$request_data = json_decode( file_get_contents( 'php://input' ) );
			if ( hash_equals( hash( 'sha256', $request_data->api_key ), getenv( '3CB_API_KEY_HASH' ) ) ) {
				$message    = $request_data->message;
				$client     = new Browser();
				$channel_id = '1048963747277963344';
				send_channel_message( $channel_id, $message, $client, $header, $out, $debug );
			} else {
				http_response_code( 401 );
			}
			break;

		case '/3cb-id':
			debug( $debug, $out, "Received a user id request\n" );
			$request_data = json_decode( file_get_contents( 'php://input' ) );
			if ( hash_equals( hash( 'sha256', $request_data->api_key ), getenv( '3CB_API_KEY_HASH' ) ) ) {
				debug( $debug, $out, "Authenticated\n" );
				$username = $request_data->username;
				$client   = new Browser();
				echo get_userid( $username, $client, $header, $out, $debug );
			} else {
				http_response_code( 401 );
			}
			break;

		default:
			http_response_code( 404 );
			break;
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
