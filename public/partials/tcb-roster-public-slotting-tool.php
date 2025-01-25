<?php

function tcb_roster_public_slotting_tool($attributes) {

	// Ensure user is logged in
	$currentUser = wp_get_current_user();
	if (!$currentUser)
		return;
	$currentUserID = $currentUser->ID;
	$currentLogin = $currentUser->user_login;

	// Early out if no post
	$post_id = get_queried_object_id();
	if (!$post_id)
		return;

	// Early out for subscribers on private missions
	if ((in_array( 'subscriber', $currentUser->roles)) && (get_field('brief_mission_type', $post_id) == 'private'))
		return;

	// Early out if no entries in slots field
	if(! have_rows('slots') )
		return;

	// Calculate number of users registered as attending
	$attendance = 0;
	if ( have_rows('rsvp') ) {
		while( have_rows('rsvp') ) : the_row();
			$userIds = get_sub_field('user');
			if ($userIds) {
				$attendance = count ($userIds);
			}
			break;
		endwhile; 
	}

	echo '<div id="slotTool"><div class="inner">';
	echo '<h2>Priority placements</h2>';
	echo '<p>Button to left of slot name appears when logged in.<br>Avatar appears when member is slotted</p>';

	// Loop through slot rows
	while( have_rows('slots') ) : the_row();
		$i = get_row_index();

		// Continue to next slot if unit is empty
		if (! have_rows ('unit') )
			continue;
		
		// Loop through unit rows
		while( have_rows('unit') ) : the_row();
			$j = get_row_index();

			echo '<div class="unit" >';
			echo '<h3>' . get_sub_field('name') . '</h3>';

			// Continue to next unit if slot is empty
			if ( !have_rows('slot') )
				continue;

			// Loop through rows.
			while( have_rows('slot') ) : the_row(); 	
				$k = get_row_index();

				// Get profile pic for slotted member 
				$profilePic = '';
				$user_id = 1;
				$slottedMemberName = get_sub_field('slot_member');
				$attendanceThreshold = get_sub_field('attendance_threshold');
				$isLocked = $attendance < $attendanceThreshold;
				$isDisabled = (($slottedMemberName !== '') && ($slottedMemberName !== $currentLogin)) || $isLocked;
				$user = get_user_by('login', $slottedMemberName);
				if ($user) {
					$user_id = $user->ID;
					$profilePic = get_avatar_url($user_id);
					$displayName = $user->display_name;
				} else {
					$displayName = '';
				}

				echo '<div class="slotToolSlot" id="slotToolSlot-' . $j . '-' . $k . '">';
				echo '<form class="slotForm">';
				echo '<input type="hidden" name="postId" class="postID" value="' . $post_id . '">';
				echo '<input type="hidden" name="userId" class="userID" value="' . $currentUserID . '">';
				echo '<input type="hidden" name="slot" class="slot" value="' . $i . ',' . $j . ',' . $k . '">';
				echo '<input class="slotIcon ' . ($isDisabled ? 'disabled"' : '"') . 'type="submit"';
				echo ' style="background-image:url(' . ($profilePic ? $profilePic : '') . ')"';
				echo '>';
				echo '</form>';

				if ($isLocked) {
					if ($attendanceThreshold < 999) {
						echo '<strong>' . get_sub_field('slot_name') . '</strong>  -  locked (requires ' . $attendanceThreshold . ' attendees)<br>';
					} else {
						echo '<strong>' . get_sub_field('slot_name') . "</strong>  -  locked (command's decision)<br>";
					}
				} else {
					echo '<strong>' . get_sub_field('slot_name') . '</strong>  -  <span class="slotMember"><a href="'. home_url() .'/user-info/?id=' . $user_id . '">' . $displayName . '</a></span><br>';
				}
				echo '</div>';	
			endwhile;
			echo '</div>'; 
		endwhile;
	endwhile;
	echo '</div></div>';

	echo '<div class="slotToolButtons" id="slotToolButtons" >';
	$roles = $currentUser->roles;
	if (in_array( 'mission_admin', $roles) || in_array( 'administrator', $roles) || in_array( 'editor', $roles)) {
		echo '<a href="'. home_url() .'/mission-admin-panel/?id=' . $post_id . '" class="button button-secondary">Mission Admin Panel</a>';
	}
	
	if (tcb_roster_public_find_user_in_slotting ( $post_id, $currentUser->user_login )) {
		echo '<a href="'. home_url() .'/mission-briefing/?id=' . $post_id . '" class="button button-secondary">Mission Briefing</a>';
	}
	echo '</div>';
}
