<?php

function tcb_roster_public_mission_admin() {

	$user = wp_get_current_user();
	if ((! in_array( 'editor', $user->roles)) && (! in_array( 'administrator',$user->roles)) && (! in_array( 'mission_admin', $user->roles)))
		return;

	// Early out for no post
	$postId = $_GET['id'];
	if ($postId == "")
		return;

	$myoptions = array( 
		'post_id' => $postId,
		'field_groups' => array( 'group_637bd56b40d34' ),
		'fields' => array('announcement'),
		//'return' => add_query_arg( 'id', $user->ID, home_url() . '/user-info' ),
		'submit_value' => 'Send announcement to Discord',
		'updated_message' => 'Announcement sent'
	);
	acf_form( $myoptions );


	
	$myoptions = array( 
		'post_id' => $postId,
		'field_groups' => array( 'group_637bd56b40d34' ),
		'fields' => array('password'),
		//'return' => add_query_arg( 'id', $user->ID, home_url() . '/user-info' ),
		'submit_value' => 'Send Password',
		'updated_message' => 'Password sent'
	);
	acf_form( $myoptions );


	// if ( function_exists( 'SimpleLogger' ) ) {
	// 	SimpleLogger()->info( 'Edited ' . $displayName . "'s Commendations");
	// }

	return;
}
