<?php

function tcb_roster_admin_prepare_field ( $field ) {

    if ( $field['_name'] == 'user-location' )
        return $field;

    $user = wp_get_current_user();
    if (($field['_name'] == 'courses_completed') && in_array("training_admin", $user->roles)) {
        return $field;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }
    return $field;
}
