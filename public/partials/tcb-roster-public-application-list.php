<?php

function tcb_roster_public_application_list($attributes){
	
	$return = '<div class="tcb_application_list">';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application',
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
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$steam_name = get_field( 'app_steam_name', $post_id );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post_id . '">' . $author_name . ' (' . $steam_name . ')</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	else {
		$return .= '<p>No pending Applications</p>';
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application',
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
			$steam_name = get_field( 'app_steam_name', $post_id );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post_id . '">' . $author_name . ' (' . $steam_name . ')</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No approved Applications</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application',
		'tax_query' => array(
			array(
				'taxonomy'  => 'tcb-status',
				'field'     => 'slug',
				'terms'     => 'denied'
			)
		)
	));

	$return .= '<h2>Denied</h2>';
	if ( $posts ) {
		$return .= '<ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$steam_name = get_field( 'app_steam_name', $post_id );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post_id . '">' . $author_name . ' (' . $steam_name . ')</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No denied Applications</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'application',
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
			$steam_name = get_field( 'app_steam_name', $post_id );
			$return .= '<li><a href="'. home_url() .'/hidden/application-view/?post=' . $post_id . '">' . $author_name . ' (' . $steam_name . ')</a>   ' . get_the_date('d-m-Y', $post_id) . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No archived Applications</p>';
	}

	$return .= '</div>';

	return $return;
}