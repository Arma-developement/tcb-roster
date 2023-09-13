<?php

function tcb_roster_admin_post_to_discordDM ( $sender, $receivers, $message ) {

    error_log( print_r("Discord DM: " . $sender . " " . json_encode($receivers) . " " . $message, TRUE ));

    $data = array( 'api_key' => $sender, 'content' => $message, 'player_ids' => $receivers );
    $curl = curl_init( 'http://localhost:8080/operation-password' );
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt( $curl, CURLOPT_POST, 1);
    curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $curl, CURLOPT_HEADER, 0);
    curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
    curl_exec( $curl );
    curl_close( $curl );

    return true;
}
