<?php

function tcb_roster_public_mission_briefing() {

	$user = wp_get_current_user();

	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	echo '<div class="tcb_mission_briefing">';

	// echo '<h3>Situation</h3>';
	// echo get_field('situation', $postId);

	echo '<h3>Mission</h3>';
	echo get_field('mission', $postId);
	
	echo '<h3>Execution</h3>';
	echo get_field('execution', $postId);
	
	// echo '<h3>Intel</h3>';
	// echo get_field('intel', $postId);
	
	echo '<h3>Environment</h3>';
	while( have_rows('environment', $postId) ): the_row();
		echo '<h4>Map</h4>';
		echo get_sub_field('map');
		
		echo '<h4>Terrain</h4>';
		echo get_sub_field('terrain');
		
		echo '<h4>Time</h4>';
		echo get_sub_field('time');
		
		echo '<h4>Weather</h4>';
		echo get_sub_field('weather');
		
		echo '<h4>IED/Mine Threat</h4>';
		echo get_sub_field('iedmine_threat');
	endwhile;

	echo '<h3>Enemy Forces</h3>';
	echo get_field('enemy_forces', $postId);

	echo '<h3>Friendly Forces</h3>';
	echo get_field('friendly_forces', $postId);

	echo '<h3>Civilians</h3>';
	echo get_field('civilians', $postId);

	echo '<h3>Plan</h3>';
	echo get_field('plan', $postId);

	echo '<h3>Service Support</h3>';
	while( have_rows('service-support', $postId) ): the_row();
		echo '<h4>Vehicles</h4>';
		echo get_sub_field('vehicles');

		echo '<h4>Supplies</h4>';
		echo get_sub_field('supplies');

		echo '<h4>Support</h4>';
		echo get_sub_field('support');

		echo '<h4>Reinforcements</h4>';
		echo get_sub_field('reinforcements');
	endwhile;

	echo '<h3>Rules of Engagement</h3>';
	echo get_field('rules_of_engagement', $postId);
	
	echo '<h3>Command and Signals</h3>';
	echo get_field('command_and_signals', $postId);

	if (tcb_roster_public_find_user_in_slotting ( $postId, $user->user_login )) {
		echo '<br><a href="'. home_url() .'/mission-briefing-edit/?id=' . $postId . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	echo '</div>';

	return;
}
