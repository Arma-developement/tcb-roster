<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

add_shortcode( 'tcbp_public_mission_briefing', 'tcbp_public_mission_briefing' );

/**
 * Same as tcbp_public_briefing_l2()/tcbp_public_briefing_l3() below, for text sitting directly
 * under an h3 with no h4 of its own (e.g. "2 Mission", "5 Command and Signals").
 *
 * @param string $content Already-formatted HTML (e.g. from get_field()).
 */
function tcbp_public_briefing_l1( $content ) {
	if ( $content ) {
		echo '<div class="tcb_briefing_l1">' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Echoes $content wrapped in a div indented to match this page's "level 2" headings (h4, e.g.
 * "1.2 Environment"). CSS alone can't cleanly express "indent until the next heading" here - a
 * general-sibling selector (h4 ~ p) would also catch a *later* h4 section that has no h5 of its
 * own (e.g. "1.6 Attachments"), once any h5 has appeared anywhere earlier in the same box - so
 * each content block is wrapped explicitly at the point it's rendered instead. Does nothing if
 * $content is empty, so an empty/conditional field doesn't leave a stray empty div.
 *
 * @param string $content Already-formatted HTML (e.g. from get_field()).
 */
function tcbp_public_briefing_l2( $content ) {
	if ( $content ) {
		echo '<div class="tcb_briefing_l2">' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Same as tcbp_public_briefing_l2() above, for "level 3" headings (h5, e.g. "1.2.1 Time").
 *
 * @param string $content Already-formatted HTML (e.g. from get_field()).
 */
function tcbp_public_briefing_l3( $content ) {
	if ( $content ) {
		echo '<div class="tcb_briefing_l3">' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

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

	echo '<div class="tcb_mission_briefing_page"><div class="tcb_mission_briefing_inner">';

	echo '<div class="tcb_mission_briefing">';

	echo '<h3>1.Situation</h3>';

	echo '<h4>1.1 General</h4>';
	tcbp_public_briefing_l2( get_field( 'brief_situation', $post_id_ ) );


	echo '<h4>1.2 Environment</h4>';

	echo '<h5>1.2.1 Time</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_start_time', $post_id_ ) );

	echo '<h5>1.2.2 Met</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_weather', $post_id_ ) );

	echo '<h5>1.2.3 Terrain</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_map', $post_id_ ) . get_field( 'brief_terrain', $post_id_ ) );


	echo '<h4>1.3 Threats</h4>';

	echo '<h5>1.3.1 Enemy Forces</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_enemy_forces', $post_id_ ) );

	echo '<h5>1.3.2 IED/Mine Threat</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_iedmine_threat', $post_id_ ) );

	// CBRN Threat/Other forces/NGO and Media are optional - only shown when the mission maker
	// actually entered something, unlike the other sections on this page (which always show
	// their heading regardless of content).
	$brief_cbrn_threat = get_field( 'brief_cbrn_threat', $post_id_ );
	if ( $brief_cbrn_threat ) {
		echo '<h5>1.3.3 CBRN Threat</h5>';
		tcbp_public_briefing_l3( $brief_cbrn_threat );
	}


	echo '<h4>1.4 Other Factors</h4>';

	$brief_other_forces = get_field( 'brief_other_forces', $post_id_ );
	if ( $brief_other_forces ) {
		echo '<h5>1.4.1 Other forces</h5>';
		tcbp_public_briefing_l3( $brief_other_forces );
	}

	echo '<h5>1.4.2 Civilians</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_civilians', $post_id_ ) );

	$brief_ngo_media = get_field( 'brief_ngo_media', $post_id_ );
	if ( $brief_ngo_media ) {
		echo '<h5>1.4.3 NGO and Media</h5>';
		tcbp_public_briefing_l3( $brief_ngo_media );
	}


	echo '<h4>1.5 Friendly Forces</h4>';

	echo '<h5>1.5.1 Force Composition</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_friendly_forces', $post_id_ ) );

	echo '<h5>1.5.2 Higher Commander\'s Intent</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_execution', $post_id_ ) );

	$brief_friendly_forces_conops = get_field( 'brief_friendly_forces_conops', $post_id_ );
	if ( $brief_friendly_forces_conops ) {
		echo '<h5>1.5.3 CONOPS</h5>';
		tcbp_public_briefing_l3( $brief_friendly_forces_conops );
	}

	echo '<h4>1.6 Attachments</h4>';
	tcbp_public_briefing_l2( get_field( 'brief_support', $post_id_ ) );

	echo '</div>';

	////////////////////////////

	echo '<div class="tcb_mission_briefing">';

	echo '<h3>2 Mission</h3>';
	tcbp_public_briefing_l1( get_field( 'brief_mission', $post_id_ ) );

	// Early out for subscribers on private missions.
	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id_ );
	$brief_mission_type       = $brief_mission_type_array['value'];
	if ( in_array( 'subscriber', $user->roles, true ) && in_array( $brief_mission_type, array( 'private', 'miniop', 'patrolop' ), true ) ) {
		echo '</div></div></div>';
		return ob_get_clean();
	}

	echo '</div>';

	////////////////////////////

	echo '<div class="tcb_mission_briefing">';

	echo '<h3>3 Execution</h3>';

	$brief_command_intent = get_field( 'brief_command_intent', $post_id_ );
	if ( $brief_command_intent ) {
		echo '<h4>3.1 Commander\'s Intent</h4>';
		tcbp_public_briefing_l2( $brief_command_intent );
	}

	echo '<h4>3.2 CONOPS</h4>';

	$brief_general_description = get_field( 'brief_general_description', $post_id_ );
	tcbp_public_briefing_l2( $brief_general_description );

	echo '<h5>3.2.1 Phasing</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_plan', $post_id_ ) );

	$brief_support_units = get_field( 'brief_support_units', $post_id_ );
	if ( $brief_support_units ) {
		echo '<h4>3.3 Tasks to Support Units</h4>';
		tcbp_public_briefing_l2( $brief_support_units );
	}

	echo '<h4>3.4 Coordinating Instructions</h4>';

	$brief_coordinating_instructions = get_field( 'brief_coordinating_instructions', $post_id_ );
	tcbp_public_briefing_l2( $brief_coordinating_instructions );

	echo '<h4>3.5 Actions On</h4>';
	tcbp_public_briefing_l2( get_field( 'brief_actions_on', $post_id_ ) . get_field( 'brief_actions_on_update', $post_id_ ) . '<p><a href="/information-centre/generic-actions-on/">SOP: Actions On</a></p>' );

	echo '<h4>3.6 Rules of Engagement</h4>';
	tcbp_public_briefing_l2( get_field( 'brief_rules_of_engagement', $post_id_ ) . get_field( 'brief_rules_of_engagement_update', $post_id_ ) . '<p><a href="/information-centre/rules-of-engagement/">SOP: ROE<br></a></p>' );

	echo '</div>';

	////////////////////////////

	echo '<div class="tcb_mission_briefing">';

	echo '<h3>4 Logistics</h3>';
	
	echo '<h4>4.1 Material Logistics</h4>';

	echo '<h5>4.1.1 Supplies</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_supplies', $post_id_ ) );

	echo '<h5>4.1.2 Vehicles</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_vehicles', $post_id_ ) );

	$brief_vehicles_r3p = get_field( 'brief_vehicles_r3p', $post_id_ );
	if ( $brief_vehicles_r3p ) {
		echo '<h5>4.1.3 Vehicles Service Support</h5>';
		tcbp_public_briefing_l3( $brief_vehicles_r3p );
	}

	$brief_vehicles_repair = get_field( 'brief_vehicles_repair', $post_id_ );
	if ( $brief_vehicles_repair ) {
		echo '<h5>4.1.4 Vehicles BDR and Salvaging</h5>';
		tcbp_public_briefing_l3( $brief_vehicles_repair );
	}

	echo '<h5>4.1.5 Section Composition</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_section_composition', $post_id_ ) );


	echo '<h4>4.2 Personnel Logistics</h4>';

	echo '<h5>4.2.1 Reinforcements</h5>';
	tcbp_public_briefing_l3( get_field( 'brief_reinforcements', $post_id_ ) );

	$brief_medical = get_field( 'brief_medical', $post_id_ );
	if ( $brief_medical ) {
		echo '<h5>4.2.2 Medical Support</h5>';
		tcbp_public_briefing_l3( $brief_medical );
	}

	echo '</div>';

	////////////////////////////

	echo '<div class="tcb_mission_briefing">';

	echo '<h3>5 Command and Signals</h3>';
	tcbp_public_briefing_l1( get_field( 'brief_command_and_signals', $post_id_ ) . '<p><a href="/information-centre/command-and-signals-acre/">SOP: C&S</a></p>' );

	$brief_comms = get_field( 'brief_comms', $post_id_ );
	if ( $brief_comms ) {
		echo '<h5>5.1 Communications</h5>';
		tcbp_public_briefing_l3( $brief_comms );
	}

	echo '</div>';

	////////////////////////////

	echo '<div class="tcb_mission_briefing">';

	if ( tcbp_public_slotting_find_user( $post_id_, $user_id ) ) {
		echo '<br><br><a href="/mission-briefing-edit/?id=' . esc_attr( $post_id_ ) . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	echo '<br><a href="javascript:history.back()" class="button button-secondary">Back</a>';

	echo '</div></div></div>';
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
		case 'training':
			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Staff' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainer' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainer' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Trainees' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 2' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 3' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 4' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 5' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 6' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 7' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Trainee 8' ), $post_id_ );

			$troop = add_row( 'slots', array(), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit' ), array( 'name' => 'Reserves' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Reserve 1' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Reserve 2' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Reserve 3' ), $post_id_ );
			add_sub_row( array( 'slots', $troop, 'unit', 1, 'slot' ), array( 'slot_name' => 'Reserve 4' ), $post_id_ );
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

	// Copy the mission maker's proposed date/times (Event Briefing group - a separate set of
	// fields from the event's own event_start_date/event_start_time/event_end_time, so a
	// proposal doesn't directly overwrite the real schedule until the admin's briefing review
	// actually completes) into the event's real Date & Time group. Written as Ymd/H:i:s - ACF's
	// own unambiguous internal storage formats - rather than the pre-formatted display strings
	// get_field() returns, which would otherwise force ACF to re-parse a formatted string and
	// risk exactly the kind of misparse already found and fixed for stamp_date elsewhere in this
	// plugin (see attendance.php).
	$proposed_start_date = get_field( 'proposed_event_start_date', $post_id_ );
	if ( $proposed_start_date ) {
		$date = DateTime::createFromFormat( 'Y-m-d', $proposed_start_date );
		if ( $date ) {
			update_field( 'event_start_date', $date->format( 'Ymd' ), $post_id_ );
		}
	}

	$proposed_start_time = get_field( 'proposed_event_start_time', $post_id_ );
	if ( $proposed_start_time ) {
		$start_time = DateTime::createFromFormat( 'g:i a', $proposed_start_time );
		if ( $start_time ) {
			update_field( 'event_start_time', $start_time->format( 'H:i:s' ), $post_id_ );
		}
	}

	$proposed_end_time = get_field( 'proposed_event_end_time', $post_id_ );
	if ( $proposed_end_time ) {
		$end_time = DateTime::createFromFormat( 'g:i a', $proposed_end_time );
		if ( $end_time ) {
			update_field( 'event_end_time', $end_time->format( 'H:i:s' ), $post_id_ );
		}
	}

	// Notify the Operations Coordinators that a new briefing needs reviewing. The edit link is
	// built directly rather than via get_edit_post_link(), since that checks the *current*
	// user's (the submitter's) capabilities and would return nothing for a member without
	// edit_post rights on this post type - the link is for admins, not the submitter.
	$mission_title = get_the_title( $post_id_ );
	$author_name   = wp_get_current_user()->display_name;
	$edit_link     = admin_url( 'post.php?post=' . $post_id_ . '&action=edit' );

	$discord_message = "{@Operations Coordinator}\nA new mission briefing \"" . $mission_title . '" has been submitted by ' . $author_name .
		"\n\nPlease check the briefing, complete, and publish " . $edit_link;

	tcb_roster_admin_post_to_discord_channel( 'operation_coordinators', $discord_message );
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

	// The fields previously listed here explicitly (brief_mission/brief_execution/brief_plan/
	// brief_actions_on/brief_rules_of_engagement/brief_command_and_signals, spanning several ACF
	// groups) are now all consolidated into a single group (group_638a3690e4548) - referencing
	// the whole group here instead of a hand-picked field-key list, so any field added to/removed
	// from it is automatically picked up (one form, one submit button - deliberately not two
	// separate acf_form() calls, which ACF requires if you need 'fields' and 'field_groups'
	// together, since it treats them as mutually exclusive rather than merging them). Deliberately
	// still acf_form(), not the "submit-plan" ACFE Form: that form's own Post action only offers
	// Current Post/Current Post Parent/Post Selector for its target post ID, none of which can
	// express "whatever post_id this shortcode was given" - Current Post resolves to the hosting
	// page (this shortcode's page, not the mission), not the dynamic $post_id_ from the URL.
	// acf_form()'s own 'post_id' parameter (below) is respected directly, which is what actually
	// made this work correctly before. brief_plan's field key (field_638a3691f4cde) is unchanged
	// by the group consolidation, so tcbp_public_mission_briefing_authorize_save() below still
	// correctly detects a submission of this form without needing any change.
	acf_form(
		array(
			'post_id'      => $post_id_,
			'field_groups' => array( 'group_638a3690e4548' ),
			'return'       => '/mission-briefing/?id=' . $post_id_,
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

add_filter( 'acf/fields/wysiwyg/toolbars', 'tcbp_public_brief_plan_wysiwyg_toolbars' );

/**
 * Registers a custom WYSIWYG toolbar preset for the brief_plan field (Event Plan group),
 * displayed as an h4 subsection within the mission briefing (see line ~102 above) - so only
 * smaller headings make sense within it. Neither of ACF's built-in presets fit as-is: "Full"
 * includes the whole Heading 1-6 range, "Basic" drops the Format dropdown (and headings)
 * entirely. This preset is an exact copy of ACF's own "Full" preset (same two rows, same
 * buttons - nothing removed), so no toolbar functionality is lost; the header restriction is
 * handled separately by tcbp_public_brief_plan_wysiwyg_block_formats() below, which limits the
 * Format dropdown's own contents rather than removing any buttons. Select "Plan (H5/H6 only)"
 * on the field's own "Toolbar" setting in the Event Plan field group to use it.
 *
 * @param array $toolbars Existing toolbar presets, keyed by name.
 * @return array
 */
function tcbp_public_brief_plan_wysiwyg_toolbars( $toolbars ) {
	$toolbars['Plan (H5/H6 only)'] = array(
		1 => array( 'formatselect', 'bold', 'italic', 'bullist', 'numlist', 'blockquote', 'alignleft', 'aligncenter', 'alignright', 'link', 'unlink', 'wp_more', 'spellchecker', 'fullscreen', 'wp_adv' ),
		2 => array( 'strikethrough', 'hr', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent', 'undo', 'redo', 'wp_help' ),
	);
	return $toolbars;
}

add_filter( 'tiny_mce_before_init', 'tcbp_public_brief_plan_wysiwyg_block_formats' );

/**
 * Restricts the Format dropdown to Paragraph/Heading 5/Heading 6 for editors using our
 * "Plan (H5/H6 only)" toolbar preset. ACF's own field settings only offer a toolbar preset
 * choice (which controls which buttons appear, not what's inside the Format dropdown) - this is
 * the actual mechanism that limits the dropdown's contents.
 *
 * Originally this matched on the editor ID containing "brief_plan", on the assumption ACF names
 * its editors after the field. In fact ACF names them acf-editor-{numeric id} (confirmed by
 * inspecting the rendered page - the brief_plan editor was id="acf-editor-89"), so the field
 * name never appears in the ID at all and that match never fired. Matching on the toolbar's own
 * button list instead - built from tcbp_public_brief_plan_wysiwyg_toolbars() above and unique to
 * this one preset - works regardless of the editor's numeric ID, and regardless of whether the
 * field is rendered in the native wp-admin post editor or the front-end ACFE submission form.
 *
 * @param array $init The TinyMCE init settings for this editor instance.
 * @return array
 */
function tcbp_public_brief_plan_wysiwyg_block_formats( $init ) {
	if ( isset( $init['toolbar1'] ) && false !== strpos( $init['toolbar1'], 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft' ) ) {
		$init['block_formats'] = 'Paragraph=p;Heading 5=h5;Heading 6=h6';
	}
	return $init;
}
