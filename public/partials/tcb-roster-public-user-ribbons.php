<?php

function tcb_roster_public_user_ribbons($attributes) {

	$user_id = $_GET['id'];

	if ($user_id != "") {
		$user = get_user_by( 'id', $user_id );
	} else {
		$user = wp_get_current_user();
	}

	$return = '';
	$listOfRibbons = get_field( 'ribbons', 'user_' . $user->ID );
	$path = '/wordpress/wp-content/plugins/tcb-roster/images/ribbons/';

	if ( !$listOfRibbons )
		return $return;

	foreach ( $listOfRibbons as $ribbon ) {
		$return .= '<br><img src="' . $path . $ribbon['value'] . '.png", title="' . $ribbon['label'] . '">';
	}

	// foreach ( $listOfRibbons as $ribbon ) {
	// 	$return .= '<br>' . $ribbon['label'];
	// }

	if (! in_array( 'commendation_admin', wp_get_current_user()->roles))
		return $return;
	
	$return .= '<br><a href="//localhost/wordpress/edit-ribbons/?id=' . $user->ID . '">Edit</a></br>';		

	return $return;
}
