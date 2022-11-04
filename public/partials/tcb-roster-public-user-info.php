<?php

function tcb_roster_public_user_info($attributes) {

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

	$return = '<h2>'. $displayName . '</h2>';

	// Rank
	$path = '/wordpress/wp-content/plugins/tcb-roster/images/ranks/';

	$rank = get_field( 'rank', $postId );
	if ( !$rank )
		return $return;	

	$return .= '<br><img src="' . $path . $rank['value'] . '.gif", title="' . $rank['label'] . '">';	
	$return .= '<br>Rank: ' . $rank['label'];
	
	// Location
	$return .= '<br>Location: ' . get_field( 'user-location', $userProfile );

	// Dates
	$dateStr = get_field( 'passing_out_date', $postId );
	$date = DateTime::createFromFormat('d/m/Y', $dateStr);
	if ($date) {
		$now = new DateTime('now');
		$interval = $date->diff($now);
		$return .= '<br>Passing out: ' . date_format($date, 'd-m-Y');
		$return .= '<br>Length of service: ' . $interval->format('%y year(s), %m month(s), %d day(s)');
	}

	// LOA
	if (get_field( 'loa', $postId ) == 1) {
		$return .= '<br>Approved LOA';
	}

	// Reserve
	if (get_field( 'reserve', $postId ) == 1) {
		$return .= '<br>Reserve Status';
	}

	// Roles
	$return .= '<br>Administrative roles: WIP';
	// $all_roles = $user->roles; 
	// print_r ( $all_roles );

	// Logs 

	return $return;
}
