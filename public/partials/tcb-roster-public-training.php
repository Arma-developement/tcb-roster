<?php

function tcb_roster_public_training($attributes) {

	$args = array(
		'numberposts'	=> -1,
		'post_type'		=> 'service-record'
	);
	$return = '';

	// Build a list of course titles and course attendance, dynamically from the service records
	$listOfPosts = get_posts( $args );
	if ($listOfPosts) {
		foreach ( $listOfPosts as $post ) {
			setup_postdata( $post );
			$userId = get_field( 'user_id', $post );
			$listOfCourses = get_field( 'courses_completed', $post );
			if ($listOfCourses) {
				foreach ( $listOfCourses as $course ) {
					$listOfAttendance[$course['value']][] = $userId;
					$listOfTitles[$course['value']] = $course['label'];
				}
			}
		}

		ksort($listOfTitles);

		foreach ($listOfTitles as $key => $title) {
			$return .= '<h4>' . $title . '</h4><ul>';
			foreach ($listOfAttendance[$key] as $userId) {
				$user = get_user_by( 'id', $userId );
				$displayName = $user->get( 'display_name' );
				$return .= '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $displayName . '</a></li>';			
			}
			$return .= '</ul>';
		}
	}
	wp_reset_postdata();

	return $return;
}
