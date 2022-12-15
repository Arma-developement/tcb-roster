<?php

function tcb_roster_public_mission_briefing_edit() {

	$user = wp_get_current_user();

	// Early out for no post
	if (!array_key_exists('id', $_GET))
		return; 
		
	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	echo '<div class="tcb_mission_briefing_edit">';

	acf_form ( 
		array (
			'name' => 'submit-plan',
			'post_id' => $postId,
			'fields' => array ('brief_mission', 'brief_execution', 'brief_plan', 'brief_actions_on', 'brief_rules_of_engagement', 'brief_command_and_signals')
		)
	);

	// if ( function_exists( 'SimpleLogger' ) ) {
	// 	SimpleLogger()->info( 'Edited ' . get_the_title($postId) . ' via the Mission Admin Panel');
	// }

	echo '</div>';

	return;
}
