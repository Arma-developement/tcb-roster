<?php

function tcb_roster_public_commendations ($attributes) {

	$args = array(
		'numberposts'	=> -1,
		'post_type'		=> 'service-record'
	);
	$return = '';

	$path = plugins_url() . '/tcb-roster/images/ribbons/';
	$width = 350 / 2;
	$height = 94 / 2;

	// Build a list of awards titles and recipients, dynamically from the service records
	$listOfPosts = get_posts( $args );
	if ($listOfPosts) {
		foreach ( $listOfPosts as $post ) {
			setup_postdata( $post );
			$userId = get_field( 'user_id', $post );

			$listOfAwards = get_field( 'service_awards', $post );
			if ($listOfAwards) {
				foreach ( $listOfAwards as $award ) {
					$listOfRecipients[$award['value']][] = $userId;
					$listOfServiceAwardTitles[$award['value']] = $award['label'];
				}
			}

			$listOfAwards = get_field( 'operational_awards', $post );
			if ($listOfAwards) {
				foreach ( $listOfAwards as $award ) {
					$listOfRecipients[$award['value']][] = $userId;
					$listOfOperationalAwardTitles[$award['value']] = $award['label'];
				}
			}
			
			$listOfAwards = get_field( 'community_awards', $post );
			if ($listOfAwards) {
				foreach ( $listOfAwards as $award ) {
					$listOfRecipients[$award['value']][] = $userId;
					$listOfCommunityAwardTitles[$award['value']] = $award['label'];
				}	
			}		
		}

		ksort($listOfServiceAwardTitles);
		foreach ($listOfServiceAwardTitles as $key => $title) {
			$return .= '<img src="' . $path . $key . '.png", title="' . $title . '" style="width:'. $width . 'px;height:'. $height . 'px;"><ul>';
			foreach ($listOfRecipients[$key] as $userId) {
				$user = get_user_by( 'id', $userId );
				$displayName = $user->get( 'display_name' );
				$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';			
			}
			$return .= '</ul>';
		}

		ksort($listOfOperationalAwardTitles);
		foreach ($listOfOperationalAwardTitles as $key => $title) {
			$return .= '<img src="' . $path . $key . '.png", title="' . $title . '" style="width:'. $width . 'px;height:'. $height . 'px;"><ul>';
			foreach ($listOfRecipients[$key] as $userId) {
				$user = get_user_by( 'id', $userId );
				$displayName = $user->get( 'display_name' );
				$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';			
			}
			$return .= '</ul>';
		}
		
		ksort($listOfCommunityAwardTitles);
		foreach ($listOfCommunityAwardTitles as $key => $title) {
			$return .= '<img src="' . $path . $key . '.png", title="' . $title . '" style="width:'. $width . 'px;height:'. $height . 'px;"><ul>';
			foreach ($listOfRecipients[$key] as $userId) {
				$user = get_user_by( 'id', $userId );
				$displayName = $user->get( 'display_name' );
				$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';			
			}
			$return .= '</ul>';
		}		
	}
	wp_reset_postdata();

	return $return;
}
