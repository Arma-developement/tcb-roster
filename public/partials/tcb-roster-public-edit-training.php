<?php

function tcb_roster_public_edit_training($attributes) {

	if ((! in_array( 'training_admin', wp_get_current_user()->roles)) && (! in_array( 'administrator', wp_get_current_user()->roles)))
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

	// echo "userID = " . $userId . "<br>";
	// echo "postID = " . $postId . "<br>";	

	if ( $postId == "" )
		return;

	echo "<h2>" . $displayName . "</h2>";

	$myoptions = array( 
		'post_id' => $postId,
		'field_groups' => array( 'group_6356984d2ce21' ),
		'return' => add_query_arg( 'id', $user->ID, home_url() . '/user-info' ),
		'submit_value' => 'Update ' . $displayName . "'s Training Record",
		'updated_message' => false
	 );
	
	acf_form( $myoptions );	

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $displayName . "'s Training Record");
	}

	return;
}
