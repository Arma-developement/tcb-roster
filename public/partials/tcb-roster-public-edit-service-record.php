<?php

function tcb_roster_public_edit_service_record($attributes) {

	echo "HERE<br>";

	// if (! is_admin())
	// 	return;

	echo "HERE2<br>";
	
	$user_id = $_GET['id'];
	if ($user_id == "")
		return;

	echo "HERE3<br>";

	$user = get_user_by( 'id', $user_id );
    $postID = get_field( 'post_id', 'user_' . $user_id );

	echo "userID = " . $user_id . "<br>";
	echo "postID = " . $postID . "<br>";

    if ( $postID == "" ) {

		echo "HERE4<br>";

		$page_slug = 'service-record-'. $user_id; // Slug of the Post
		$new_page = array(
			'post_type'     => 'service_record', 	// Post Type Slug eg: 'page', 'post'
			'post_title'    => 'Service Record',	// Title of the Content
			'post_content'  => 'Test Page Content',	// Content
			'post_status'   => 'publish',			// Post Status
			'post_author'   => 1,					// Post Author ID
			'post_name'     => $page_slug			// Slug of the Post
		);
		
		if (!get_page_by_path( $page_slug, OBJECT, 'page')) { // Check If Page Not Exits

			echo "HERE5<br>";

			$postID = wp_insert_post($new_page);
			update_field( 'post_id', 'user_' . $user_id, $postID); 

			echo "HERE5 " . $postID . "<br>";
		}
	}

//		'field_groups' => array( 'key' => 'group_6356980addb3c' ),

	$myoptions = array( 
		'post_id' => 'user_' . $user->ID,
		'field_groups' => array( 'key' => 'group_6356980addb3c' ),
		'return' => add_query_arg( 'id', $user->ID, '//localhost/wordpress/user-info' ),
		'submit_value' => 'Update ' . $user->get( 'display_name' ) . "'s Commendations",
		'updated_message' => __("Commendations Updated", 'acf'),
	 );
	
	acf_form( $myoptions );	

	return '';
}
