<?php

function tcb_roster_public_mission_briefing_edit() {

	$user = wp_get_current_user();

	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	echo '<div class="tcb_mission_briefing_edit">';

	acf_form ( 
		array (
			'name' => 'submit-plan',
			'post_id' => $postId,
			'fields' => array ('mission', 'execution', 'plan')
		)
	);

	// acfe_form ( 
	// 	array (
	// 		'name' => 'send-password',
	// 		'post_id' => $postId
	// 	)
	// );

	// if ( function_exists( 'SimpleLogger' ) ) {
	// 	SimpleLogger()->info( 'Edited ' . get_the_title($postId) . ' via the Mission Admin Panel');
	// }

	echo '</div>';

	return;
}
