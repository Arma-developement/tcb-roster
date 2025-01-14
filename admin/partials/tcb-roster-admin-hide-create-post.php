<?php

function tcb_roster_admin_hide_create_post() {

    /**
     * Post Type: Applications.
     */

    $labels = [
        "name" => esc_html__( "Applications", "tcb24" ),
        "singular_name" => esc_html__( "Application", "tcb24" ),
    ];

    $args = [
        "label" => esc_html__( "Applications", "tcb24" ),
        "labels" => $labels,
        "description" => "Application form for joining 3CB",
        "public" => false,
        "publicly_queryable" => false,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => false,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => true,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => false,
        "rewrite" => [ "slug" => "application", "with_front" => true ],
        "query_var" => true,
        "supports" => [ "title", "author" ],
        "show_in_graphql" => false,
        'capabilities' => array(
            'create_posts' => false, 
        ),
    ];

    register_post_type( "application", $args );
}