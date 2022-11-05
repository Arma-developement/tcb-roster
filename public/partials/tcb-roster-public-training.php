<?php

function tcb_roster_public_training($attributes) {

	$args = array(
		'numberposts'	=> -1,
		'post_type'		=> 'service-record',
		'meta_key'		=> 'rank',
		'meta_value'	=> $meta_key
	);

	$listOfPosts = get_posts( $args );
	if ($listOfPosts) {
		$return .= '<ul>';
		foreach ( $listOfPosts as $post ) {
			setup_postdata( $post );

			$userId = get_field( 'user_id', $post );
			$user = get_user_by( 'id', $userId );
			$displayName = $user->get( 'display_name' );


			$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';

		}
		$return .= '</ul>';
	}
	wp_reset_postdata();

	return $return;
}
