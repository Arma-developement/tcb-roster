<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

add_shortcode( 'tcbp_public_mission_briefing', 'tcbp_public_mission_briefing' );

/**
 * Function to handle the mission briefing.
 */
function tcbp_public_mission_briefing() {

	$user    = wp_get_current_user();
	$user_id = $user->ID;

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id_ = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id_ ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_mission_briefing">';
	echo '<h2>Mission Statement</h2>';

	echo '<h3>Situation</h3>';
	echo get_field( 'brief_situation', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Mission</h3>';
	echo get_field( 'brief_mission', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// Early out for subscribers on private missions.
	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id_ );
	$brief_mission_type       = $brief_mission_type_array['name'];
	if ( in_array( 'subscriber', $user->roles, true ) && in_array( $brief_mission_type, array( 'private', 'miniop', 'patrolop' ), true ) ) {
		echo '</div>';
		return ob_get_clean();
	}

	echo '<h3>Environment</h3>';

	echo '<h4>Map</h4>';
	echo get_field( 'brief_map', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>Terrain</h4>';
	echo get_field( 'brief_terrain', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>Time</h4>';
	echo get_field( 'brief_start_time', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>Weather</h4>';
	echo get_field( 'brief_weather', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>IED/Mine Threat</h4>';
	echo get_field( 'brief_iedmine_threat', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Enemy Forces</h3>';
	echo get_field( 'brief_enemy_forces', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Friendly Forces</h3>';
	echo get_field( 'brief_friendly_forces', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Civilians</h3>';
	echo get_field( 'brief_civilians', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h2>Scheme of Manoeuvre</h2>';

	echo '<h3>Command Intent</h3>';
	echo get_field( 'brief_execution', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Plan</h3>';
	echo get_field( 'brief_plan', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Section Composition</h3>';
	echo get_field( 'brief_section_composition', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Service Support</h3>';

	echo '<h4>Vehicles</h4>';
	echo get_field( 'brief_vehicles', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>Supplies</h4>';
	echo get_field( 'brief_supplies', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>Support</h4>';
	echo get_field( 'brief_support', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>Reinforcements</h4>';
	echo get_field( 'brief_reinforcements', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Actions On</h3>';
	echo get_field( 'brief_actions_on', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><a href="/information-centre/generic-actions-on/">SOP: Actions On</a></p>';

	echo '<h3>Rules of Engagement</h3>';
	echo get_field( 'brief_rules_of_engagement', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><a href="/information-centre/rules-of-engagement/">SOP: ROE<br></a></p>';

	echo '<h3>Command and Signals</h3>';
	echo get_field( 'brief_command_and_signals', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><a href="/information-centre/command-and-signals-tfar/">SOP: C&S<br></a></p>';

	if ( tcbp_public_slotting_find_user( $post_id_, $user_id ) ) {
		echo '<br><br><a href="/mission-briefing-edit/?id=' . esc_attr( $post_id_ ) . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	echo '<br><a href="javascript:history.back()" class="button button-secondary">Back</a>';

	echo '</div>';
	return ob_get_clean();
}

add_action( 'acfe/form/submit/post/form=submit-briefing', 'tcbp_public_mission_briefing_submission_callback', 10, 1 );

/**
 * Callback function for mission briefing submission.
 *
 * @param int $post_id_ The ID of the post being processed.
 */
function tcbp_public_mission_briefing_submission_callback( $post_id_ ) {

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


add_shortcode( 'tcbp_public_mission_briefing_edit', 'tcbp_public_mission_briefing_edit' );

/**
 * Function to edit the mission briefing.
 */
function tcbp_public_mission_briefing_edit() {

	$user = wp_get_current_user();

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id_ = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id_ ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_mission_briefing_edit">';

	acf_form(
		array(
			'name'    => 'submit-plan',
			'post_id' => $post_id_,
			'fields'  => array( 'brief_mission', 'brief_execution', 'brief_plan', 'brief_actions_on', 'brief_rules_of_engagement', 'brief_command_and_signals' ),
			'return'  => '/mission-briefing/?id=' . $post_id_,
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title( $post_id_ ) . ' via the Mission Admin Panel' );
	}

	echo '</div>';

	return ob_get_clean();
}
