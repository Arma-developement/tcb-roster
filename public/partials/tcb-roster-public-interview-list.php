<?php

function tcb_roster_public_interview_list($attributes){
	
	$return = '';
	$return .= '<div class="tcb_interview_list">';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interview',
		'post_status' => 'pending'
	));

	if ( $posts ) {
		$return .= '<h2>Pending</h2><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$userData = get_field( 'applicant', $post_id );
			$displayName = $userData['display_name'];
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post_id . '">' . $displayName . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interview'
	));

	if ( $posts ) {
		$return .= '<h2>Completed</h2><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$userData = get_field( 'applicant', $post_id );
			$displayName = $userData['display_name'];	
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post_id . '">' . $displayName . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	$return .= '</div>';

	return $return;
}