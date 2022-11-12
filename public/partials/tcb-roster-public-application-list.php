<?php

function tcb_roster_public_application_list($attributes){
	
	$return = '';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application',
		'post_status' => 'pending'
	));

	if ( $posts ) {
		$return .= '<h4>Pending</h4><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post->ID . '">' . get_field( 'app_steam_name', $post ) . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application'
	));

	if ( $posts ) {
		$return .= '<h4>Completed</h4><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post->ID . '">' . get_field( 'app_steam_name', $post ) . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}

	return $return;
}