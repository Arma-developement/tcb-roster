<?php

function tcb_roster_public_subsection($attributes) {
	$return = '';

	$isNonMember = in_array( 'subscriber', wp_get_current_user()->roles);
	
	if (array_key_exists('rank', $attributes)) {
	
		$rank = $attributes['rank'];

		$args = array(
			'numberposts'	=> -1,
			'post_type'		=> 'service-record',
			'meta_key'		=> 'rank',
			'meta_value'	=> $rank
		);
		
		$return .= '<div class="tcb_subsection">';

		$listOfPosts = get_posts( $args );
		if ($listOfPosts) {
			$return .= '<ul>';
			foreach ( $listOfPosts as $post ) {
				setup_postdata( $post );

				$userId = get_field( 'user_id', $post );
				$user = get_user_by( 'id', $userId );

				// Check for old service records, with no user
				if (!$user) {
					continue;
				}

				$displayName = $user->get( 'display_name' );

				if ($isNonMember) {
					$return .= '<li>' . $displayName . '</li>';
				} else {
					if ((get_field( 'loa', $post ) == 1) && ($rank != 'Res')) {
						$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a> (LOA)</li>';
					} else {
						$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';
					}
				}
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		$return .= '</div>';		

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

		$return .= '<div class="tcb_subsection">';

		$listOfPosts = get_posts( $args );
		if ($listOfPosts) {
			$return .= '<ul>';
			foreach ( $listOfPosts as $post ) {
				setup_postdata( $post );

				$userId = get_field( 'user_id', $post );
				$user = get_user_by( 'id', $userId );

				// Check for old service records, with no user
				if (!$user) {
					continue;
				}

				$displayName = $user->get( 'display_name' );

				if ($isNonMember) {
					$return .= '<li>' . $displayName . '</li>';
				} else {
					$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';
				}
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		$return .= '</div>';

		return $return;		
	}
	return $return;
}
