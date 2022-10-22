<?php

function tcb_roster_public_user_info($attributes) {

	$user_id = $_GET['id'];

	if ($user_id != "") {
		$user = get_user_by( 'id', $user_id );
	} else {
		$user = wp_get_current_user();
	}

	$return = '<h2>'. $user->get( 'user_nicename' ) . '</h2>';

	// Roles
	$groups_user = new Groups_User( $user->ID );
	// get group objects
	$user_groups = $groups_user->groups;

	print_r ($groups_user);
	print_r ($user_groups);

	//$return .= '<br><p>' + $groups_user + '</p>';
	//$return .= '<br><p>' + $user_groups + '</p>';
	// get group ids (user is direct member)
	//$user_group_ids = $groups_user->group_ids;
	// get group ids (user is direct member or by group inheritance)
	//$user_group_ids_deep = $groups_user->group_ids_deep;	

	// Rank badge

	$return .= '<br>Location: ' . get_field( 'user-location', 'user_' . $user->ID );

	$dateStr = get_field( 'passing_out_date', 'user_' . $user->ID );
	$date = DateTime::createFromFormat('d/m/Y', $dateStr);
	$now = new DateTime('now');
	$interval = $date->diff($now);
	$return .= '<br>Passing out: ' . date_format($date, 'd-m-Y');
	$return .= '<br>Length of service: ' . $interval->format('%y year(s), %m month(s), %d day(s)');;

	if (get_field( 'loa', 'user_' . $user->ID ) == 1) {
		$return .= '<br>Approved LOA';
	}

	if (get_field( 'reserve', 'user_' . $user->ID ) == 1) {
		$return .= '<br>Reserve Status';
	}

	return $return;
}
