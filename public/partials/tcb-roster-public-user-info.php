<?php

function tcb_roster_public_user_info($attributes) {

	if (!array_key_exists('id', $_GET))
		return; 

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

	$isAdmin = in_array( 'administrator', wp_get_current_user()->roles);

	$return = '<div class="tcb_user_info">';
	$return .= '<h2>'. $displayName . '</h2>';

	// Rank
	$path = plugins_url() . '/tcb-roster/images/ranks/';

	$rank = get_field( 'rank', $postId );
	if ( !$rank )
		return $return;

	$width = 144;
	$height = 240;
	$return .= '<img src="' . $path . $rank['value'] . '.gif", title="' . $rank['label'] . '" style="width:'. $width . 'px;height:'. $height . 'px;">';
	$return .= '<ul>';	
	$return .= '<li>Rank: ' . $rank['label'] . '</li>';
	
	// Location
	$location = get_field( 'user-location', $userProfile );
	if ($location) {
		$return .= '<li>Location: ' . $location . '</li>';
	}

	// Steam ID
	$steamID = get_field( 'steam_id', $postId );
	if ($isAdmin && $steamID) {
		$return .= '<li>Steam ID: ' . $steamID . '</li>';
	}

	// Discord ID
	$discordID = get_field( 'discord_id', $postId );
	if ($isAdmin && $discordID) {
		$return .= '<li>Discord ID: ' . $discordID . '</li>';
	}

	// Dates
	if ( $rank['value'] == 'Rct' ) {
		$dateStr = get_field( 'attestation_date', $postId );
		$date = DateTime::createFromFormat('d/m/Y', $dateStr);
		if ($date) {
			$now = new DateTime('now');
			$interval = $date->diff($now);
			$return .= '<li>Attestation: ' . date_format($date, 'd-m-Y') . '</li>';
			$return .= '<li>Length of recruit period: ' . $interval->format('%y year(s), %m month(s), %d day(s)') . '</li>';
		}
	} else {
		$dateStr = get_field( 'passing_out_date', $postId );
		$date = DateTime::createFromFormat('d/m/Y', $dateStr);
		if ($date) {
			$now = new DateTime('now');
			$interval = $date->diff($now);
			$return .= '<li>Passing out: ' . date_format($date, 'd-m-Y') . '</li>';
			$return .= '<li>Length of service: ' . $interval->format('%y year(s), %m month(s), %d day(s)') . '</li>';
		}
	}

	// LOA
	if ((get_field( 'loa', $postId ) == 1) && ($rank['value'] != "Res")) {
		$return .= '<li>Approved LOA' . '</li>';
	}

	$return .= '</ul>';

	// Duties
	$listOfDuties = get_field( 'duties', $postId );

	if ( $listOfDuties ) {
		$return .= '<h3>Administrative duties</h3><ul>';
		foreach ( $listOfDuties as $duty ) {
			$return .= '<li>' . $duty['label'] . '</li>';
		}
		$return .= '</ul>';
	}
	$return .= '</div>';
	
	return $return;
}
