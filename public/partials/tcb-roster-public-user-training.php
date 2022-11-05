<?php

function tcb_roster_public_user_training($attributes) {

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

	$return = '';
	$listOfCourses = get_field( 'courses_completed', $postId );

	if ( $listOfCourses ) {
		$return .= '<ul>';
		foreach ( $listOfCourses as $course ) {
			$return .= '<li>' . $course['label'] . '</li>';
		}
		$return .= '</ul>';		
	}

	return $return;
}
