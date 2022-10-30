<?php

function tcb_roster_public_user_info($attributes) {

	$user_id = $_GET['id'];

	if ($user_id != "") {
		$user = get_user_by( 'id', $user_id );
	} else {
		$user = wp_get_current_user();
	}
	
	$_SESSION['foreign_user_id'] = $user->ID;

	$return = '<h2>'. $user->get( 'display_name' ) . '</h2>';

	// Rank
	$path = '/wordpress/wp-content/plugins/tcb-roster/images/ranks/';

	$rank = get_field( 'rank', 'user_' . $user->ID );
	if ( !$rank )
		return $return;	

	$return .= '<br><img src="' . $path . $rank['value'] . '.gif", title="' . $rank['label'] . '">';	
	$return .= '<br>Rank: ' . $rank['label'];
	
	// Location
	$return .= '<br>Location: ' . get_field( 'user-location', 'user_' . $user->ID );

	// Dates
	$dateStr = get_field( 'passing_out_date', 'user_' . $user->ID );
	$date = DateTime::createFromFormat('d/m/Y', $dateStr);
	if ($date) {
		$now = new DateTime('now');
		$interval = $date->diff($now);
		$return .= '<br>Passing out: ' . date_format($date, 'd-m-Y');
		$return .= '<br>Length of service: ' . $interval->format('%y year(s), %m month(s), %d day(s)');
	}

	// LOA
	if (get_field( 'loa', 'user_' . $user->ID ) == 1) {
		$return .= '<br>Approved LOA';
	}

	// Reserve
	if (get_field( 'reserve', 'user_' . $user->ID ) == 1) {
		$return .= '<br>Reserve Status';
	}

	// Roles
	$return .= '<br>Administrative roles: WIP';
	// $all_roles = $user->roles; 
	// print_r ( $all_roles );

	// Logs 

	return $return;
}
