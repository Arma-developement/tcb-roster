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

	echo '<div class="tcb_mission_briefing">';

	echo '<h3>Mission</h3>';
	echo get_field('brief_mission', $postId);
	
	echo '<h3>Execution</h3>';
	echo get_field('brief_execution', $postId);
	
	echo '<h3>Environment</h3>';

	echo '<h4>Map</h4>';
	echo get_field('brief_map', $postId);
	
	echo '<h4>Terrain</h4>';
	echo get_field('brief_terrain', $postId);
	
	echo '<h4>Time</h4>';
	echo get_field('brief_start_time', $postId);
	
	echo '<h4>Weather</h4>';
	echo get_field('brief_weather', $postId);
	
	echo '<h4>IED/Mine Threat</h4>';
	echo get_field('brief_iedmine_threat', $postId);

	echo '<h3>Enemy Forces</h3>';
	echo get_field('brief_enemy_forces', $postId);

	echo '<h3>Friendly Forces</h3>';
	echo get_field('brief_friendly_forces', $postId);

	echo '<h3>Civilians</h3>';
	echo get_field('brief_civilians', $postId);

	echo '<h3>Plan</h3>';
	echo get_field('brief_plan', $postId);

	echo '<h3>Service Support</h3>';

	echo '<h4>Vehicles</h4>';
	echo get_field('brief_vehicles', $postId);

	echo '<h4>Supplies</h4>';
	echo get_field('brief_supplies', $postId);

	echo '<h4>Support</h4>';
	echo get_field('brief_support', $postId);

	echo '<h4>Reinforcements</h4>';
	echo get_field('brief_reinforcements', $postId);

	echo '<h3>Actions On</h3>';
	echo get_field('brief_actions_on', $postId);
	echo '<br><a href="' . home_url() .'/information-centre/generic-actions-on/">SOP: Actions On<br></a>';

	echo '<h3>Rules of Engagement</h3>';
	echo get_field('brief_rules_of_engagement', $postId);
	echo '<br><a href="' . home_url() .'/information-centre/rules-of-engagement/">SOP: ROE<br></a>';
	
	echo '<h3>Command and Signals</h3>';
	echo get_field('brief_command_and_signals', $postId);
	echo '<br><a href="' . home_url() .'/information-centre/command-and-signals-tfar/">SOP: C&S<br></a>';

	if (tcb_roster_public_find_user_in_slotting ( $postId, $user->user_login )) {
		echo '<br><br><a href="'. home_url() .'/mission-briefing-edit/?id=' . $postId . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	echo '</div>';

	return;
}
