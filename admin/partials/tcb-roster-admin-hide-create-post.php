<?php

function tcb_roster_admin_hide_create_post() {
    register_post_type( 'Applications', array(
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
        ),
        'map_meta_cap' => true, // Set to `false`, if users are not allowed to edit/delete existing posts
    );
} 