<?php

function tcb_roster_public_interview_list($attributes){
	
	$return = '<div class="tcb_interview_list">';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interview',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'  => 'slug',
				'terms'  => 'pending',
			)
		)
	));

	$return .= '<h2>Pending</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$userData = get_field( 'applicant', $post_id );
			$displayName = $userData['display_name'];
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post_id . '">' . $displayName . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No pending Interviews</p>';
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interview',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'  => 'slug',
				'terms'  => 'approved',
			)
		)
	));

	$return .= '<h2>Approved</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$userData = get_field( 'applicant', $post_id );
			$displayName = $userData['display_name'];
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post_id . '">' . $displayName . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No approved Interviews</p>';
	}	

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interview',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'  => 'slug',
				'terms'  => 'rejected',
			)
		)
	));

	$return .= '<h2>Rejected</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$userData = get_field( 'applicant', $post_id );
			$displayName = $userData['display_name'];
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post_id . '">' . $displayName . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No rejected Interviews</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'interview',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'  => 'slug',
				'terms'  => 'archived',
			)
		)
	));

	$return .= '<h2>Archived</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$userData = get_field( 'applicant', $post_id );
			$displayName = $userData['display_name'];
			$return .= '<li><a href="'. home_url() .'/interview_view/?post=' . $post_id . '">' . $displayName . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No archived Interviews</p>';
	}

	$return .= '</div>';

	return $return;
}