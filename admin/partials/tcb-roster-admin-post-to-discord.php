<?php

function tcb_roster_admin_post_to_discord ( $sender, $channel, $message ) {

    switch ($channel) {
        case 'recruitment-managers':
            $webhook = 'https://discord.com/api/webhooks/1039164797125668894/cfPBzoEb5PGzCUv66rK9gaMDUVWHNL_B--nOeB00VkeeCi6DFVuewMTNOCEvObOwst4t';
            break;
        default:
            return false;
    }

    $data = array( 'content' => $message, 'username' => $sender );
    $curl = curl_init( $webhook );
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
