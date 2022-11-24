<?php

function tcb_roster_public_mission_admin() {

	$user = wp_get_current_user();
	if ((! in_array( 'editor', $user->roles)) && (! in_array( 'administrator',$user->roles)) && (! in_array( 'mission_admin', $user->roles)))
		return;

	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	$return = '';

	acfe_form ( 
		array (
			'name' => 'send-announcement',
			'post_id' => $postId
		)
	);

	acfe_form ( 
		array (
			'name' => 'send-password',
			'post_id' => $postId
		)
	);


	// if ( function_exists( 'SimpleLogger' ) ) {
	// 	SimpleLogger()->info( 'Edited ' . $displayName . "'s Commendations");
	// }

	return;
}
