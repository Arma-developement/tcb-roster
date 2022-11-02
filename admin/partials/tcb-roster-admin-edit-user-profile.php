<?php

function tcb_roster_admin_edit_user_profile ( $profile_user ) {

	if (! is_admin())
		return;

    $postID = get_field( 'post_id', 'user_' . $profile_user->ID );

    if ( $postID == "" )
        echo '<a href="//localhost/wordpress/edit-service-record/?id=' . $profile_user->ID . '" class="button button-secondary">Create Service Record</a><br><br>';
    else
        echo '<a href="//localhost/wordpress/edit-service-record/?id=' . $profile_user->ID . '" class="button button-secondary">Edit Service Record</a><br><br>';

    return;
}
