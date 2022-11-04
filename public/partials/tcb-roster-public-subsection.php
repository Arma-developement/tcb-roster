<?php

function tcb_roster_public_subsection($attributes) {

	$meta_key = $attributes['rank'];

	if ($meta_key == "")
		return;
	
	$args = array(
		'numberposts'	=> -1,
		'post_type'		=> 'service-record',
		'meta_key'		=> 'rank',
		'meta_value'	=> $meta_key
	);

	$listOfPosts = get_posts( $args );

	foreach ( $listOfPosts as $post ) {
		setup_postdata( $post );

		$userId = get_field( 'user_id', $post );
		$user = get_user_by( 'id', $userId );
		$displayName = $user->get( 'display_name' );

		$return .= '<a href="//localhost/wordpress/user-info/?id=' . $userId . '">' . $displayName . '</a></br>';
	}

	wp_reset_postdata();

	return $return;
}
