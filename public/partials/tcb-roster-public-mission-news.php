<?php

function tcb_roster_public_mission_news() {

	$user = wp_get_current_user();
	if ((! in_array( 'editor', $user->roles)) && (! in_array( 'administrator',$user->roles)) && (! in_array( 'mission_admin', $user->roles)))
		return;

	// Early out for no post
	if (!array_key_exists('id', $_GET))
		return; 

	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	ob_start();

	echo '<div class="tcb_mission_news">';

	acfe_form ( 
		array (
			'name' => 'submit_mission_news',
			'post_id' => $postId,
			'return' => wp_get_referer(),
			'updated_message' => false
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title($postId) . ' via the Mission News Panel');
	}

	echo '</div>';

	return ob_get_clean();
}
