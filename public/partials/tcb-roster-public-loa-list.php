<?php

function tcb_roster_public_loa_list($attributes){
	
	$return = '';

	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'loa',
		'post_status' => 'pending'
	));

	$return .= '<div class="tcb_loa_list">';

	if ( $posts ) {
		$return .= '<h2>Pending</h2><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}

	// $posts = get_posts(array(
	// 	'numberposts' => -1,
	// 	'post_type' => 'loa',
	// 	'post_status' => 'approved'
	// ));

	// $return .= '<div class="tcb_loa_list">';

	// if ( $posts ) {
	// 	$return .= '<h2>Approved</h2><ul>';
	// 	foreach( $posts as $post ) {
	// 		setup_postdata( $post );
	// 		$post_id = $post->ID;
	// 		$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
	// 		$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a></li>';
	// 	}		
	// 	$return .= '</ul>';
	// 	wp_reset_postdata();
	// }
	
	$posts = get_posts(array(
		'numberposts' => -1,
		'post_type' => 'loa'
	));

	if ( $posts ) {
		$return .= '<h2>Approved</h2><ul>';
		foreach( $posts as $post ) {
			setup_postdata( $post );
			$post_id = $post->ID;
			$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
			$return .= '<li><a href="'. home_url() .'/hidden/loa-view/?post=' . $post_id . '">' . $author_name . '</a></li>';
		}		
		$return .= '</ul>';
		wp_reset_postdata();
	}

	$return .= '</div>';

	return $return;
}