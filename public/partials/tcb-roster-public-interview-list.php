<?php

function tcb_roster_public_interview_list($attributes){
	
	$return = '';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interviews',
		'post_status' => 'pending'
	));

	if ( $posts ) {
		$return .= '<h4>Pending</h4><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post->ID . '">' . get_field( 'applicant', $post ) . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interviews'
	));

	if ( $posts ) {
		$return .= '<h4>Completed</h4><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post->ID . '">' . get_field( 'applicant', $post ) . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}

	return $return;
}