<?php

function tcb_roster_public_user_training($attributes) {

	$user_id = $_GET['id'];

	if ($user_id != "") {
		$user = get_user_by( 'id', $user_id );
	} else {
		$user = wp_get_current_user();
	}

	$return = '';
	$listOfCourses = get_field( 'courses_completed', 'user_' . $user->ID );

	if ( !$listOfCourses )
		return $return;

	foreach ( $listOfCourses as $course ) {
		$return .= '<br>' . $course['label'];
	}

	if (! in_array( 'training_admin', wp_get_current_user()->roles))
		return $return;
	
	$return .= '<br><a href="//localhost/wordpress/edit-training-record/?id=' . $user->ID . '">Edit</a></br>';	

	return $return;
}
