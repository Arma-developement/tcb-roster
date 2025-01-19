<?php

function tcb_roster_public_loa_list($attributes){
	
	$return = '<div class="tcb_loa_list">';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'loa',
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
			$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y') . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}
	else {
		$return .= '<p>No pending LOAs</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'loa',
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
			$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y') . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No approved LOAs</p>';
	}
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'loa',
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
			$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y') . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No denied LOAs</p>';
	}

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'loa',
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
			$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a>   ' . get_the_date('d-m-Y') . '</li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	} else {
		$return .= '<p>No archived LOAs</p>';
	}

	$return .= '</div>';

	return $return;
}