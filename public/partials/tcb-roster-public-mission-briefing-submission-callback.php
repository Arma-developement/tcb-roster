<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Callback function for mission briefing submission.
 *
 * @param int $post_id_ The ID of the post being processed.
 */
function tcb_roster_public_mission_briefing_submission_callback( $post_id_ ) {

	// Set default perms.
	add_post_meta( $post_id_, '_members_access_role', 'limited_member' );
	add_post_meta( $post_id_, '_members_access_role', 'member' );

	// Set roster type.
	$roster_type = get_field( 'brief_roster_type', $post_id_ );

	add_row( 'rsvp', array( 'label' => 'Attending' ), $post_id_ );
	add_row( 'rsvp', array( 'label' => 'Maybe' ), $post_id_ );
	add_row( 'rsvp', array( 'label' => 'Not Attending' ), $post_id_ );

	switch ( $roster_type ) {
		case 'std':
			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Coy' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-0' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-2' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-3' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-4' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop Commander' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop 2iC' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Whiskey 6-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Pilot' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Co-pilot' ), $post_id_ );
			break;
		case 'full44':
			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Coy' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-0' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-2' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-3' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-4' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop Commander' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop 2iC' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'AT' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'AT' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'AT' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'MG Asst' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Whiskey 6-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Pilot' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Co-pilot' ), $post_id_ );
			break;
		case 'full53':
			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Coy' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-0' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-2' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-3' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-4' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop Commander' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop 2iC' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'AT' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'AT Asst' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'AT' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'AT Asst' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'AT' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'AT Asst' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'MG Asst' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Whiskey 6-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Pilot' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Co-pilot' ), $post_id_ );
			break;
		case 'full222':
			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Coy' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Zeus' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-0' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-2' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-3' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => '1-4' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop Commander' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Troop 2iC' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 2, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 3, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 4, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Section Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Medic' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Fire Team Leader' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Engineer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'Marksman' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 5, 'slot' ), array( 'slot_name' => 'LMG' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Whiskey 6-1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Pilot' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Co-pilot' ), $post_id_ );
			break;
	}
}
