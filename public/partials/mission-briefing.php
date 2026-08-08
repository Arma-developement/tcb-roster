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

	echo '<h3>1.Situation</h3>';

	echo '<h4>1.1 General</h4>';
	echo get_field( 'brief_situation', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	
	echo '<h4>1.2 Environment</h4>';

	echo '<h5>Map</h5>';
	echo get_field( 'brief_map', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Terrain</h5>';
	echo get_field( 'brief_terrain', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Time</h5>';
	echo get_field( 'brief_start_time', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Weather</h5>';
	echo get_field( 'brief_weather', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


	echo '<h4>1.3 Threats / Other factors</h4>';

	echo '<h5>Enemy Forces</h5>';
	echo get_field( 'brief_enemy_forces', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>IED/Mine Threat</h5>';
	echo get_field( 'brief_iedmine_threat', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Civilians</h5>';

	echo get_field( 'brief_civilians', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>1.4 Friendly Forces</h4>';
	echo get_field( 'brief_friendly_forces', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Support Assets</h5>';
	echo get_field( 'brief_support', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>2.Mission</h3>';
	echo get_field( 'brief_mission', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// Early out for subscribers on private missions.
	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id_ );
	$brief_mission_type       = $brief_mission_type_array['value'];
	if ( in_array( 'subscriber', $user->roles, true ) && in_array( $brief_mission_type, array( 'private', 'miniop', 'patrolop' ), true ) ) {
		echo '</div>';
		return ob_get_clean();
	}

	echo '<h3>3 Execution</h3>';
	echo get_field( 'brief_execution', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>3.1 Troop Order</h4>';
	echo get_field( 'brief_plan', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>3.2 Coordinatoring Actions</h4>';

	echo '<h5>Actions On</h3>';
	echo get_field( 'brief_actions_on', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><a href="/information-centre/generic-actions-on/">SOP: Actions On</a></p>';

	echo '<h5>Rules of Engagement</h3>';
	echo get_field( 'brief_rules_of_engagement', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><a href="/information-centre/rules-of-engagement/">SOP: ROE<br></a></p>';


	echo '<h3>4 Logistics</h3>';
	
	echo '<h4>4.1 Material Logistics</h4>';

	echo '<h5>Vehicles</h5>';
	echo get_field( 'brief_vehicles', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Supplies</h5>';
	echo get_field( 'brief_supplies', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h4>4.2 Personal Logistics</h4>';

	echo '<h5>Reinforcements</h5>';
	echo get_field( 'brief_reinforcements', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h5>Section Composition</h5>';
	echo get_field( 'brief_section_composition', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	

	echo '<h3>5 Command and Signals</h3>';
	echo get_field( 'brief_command_and_signals', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><a href="/information-centre/command-and-signals-tfar/">SOP: C&S<br></a></p>';

	if ( tcbp_public_slotting_find_user( $post_id_, $user_id ) ) {
		echo '<br><br><a href="/mission-briefing-edit/?id=' . esc_attr( $post_id_ ) . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	echo '<br><a href="javascript:history.back()" class="button button-secondary">Back</a>';

	echo '</div>';
	return ob_get_clean();
}

add_shortcode( 'tcbp_public_mission_briefing_submit', 'tcbp_public_mission_briefing_submit' );

/**
 * Renders the "submit a new mission briefing" form, with an optional dropdown letting the user
 * pick an existing mission to copy its Event Briefing (group_638ca355bf287) field values from,
 * so a new briefing doesn't always have to start blank. Copying is a one-off prefill, not a
 * live link - the new mission is still a completely separate post once submitted.
 */
function tcbp_public_mission_briefing_submit() {

	ob_start();

	echo '<div class="tcb_mission_briefing_submit">';

	// Skip the "copy from" dropdown entirely on the post-submission success page - it's only
	// relevant when the user is about to fill in a blank/prefilled form, not after they've
	// already submitted one.
	$map = array();
	if ( ! acfe_is_form_success( 'submit-briefing' ) ) {
		$copy_from = isset( $_GET['copy_from'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['copy_from'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$past_missions = get_posts(
			array(
				'post_type'      => 'tribe_events',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			)
		);

		echo '<form method="get" class="tcb_copy_briefing_form">';
		echo '<label for="copy_from">Copy briefing details from an existing mission:</label> ';
		echo '<select name="copy_from" id="copy_from" onchange="this.form.submit()">';
		echo '<option value="">-- Start blank --</option>';
		foreach ( $past_missions as $mission ) {
			echo '<option value="' . esc_attr( $mission->ID ) . '" ' . selected( $copy_from, $mission->ID, false ) . '>' . esc_html( $mission->post_title ) . '</option>';
		}
		echo '</select>';
		echo '<noscript><input type="submit" value="Load"></noscript>';
		echo '</form>';

		// Build the prefill map dynamically from the field group's own field list, rather than
		// hardcoding each of its 20+ field keys individually - any field added to the group later
		// is automatically picked up too.
		if ( $copy_from && get_post( $copy_from ) ) {
			$fields = acf_get_fields( 'group_638ca355bf287' );
			if ( $fields ) {
				foreach ( $fields as $field ) {
					// Textarea fields with a "New Lines" setting (e.g. Automatically add paragraphs)
					// wrap the raw stored text in HTML at get_field() read time - fine when copying
					// into a WYSIWYG field, which renders it, but a plain Text/Textarea destination
					// field just shows those tags as literal visible characters. Read the raw,
					// unformatted value for those two field types specifically; everything else
					// (WYSIWYG included) keeps the normal formatted read.
					$format_value          = ! in_array( $field['type'], array( 'text', 'textarea' ), true );
					$map[ $field['key'] ] = array( 'value' => get_field( $field['name'], $copy_from, $format_value ) );
				}
			}
		}
	}

	acfe_form(
		array(
			'name' => 'submit-briefing',
			'map'  => $map,
		)
	);

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

	// Set the event's category from the briefing's mission type, so the correct overview layout
	// (tcbp_public_mission_overview()) and badge colour are picked up automatically.
	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id_ );
	$brief_mission_type       = $brief_mission_type_array ? $brief_mission_type_array['value'] : '';
	switch ( $brief_mission_type ) {
		case 'publicaction':
		case 'privateaction':
			$event_category = 'actions';
			break;
		case 'training':
			$event_category = 'training';
			break;
		default:
			$event_category = 'operations';
			break;
	}
	wp_set_object_terms( $post_id_, $event_category, 'event_category' );

	// Copy the briefing's announcement text into the event's own Announcement message field
	// (read by tcbp_public_mission_send_announcement()), and set the briefing's chosen image as
	// the event's featured image, so the mission admin doesn't have to duplicate either manually
	// when preparing the announcement.
	$announcement_text = get_field( 'announcement_text', $post_id_ );
	if ( $announcement_text ) {
		update_field( 'message', $announcement_text, $post_id_ );
	}

	$featured_image = get_field( 'featured_image', $post_id_ );
	if ( $featured_image && ! empty( $featured_image['ID'] ) ) {
		set_post_thumbnail( $post_id_, $featured_image['ID'] );
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

	// Only a user slotted for this mission may edit its briefing - this must match the same
	// check that gates the "Edit Mission Briefing" link in tcbp_public_mission_briefing(),
	// since that link is only a UI convenience, not an access control on its own.
	if ( ! tcbp_public_slotting_find_user( $post_id_, $user->ID ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_mission_briefing_edit">';

	acf_form(
		array(
			'name'    => 'submit-plan',
			'post_id' => $post_id_,
			// Field keys, not names: acf_form() otherwise resolves a name to its key via the field
			// group's Location rules, falling back to the post's own per-field reference metadata
			// if that fails - a fallback that doesn't exist for a field never yet saved on this
			// post. Keys resolve directly and don't depend on either.
			'fields'  => array(
				'field_638ca355cf0fa', // brief_mission.
				'field_638ca355d2ccc', // brief_execution.
				'field_638a3691f4cde', // brief_plan.
				'field_639b76831fd8f', // brief_actions_on.
				'field_638ca35613222', // brief_rules_of_engagement.
				'field_638ca35616d9d', // brief_command_and_signals.
			),
			'return'  => '/mission-briefing/?id=' . $post_id_,
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title( $post_id_ ) . ' via the Mission Admin Panel' );
	}

	echo '</div>';

	return ob_get_clean();
}

add_action( 'acf/save_post', 'tcbp_public_mission_briefing_authorize_save', 5 );

/**
 * Independently re-checks briefing-edit authorization at the moment ACF is actually about to
 * save, rather than trusting the page-render-time check in tcbp_public_mission_briefing_edit()
 * alone. acf_form_head() is never called in this plugin, so there's no guarantee the save is
 * processed synchronously within that function's own request - a form rendered while a user
 * was slotted into a mission could otherwise still be captured and submitted later, after they
 * lose access, and get saved anyway. Registered at priority 5 (default ACF save is 10) so this
 * runs, and can wp_die() to hard-stop the request, before ACF writes anything.
 *
 * @param int $post_id The post ACF is about to save.
 */
function tcbp_public_mission_briefing_authorize_save( $post_id ) {

	// Only relevant to a front-end submission of this specific form - identified by one of its
	// own field keys being present in the submitted data. wp-admin saves (by staff with normal
	// edit_post capabilities) are unaffected.
	if ( is_admin() || empty( $_POST['acf']['field_638a3691f4cde'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$user_id = get_current_user_id();
	if ( ! $user_id || ! tcbp_public_slotting_find_user( $post_id, $user_id ) ) {
		wp_die( esc_html__( 'You are no longer authorized to edit this mission briefing.', 'roster' ) );
	}
}
