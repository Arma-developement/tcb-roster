<?php

function tcb_roster_admin_prepare_field ( $field ) {

    if ( $field['_name'] == 'user-location' )
        return $field;

    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }
    return $field;
}
