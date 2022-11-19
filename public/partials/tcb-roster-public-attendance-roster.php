<?php

function tcb_roster_public_attendance_roster($attributes) {

	// Ensure user is logged in
	$currentUser = wp_get_current_user();
	if (!$currentUser)
		return;
	$the_current_user_id = $currentUser->ID;

	// Early out if no post
	$post_id = get_queried_object_id();
	if (!$post_id)
		return;

	// Early out if no entrys in rsvp field
	if(! have_rows('rsvp') )
		return;		

	// Find users in registered fields
	$registeredUsers = [];
	$fields = get_field('rsvp');
	if (!$fields)
		return;
	foreach ($fields as $field) {
		if ($field['user'])
			$registeredUsers = array_merge($registeredUsers, $field['user']);
	}
	$imRsvpd = in_array($the_current_user_id, $registeredUsers);

	//print_r ($registeredUsers);
		
	echo '<div id="attendanceRoster">';
	echo '<h2>Attendance</h2>';
	echo '<div class="wrap">';
	echo '<hr>';

	$rsvpCols = get_field_objects();
	$rsvpRow = 0;
	while( have_rows('rsvp') ) : the_row();
		$rsvp = get_row();

		echo '<div class="attendanceCol" id="rsvpRow-' . $rsvpRow . '">';
		echo '<h4>'. get_sub_field( 'label' ) . '</h4>';

		$imRsvpdInThisList = false;
		$users = get_sub_field('user');	
		
		if ($users) {
			// Check if user in list
			if (in_array($the_current_user_id, $users))
				$imRsvpdInThisList = true;

			// Display list
			echo '<ul>';
			foreach( $users as $user ) {
				echo '<li><img src="<' . get_avatar_url($user) . '" alt="author-avatar">' . get_userdata($user)->user_login . '</li>';
			}
			echo '</ul>';
		}
		
		// Display buttons only if unregistered or registered in this list
		if (!$imRsvpd || $imRsvpdInThisList) {
			echo '<form class="rsvpFormUnregister" id="rsvpFormUnregister-' . $rsvpRow . '">';
			echo '<input type="hidden" name="post-id" class="rsvpPostID" value="' . $post_id . '">';
			echo '<input type="hidden" name="user-id" class="rsvpUserID" value="' . $the_current_user_id . '">';
			echo '<input type="hidden" name="member-name" class="rsvpMemberName" value="' . wp_get_current_user()->user_login . '">';
			echo '<input type="hidden" name="rsvp-path" class="rsvpAcfPath" value="' . $rsvpCols['rsvp']['key'] . ',' . $rsvpRow . ',' . $rsvpCols['rsvp']['sub_fields']['1']['key'] . '">';
			echo '<input type="hidden" name="all-users" class="rsvpAllUsers" value=' . json_encode($fields[$rsvpRow]) . '>';

			if (!$imRsvpd)
				echo '<input type="submit" value="Register"></form>';
			else
				echo '<input type="submit" value="Unregister"></form>';
		}
		echo '</div>';
		$rsvpRow++;
	endwhile;
	echo '</div></div>';
}
