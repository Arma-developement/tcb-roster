<?php

function tcb_roster_public_application_list($attributes){
	
	$return = '';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application',
		'post_status' => 'pending'
	));

	$return .= '<div class="tcb_application_list">';

	if ( $posts ) {
		$return .= '<h4>Pending</h4><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$steam_name = get_field( 'app_steam_name', $post_id );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post_id . '">' . $author_name . ' (' . $steam_name . ')</a></li>';
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
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$steam_name = get_field( 'app_steam_name', $post_id );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post_id . '">' . $author_name . ' (' . $steam_name . ')</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}

	$return .= '</div>';

	return $return;
}