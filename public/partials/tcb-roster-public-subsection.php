<?php

function tcb_roster_public_subsection($attributes) {
	$return = '';
	
	if (array_key_exists('rank', $attributes)) {
	
		$rank = $attributes['rank'];

		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'service-record',
			'meta_key'		=> 'rank',
			'meta_value'	=> $rank
		);

		$listOfPosts = get_posts( $args );
		if ($listOfPosts) {
			$return .= '<ul>';
			foreach ( $listOfPosts as $post ) {
				setup_postdata( $post );

				$userId = get_field( 'user_id', $post );
				$user = get_user_by( 'id', $userId );
				$displayName = $user->get( 'display_name' );

				if ((get_field( 'loa', $post ) == 1) && ($rank != 'Res')) {
					$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a> (LOA)</li>';
				} else {
					$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';
				}
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		return $return;
	}

	if (array_key_exists('duty', $attributes)) {

		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'service-record',
			'meta_query' => array(
				array(
					'key' =>  'duties',
					'value'  => $attributes['duty'],
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

				$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		return $return;		
	}
	return $return;
}
