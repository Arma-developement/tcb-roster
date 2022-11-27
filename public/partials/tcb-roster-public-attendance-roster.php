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
