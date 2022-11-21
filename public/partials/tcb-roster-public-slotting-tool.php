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

	// Early out if no entrys in slots field
	if(! have_rows('slots') )
		return;

	echo '<div id="slotTool" >';
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
				$slottedMemberName = get_sub_field('slot_member');
				$isDisabled = ($slottedMemberName !== '') && ($slottedMemberName !== $currentLogin);				
				$user = get_user_by('login', $slottedMemberName);
				if ($user) {
					$user_id = $user->ID;
					$profilePic = get_avatar_url($user_id);
				}

				echo '<div class="slotToolSlot" id="slotToolSlot-' . $j . '-' . $k . '">';
				echo '<form class="slotForm">';
				echo '<input type="hidden" name="post-id" class="postID" value="' . $post_id . '">';
				echo '<input type="hidden" name="user-id" class="userID" value="' . $currentUserID . '">';
				echo '<input type="hidden" name="slot" class="slot" value="' . $i . ',' . $j . ',' . $k . '">';
				echo '<input class="slotIcon ' . ($isDisabled ? 'disabled"' : '"') . 'type="submit"';
				echo ' style="background-image:url(' . ($profilePic ? $profilePic : '') . ')"';
				echo '>';
				echo '</form>';

				echo '<strong>' . get_sub_field('slot_name') . '</strong>  -  <span class="slotMember">' . get_sub_field('slot_member') . '</span><br>';
				echo '</div>';	
			endwhile;
			echo '</div>'; 
		endwhile;
	endwhile;
	echo '</div>';
	
	$roles = $currentUser->roles;
	if (in_array( 'mission_admin', $roles) || in_array( 'administrator', $roles) || in_array( 'editor', $roles)) {
		echo '<br><a href="'. home_url() .'/mission-admin/?id=' . $post_id . '" class="button button-secondary">Mission Admin</a><br>';
	}

	// echo '<pre>';
	// $slots = get_field('slots', $post_id);
	// print_r ($slots);
	// echo '</pre>';
	
	// echo '<pre>';
	// $fields = get_field('slots', $post_id);
	// $i = 0; $j = 1; $k = 1;
	// $slotIndexArray = array('slots',$i+1,'unit',$j+1,'slot',$k+1,'slot_member');
	// //update_sub_field ($slotIndexArray, 'admin2');
	// print_r ($fields[$i]['unit'][$j]['slot'][$k]);
	// echo '</pre>';
}
