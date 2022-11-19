<?php

function tcb_roster_public_slotting_tool($attributes) {

	// Ensure user is logged in
	$currentUser = wp_get_current_user();
	if (!$currentUser)
		return;
	$the_current_user_id = $currentUser->ID;

	// Early out if no post
	$post_id = get_queried_object_id();
	if (!$post_id)
		return;

	// Early out if no entrys in slots field
	if(! have_rows('slots') )
		return;		

	echo '<div id="slotTool">';
	echo '<h2>Priority placements</h2>';
	echo '<p>Button to left of slot name appears when logged in.<br>Avatar appears when member is slotted</p>';

	$slots = get_field_objects();

	// Loop through slot rows
	while( have_rows('slots') ) : the_row();

		// Continue to next slot if unit is empty
		if (! have_rows ('unit') )
			continue;
		
		// Loop through unit rows
		$unitRow = 0;
		while( have_rows('unit') ) : the_row();

			echo '<div class="unit">';
			echo '<h3>' . the_sub_field('name') . '</h3>';

			// Continue to next unit if slot is empty
			if ( !have_rows('slot') )
				continue;

			// Loop through rows.
			$slotRow = 0;
			while( have_rows('slot') ) : the_row(); 	

				// Get profile pic for slotted member 
				$profilePic = '';
				$slottedMemberName = get_sub_field('slot_member');
				$the_user2 = get_user_by('login', $slottedMemberName);
				
				if ($the_user2) {
					$the_user_id = $the_user2->ID;
					//echo 'user id:'.$the_user_id;
					$profilePic = get_avatar_url($the_user_id);
					//echo $profilePic;
				}
				
				// Get profile pic for avatar update when slotting
				$profilePicSlotter = get_avatar_url($the_current_user_id);
				//echo $profilePicSlotter;
				
				$slotMember = get_sub_field_object('slot_member');

				echo '<div class="slotToolSlot" id="slotToolSlot-' . $unitRow . '-' . $slotRow . '">';

				echo '<form class="slotForm" action="" >';
				echo '<input type="hidden" name="member-name" class="memberName" value="' . wp_get_current_user()->user_login . '">';
				echo '<input type="hidden" name="post-id" class="postID" value="' . $post_id . '">';
				echo '<input type="hidden" name="profile-pic" class="profilePic" value="' . $profilePicSlotter . '">';
				echo '<input type="hidden" name="acf-path" class="acfPath" value="' . $slots['slots']['key'] . ',0,' . 
					$slots['slots']['sub_fields']['0']['key']. ',' . $unitRow . ',' . 
					$slots['slots']['sub_fields']['0']['sub_fields']['1']['key'] . ',' . $slotRow .',' . $slotMember['key'] . '">';
				echo '<input class="slotIcon ';
				if ($slottedMemberName !=='' && $slottedMemberName !== $currentUser->user_login)
					echo 'disabled';
				echo '" type="submit" style="background-image:url(';
				if ($profilePic)
					echo $profilePic;
				echo ')"></form>';

				echo '<strong>' . the_sub_field('slot_name') . '</strong> - <span class="slotMember">' . the_sub_field('slot_member') . '</span><br>';
				echo '</div>';	
				
				$slotRow++;
			endwhile;
			
			echo '</div><br>'; 
			$unitRow++;
		endwhile;
	endwhile;
	echo '</div>';

	/*echo '<div style="background:#eee;padding:20px;">';
	echo '<hr>';

	echo '<!--<h3>Test section - single repeater</h3>';
	echo '<div id="testFormWrap">';

	if (! have_rows('test_slots') ) 
		return;

	// Loop through rows.
	$i = 1;
	while( have_rows('test_slots') ) : the_row();
		$testslots = get_sub_field_object('slot_member');

		echo '<hr>';
		echo '<div id="testSlot' . $i . '">';
		echo '<form class="testForm" action="" style="display:inline;" >';
		echo '<input type="hidden" name="member-name" class="memberName" value="' . wp_get_current_user()->user_login . '">';
		echo '<input type="hidden" name="post-id" class="postID" value="' . $post_id . '">';
		echo '<input type="hidden" name="acf-path" class="acfPath" value="' . $testslots['parent_repeater'] . ',' . $i . ',' . $testslots['key']. '">';
		echo '<input class="slotIcon" type="submit">';
		echo '</form>';
		echo '<strong>' . the_sub_field('slot_name') . '</strong> - <span class="slotMember">' . the_sub_field('slot_member') . '</span><br>';
		echo '</div>';

		$i++;
	endwhile;
	
	echo '</div>';
	echo '<!--<p>This used AJAX to update a WordPress custom field - saved to database with no page reload</p>-->';
	echo '<h2 id="testh2">' . the_field('test') . '</h2>';
	echo '<!--<p>post id:' . $post_id. '</p>-->';
	echo '<form id="testForm3" class="testForm2" action="">
		New value: <input type="text" name="input-test" class="memberName" value="' . the_field('test') . '">';
	echo '<input type="hidden" name="input-id" class="postID" value="' . $post_id . '">';
	echo '<input type="submit" value="Update">';
	echo '</form>';
	echo '</div>';*/
}
