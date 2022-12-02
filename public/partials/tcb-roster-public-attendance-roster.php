<?php

function tcb_roster_public_attendance_roster($attributes) {

	// Ensure user is logged in
	$currentUser = wp_get_current_user();
	if (!$currentUser)
		return;
	$currentUserID = $currentUser->ID;

	// Early out if no post
	$post_id = get_queried_object_id();
	if (!$post_id)
		return;

	// Output the briefing
	echo '<div class="tcb_briefing" >';

	echo '<h3>Modset</h3>';
	echo get_field('modset');

	echo '<h3>Situation</h3>';
	echo get_field('situation');

	echo '<h3>Mission</h3>';
	echo get_field('mission');
	
	echo '<h3>Execution</h3>';
	echo get_field('execution');
	
	echo '<h3>Intel</h3>';
	echo get_field('intel');
	
	while( have_rows('environment') ): the_row();
		echo '<h3>Map</h3>';
		echo get_sub_field('map');
		
		// echo '<h4>Terrain</h4>';
		// echo get_sub_field('terrain');
		
		echo '<h3>Time</h3>';
		echo get_sub_field('time');
		
		// echo '<h4>Weather</h4>';
		// echo get_sub_field('weather');
		
		// echo '<h4>IED/Mine Threat</h4>';
		// echo get_sub_field('iedmine_threat');
	endwhile;

	echo '<h3>Enemy Forces</h3>';
	echo get_field('enemy_forces');

	echo '<h3>Friendly Forces</h3>';
	echo get_field('friendly_forces');

	// echo '<h3>Civilians</h3>';
	// echo get_field('civilians');

	// echo '<h3>Service Support</h3>';
	// while( have_rows('service-support') ): the_row();
	// 	echo '<h4>Vehicles</h4>';
	// 	echo get_sub_field('vehicles');

	// 	echo '<h4>Supplies</h4>';
	// 	echo get_sub_field('supplies');

	// 	echo '<h4>Support</h4>';
	// 	echo get_sub_field('support');

	// 	echo '<h4>Reinforcements</h4>';
	// 	echo get_sub_field('reinforcements');
	// endwhile;

	// echo '<h3>Rules of Engagement</h3>';
	// echo get_field('rules_of_engagement');
	
	// echo '<h3>Command and Signals</h3>';
	// echo get_field('command_and_signals');

	echo '</div>';		

	// Early out if no entrys in rsvp field
	if(! have_rows('rsvp') )
		return;		

	// print_r ( get_field('rsvp') );
	// print_r ( get_field('time_stamp') );
		
	echo '<div id="attendanceRoster">';
	echo '<h2>Attendance</h2>';
	echo '<div class="wrap">';

	while( have_rows('rsvp') ) : the_row();
		$i = get_row_index();

		echo '<div class="attendanceCol" id="rsvpRow-' . $i . '">';
		echo '<h4>'. get_sub_field( 'label' ) . '</h4>';

		$alreadyRegistered = false;
		$userIds = get_sub_field('user');	
	
		if ($userIds) {
			// Check if user in list
			if (in_array($currentUserID, $userIds))
				$alreadyRegistered = true;

			// Display list
			echo '<ul>';
			foreach( $userIds as $userId ) {
				$userData = get_userdata($userId);
				$avatar = get_avatar_url($userId);
				$avatar = false;
				if ($avatar)
					echo '<li><img src="<' . $avatar . '" alt="author-avatar"><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $userData->display_name . '</a></li>';
				else
					echo '<li><a href="'. home_url() .'/user-info/?id=' . $userId . '">' . $userData->display_name . '</a></li>';
			}
			echo '</ul>';
		}
		
		echo '<form class="rsvpFormUnregister" id="rsvpFormUnregister-' . $i . '">';
		echo '<input type="hidden" name="postId" class="rsvpPostID" value="' . $post_id . '">';
		echo '<input type="hidden" name="userId" class="rsvpUserID" value="' . $currentUserID . '">';
		echo '<input type="hidden" name="selection" class="rsvpSelection" value="' . $i . '">';

		if ($alreadyRegistered)
			echo '<input type="submit" value="Unregister"></form>';
		else
			echo '<input type="submit" value="Register"></form>';
	
		echo '</div>';
	endwhile;
	echo '</div></div>';
}
