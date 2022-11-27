<?php

function tcb_roster_public_edit_service_record($attributes) {
	
	$userId = $_GET['id'];
	if ($userId == "")
		return;

	$user = get_user_by( 'id', $userId );
	$userIdField = 'user_id'; 
	$displayName = $user->get( 'display_name' );
	$userProfile = 'user_' . $userId;
	$postIdField = 'post_id'; 
    $postId = get_field( $postIdField, $userProfile );

	// Security check
	if (($postId != "") && (!current_user_can( 'edit_post', $postId )))
		return;

	echo "<h2>" . $displayName . "</h2>";

	// echo "userID = " . $userId . "<br>";
	// echo "postID = " . $postId . "<br>";

    if ( $postId == "" ) {

		$page_slug = 'service-record-'. $userId; // Slug of the Post
		$new_page = array(
			'post_type'     => 'service-record', 	// Post Type Slug eg: 'page', 'post'
			'post_title'    => $displayName . "'s Service Record",	// Title of the Content
			'post_content'  => 'Test Page Content',	// Content
			'post_status'   => 'publish',			// Post Status
			'post_author'   => 1,					// Post Author ID
			'post_name'     => $page_slug			// Slug of the Post
		);
		
		if (!get_page_by_path( $page_slug, OBJECT, 'service-record')) { // Check If Page Not Exits
			$postId = wp_insert_post($new_page);
			update_field( $postIdField, $postId, $userProfile); 
			update_field( $userIdField, $userId, $postId); 
		}
	}

	// echo "postID = " . $postId . "<br>";

	acf_form( array( 
		'post_id' => $postId,
		'field_groups' => array( 'group_635697195a971', 'group_6356984d2ce21', 'group_6356980addb3c' ),
		'submit_value' => 'Update ' . $displayName . "'s Service Record",
		'updated_message' => false
	) );

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $displayName . "'s Service Record");
	}

	return;
}
