<?php

// function tcb_roster_admin_post_to_discordDM_makeRequest ($endpoint, $data) {
//     // Create CURL request
//     $curl = curl_init();
//     curl_setopt( $curl, CURLOPT_URL, 'https://discord.com/api/' . $endpoint. '');
//     curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Authorization: Bot token', 'Content-Type: application/json', 'Accept: application/json'));
//     curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1);
//     curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0);
//     curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode($data));
    
//     // Execute request
//     $response = curl_exec( $curl );
//     curl_close( $curl );
//     return json_decode($response, true);
// }

function tcb_roster_admin_post_to_discordDM ( $sender, $receiver, $message ) {

    // // Open the DM
    // $newDM = tcb_roster_admin_post_to_discordDM_makeRequest('/users/@me/channels', array('recipient_id' => $receiver));

    // // Sends the message
    // if(isset($newDM['id'])) {
    //     $newMessage = tcb_roster_admin_post_to_discordDM_makeRequest('/channels/'.$newDM['id'].'/messages', array('content' => $message));
    // }

    error_log( print_r("Discord DM: " . $sender . " " . $receiver . " " . $message, TRUE ));

    return true;
}
