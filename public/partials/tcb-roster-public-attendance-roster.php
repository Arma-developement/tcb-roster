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

	echo '<h2>Mission Details</h2>';

	echo '<h3>Situation</h3>';
	echo get_field('brief_situation');

	echo '<h3>Mission</h3>';
	echo get_field('brief_mission');
	
	// Early out for subscribers on private missions
	if ((in_array( 'subscriber', $currentUser->roles)) && (get_field('brief_mission_type') == 'private')) {
		echo '<br><br><p>This is a 3CB members only mission</p>';
		echo '<p>For information about 3CB, click <a href="'. home_url() .'/information-centre/about-3cb">here</a></p>';
		echo '<p>Interested in joining 3CB, click <a href="'. home_url() .'/information-centre/the-recruitment-process">here</a></p>';
		echo '</div>';
		return;
	}

	echo '<h3>Execution</h3>';
	echo get_field('brief_execution');
	
	echo '<h3>Intel</h3>';
	echo get_field('brief_intel');
	
	echo '<h3>Map</h3>';
	echo get_field('brief_map');
	
	// echo '<h3>Terrain</h3>';
	// echo get_field('terrain');
	
	echo '<h3>Time</h3>';
	echo get_field('brief_start_time');
	
	// echo '<h3>Weather</h3>';
	// echo get_field('weather');
	
	// echo '<h3>IED/Mine Threat</h3>';
	// echo get_field('iedmine_threat');

	echo '<h3>Enemy Forces</h3>';
	echo get_field('brief_enemy_forces');

	echo '<h3>Friendly Forces</h3>';
	echo get_field('brief_friendly_forces');

	// echo '<h3>Civilians</h3>';
	// echo get_field('civilians');

	// 	echo '<h3>Vehicles</h3>';
	// 	echo get_field('vehicles');

	// 	echo '<h3>Supplies</h3>';
	// 	echo get_field('supplies');

	// 	echo '<h3>Support</h3>';
	// 	echo get_field('support');

	// 	echo '<h3>Reinforcements</h3>';
	// 	echo get_field('reinforcements');

	// echo '<h3>Rules of Engagement</h3>';
	// echo get_field('rules_of_engagement');
	
	// echo '<h3>Command and Signals</h3>';
	// echo get_field('command_and_signals');

	echo '<h3>Modset</h3>';
	echo get_field('brief_modset');

	echo '</div>';		

	// Early out if no entrys in rsvp field
	if(! have_rows('rsvp') )
		return;		

	// print_r ( get_field('rsvp') );
	// print_r ( get_field('time_stamp') );
		
	echo '<div id="attendanceRoster"><div class="inner">';
	echo '<h2>Attendance</h2>';
	echo '<div class="wrap">';

	while( have_rows('rsvp') ) : the_row();
		$i = get_row_index();

		echo '<div class="attendanceCol" id="rsvpRow-' . $i . '">';
		echo '<h3>'. get_sub_field( 'label' ) . '</h3>';

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
	echo '</div></div></div>';
}
