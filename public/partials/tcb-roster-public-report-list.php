<?php

function tcb_roster_public_report_list($attributes){
	
	$return = '<div class="tcb_report_list">';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'report',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'     => 'slug',
				'terms'     => 'pending',
			)
		)
	));

	$return .= '<h2>Pending</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$return .= '<li><a href="'. home_url() .'/hidden/report-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	else {
		$return .= '<p>No pending Reports</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'report',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'     => 'slug',
				'terms'     => 'approved'
			)
		)
	));

	$return .= '<h2>Approved</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$return .= '<li><a href="'. home_url() .'/hidden/report-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No approved Reports</p>';
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'report',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'     => 'slug',
				'terms'     => 'rejected'
			)
		)
	));

	$return .= '<h2>Rejected</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$return .= '<li><a href="'. home_url() .'/hidden/report-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No rejected Reports</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'report',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'     => 'slug',
				'terms'     => 'archived'
			)
		)
	));

	$return .= '<h2>Archived</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$return .= '<li><a href="'. home_url() .'/hidden/report-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No archived Reports</p>';
	}

	$return .= '</div>';

	return $return;
}