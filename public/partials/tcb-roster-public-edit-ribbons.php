<?php

function tcb_roster_public_edit_ribbons($attributes) {

	if ((! in_array( 'commendation_admin', wp_get_current_user()->roles)) && (! in_array( 'administrator', wp_get_current_user()->roles)))
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
		'field_groups' => array( 'group_6356980addb3c' ),
		'return' => add_query_arg( 'id', $user->ID, '//localhost/wordpress/user-info' ),
		'submit_value' => 'Update ' . $displayName . "'s Commendations",
		'updated_message' => false
	 );
	
	acf_form( $myoptions );

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $displayName . "'s Commendations");
	}

	return;
}
