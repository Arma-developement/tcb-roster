<?php

function tcb_roster_public_subsection($attributes) {
	$return = '';

	$meta_key = $attributes['rank'];

	if ($meta_key != "") {
	
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

				if ((get_field( 'loa', $post ) == 1) && ($meta_key != 'Res')) {
					$return .= '<li><a href="//localhost/wordpress/user-info/?id=' . $userId . '">' . $displayName . '</a> (LOA)</li>';
				} else {
					$return .= '<li><a href="//localhost/wordpress/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';
				}
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		return $return;
	}

	$meta_key = $attributes['duty'];

	if ($meta_key != "") {

		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'service-record',
			'meta_query' => array(
				array(
					'key' =>  'duties',
					'value'  => $meta_key,
					'compare' =>  'LIKE'
				)
			)
		);

		$listOfPosts = get_posts( $args );
		if ($listOfPosts) {
			$return .= '<ul>';
			foreach ( $listOfPosts as $post ) {
				setup_postdata( $post );

				$userId = get_field( 'user_id', $post );
				$user = get_user_by( 'id', $userId );
				$displayName = $user->get( 'display_name' );

				$return .= '<li><a href="//localhost/wordpress/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		return $return;		
	}
	return $return;
}
