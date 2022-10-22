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

	foreach ( $listOfCourses as $course ) {
		$return .= '<br>' . $course['label'];
	}

	return $return;
}
