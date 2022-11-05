<?php

function tcb_roster_public_user_ribbons($attributes) {

	$userId = $_GET['id'];

	if ($userId != "") {
		$user = get_user_by( 'id', $userId );
	} else {
		$user = wp_get_current_user();
		$userId = $user->ID;
	}

	$displayName = $user->get( 'display_name' );
	$userProfile = 'user_' . $userId;
	$postIdField = 'post_id'; 
	$postId = get_field( $postIdField, $userProfile );

	$return = '';

	$width = 350 / 2;
	$height = 94 / 2;

	$listOfRibbons = get_field( 'service_awards', $postId );
	$path = plugins_url() . '/tcb-roster/images/ribbons/';

	if ( $listOfRibbons ) {
		foreach ( $listOfRibbons as $ribbon ) {
			$return .= '<img src="' . $path . $ribbon['value'] . '.png", title="' . $ribbon['label'] . '" style="width:'. $width . 'px;height:'. $height . 'px;">';
		}
	}

	$listOfRibbons = get_field( 'operational_awards', $postId );

	$return .= '<br>';

	if ( $listOfRibbons ) {
		foreach ( $listOfRibbons as $ribbon ) {
			$return .= '<img src="' . $path . $ribbon['value'] . '.png", title="' . $ribbon['label'] . '" style="width:'. $width . 'px;height:'. $height . 'px;">';
		}
	}
	
	$listOfRibbons = get_field( 'community_awards', $postId );

	$return .= '<br>';

	if ( $listOfRibbons ) {
		foreach ( $listOfRibbons as $ribbon ) {
			$return .= '<img src="' . $path . $ribbon['value'] . '.png", title="' . $ribbon['label'] . '" style="width:'. $width . 'px;height:'. $height . 'px;">';
		}
	}

	// foreach ( $listOfRibbons as $ribbon ) {
	// 	$return .= '<br>' . $ribbon['label'];
	// }

	if ((! in_array( 'commendation_admin', wp_get_current_user()->roles)) && (! in_array( 'administrator', wp_get_current_user()->roles)))
		return $return;
	
	$return .= '<br><a href="'. home_url() .'/edit-ribbons/?id=' . $userId . '" class="button button-secondary">Edit Commendations</a><br>';

	return $return;
}
