<?php

function tcb_roster_public_mission_briefing() {

	$user = wp_get_current_user();

	// Early out for no post
	if (!array_key_exists('id', $_GET))
		return; 

	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	//error_log( print_r("meta data: " . json_encode( get_post_meta( $postId ) ), TRUE ));

	$return = '<div class="tcb_mission_briefing">';

	$return .= '<h3>Mission</h3>';
	$return .=  get_field('brief_mission', $postId);
	
	$return .=  '<h3>Execution</h3>';
	$return .=  get_field('brief_execution', $postId);
	
	$return .=  '<h3>Environment</h3>';

	$return .=  '<h4>Map</h4>';
	$return .=  get_field('brief_map', $postId);
	
	$return .=  '<h4>Terrain</h4>';
	$return .=  get_field('brief_terrain', $postId);
	
	$return .=  '<h4>Time</h4>';
	$return .=  get_field('brief_start_time', $postId);
	
	$return .=  '<h4>Weather</h4>';
	$return .=  get_field('brief_weather', $postId);
	
	$return .=  '<h4>IED/Mine Threat</h4>';
	$return .=  get_field('brief_iedmine_threat', $postId);

	$return .=  '<h3>Enemy Forces</h3>';
	$return .=  get_field('brief_enemy_forces', $postId);

	$return .=  '<h3>Friendly Forces</h3>';
	$return .=  get_field('brief_friendly_forces', $postId);

	$return .=  '<h3>Civilians</h3>';
	$return .=  get_field('brief_civilians', $postId);

	$return .=  '<h3>Plan</h3>';
	$return .=  get_field('brief_plan', $postId);

	$return .=  '<h3>Service Support</h3>';

	$return .=  '<h4>Vehicles</h4>';
	$return .=  get_field('brief_vehicles', $postId);

	$return .=  '<h4>Supplies</h4>';
	$return .=  get_field('brief_supplies', $postId);

	$return .=  '<h4>Support</h4>';
	$return .=  get_field('brief_support', $postId);

	$return .=  '<h4>Reinforcements</h4>';
	$return .=  get_field('brief_reinforcements', $postId);

	$return .=  '<h3>Actions On</h3>';
	$return .=  get_field('brief_actions_on', $postId);
	$return .=  '<p><a href="' . home_url() .'/information-centre/generic-actions-on/">SOP: Actions On</a></p>';

	$return .=  '<h3>Rules of Engagement</h3>';
	$return .=  get_field('brief_rules_of_engagement', $postId);
	$return .=  '<p><a href="' . home_url() .'/information-centre/rules-of-engagement/">SOP: ROE<br></a></p>';
	
	$return .=  '<h3>Command and Signals</h3>';
	$return .=  get_field('brief_command_and_signals', $postId);
	$return .=  '<p><a href="' . home_url() .'/information-centre/command-and-signals-tfar/">SOP: C&S<br></a></p>';

	if (tcb_roster_public_find_user_in_slotting ( $postId, $user->user_login )) {
		$return .=  '<br><br><a href="'. home_url() .'/mission-briefing-edit/?id=' . $postId . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	$return .=  '</div>';

	return $return;
}
