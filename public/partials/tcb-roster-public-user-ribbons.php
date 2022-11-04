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
	$listOfRibbons = get_field( 'ribbons', $postId );
	$path = '/wordpress/wp-content/plugins/tcb-roster/images/ribbons/';

	if ( !$listOfRibbons )
		return $return;

	foreach ( $listOfRibbons as $ribbon ) {
		$return .= '<br><img src="' . $path . $ribbon['value'] . '.png", title="' . $ribbon['label'] . '">';
	}

	// foreach ( $listOfRibbons as $ribbon ) {
	// 	$return .= '<br>' . $ribbon['label'];
	// }

	if ((! in_array( 'commendation_admin', wp_get_current_user()->roles)) && (! in_array( 'administrator', wp_get_current_user()->roles)))
		return $return;
	
	$return .= '<br><a href="//localhost/wordpress/edit-ribbons/?id=' . $userId . '">Edit</a></br>';		

	return $return;
}
