<?php

function tcb_roster_public_mission_briefing_submit ($postId, $type, $args, $form, $action) {

	// Retrieve roster type
	$rosterType = get_field('brief_roster_type', $postId);

	add_row('rsvp', array( 'label' => 'Attending' ), $postId);
	add_row('rsvp', array( 'label' => 'Maybe' ), $postId);
	add_row('rsvp', array( 'label' => 'Not Attending' ), $postId);

	switch ($rosterType) {
		case 'std':
			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Coy' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-0' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-2' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-3' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-4' ), $postId);
			
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop Commander' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop 2iC' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Whiskey 6-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Pilot' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Co-pilot' ), $postId);
			break;
		case 'full44':
			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Coy' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-0' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-2' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-3' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-4' ), $postId);
			
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop Commander' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop 2iC' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'AT' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'AT' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'AT' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'MG Asst' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Whiskey 6-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Pilot' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Co-pilot' ), $postId);
			break;
		case 'full53':
			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Coy' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-0' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-2' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-3' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-4' ), $postId);
			
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop Commander' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop 2iC' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);			
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'AT' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'AT Asst' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);			
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'AT' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'AT Asst' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);			
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'AT' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'AT Asst' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'LMG' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'MG Asst' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Whiskey 6-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Pilot' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Co-pilot' ), $postId);
			break;
		case 'full222':
			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Coy' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Zeus' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-0' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-2' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-3' ), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => '1-4' ), $postId);
			
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop Commander' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Troop 2iC' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 2, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 3, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 4, 'slot'), array( 'slot_name' => 'LMG' ), $postId);;

			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Section Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Medic' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Fire Team Leader' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Engineer' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'Marksman' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 5, 'slot'), array( 'slot_name' => 'LMG' ), $postId);

			$troop = add_row('slots', array(), $postId);
			add_sub_row( array('slots', $troop, 'unit'), array( 'name' => 'Whiskey 6-1' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Pilot' ), $postId);
			add_sub_row( array('slots', $troop, 'unit', 1, 'slot'), array( 'slot_name' => 'Co-pilot' ), $postId);
			break;
	}
}
