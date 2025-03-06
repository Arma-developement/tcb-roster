<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to handle the mission briefing.
 */
function tcb_roster_public_mission_briefing() {

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
	echo '<h2>Mission Details</h2>';

	echo '<h3>Situation</h3>';
	echo esc_html( get_field( 'brief_situation', $post_id_ ) );

	echo '<h3>Mission</h3>';
	echo esc_html( get_field( 'brief_mission', $post_id_ ) );

	// Early out for subscribers on private missions.
	if ( in_array( 'subscriber', $user->roles, true ) && ( get_field( 'brief_mission_type', $post_id_ ) === 'private' ) ) {
		echo '</div>';
		return ob_get_clean();
	}

	echo '<h3>Execution</h3>';
	echo esc_html( get_field( 'brief_execution', $post_id_ ) );

	echo '<h3>Environment</h3>';

	echo '<h4>Map</h4>';
	echo esc_html( get_field( 'brief_map', $post_id_ ) );

	echo '<h4>Terrain</h4>';
	echo esc_html( get_field( 'brief_terrain', $post_id_ ) );

	echo '<h4>Time</h4>';
	echo esc_html( get_field( 'brief_start_time', $post_id_ ) );

	echo '<h4>Weather</h4>';
	echo esc_html( get_field( 'brief_weather', $post_id_ ) );

	echo '<h4>IED/Mine Threat</h4>';
	echo esc_html( get_field( 'brief_iedmine_threat', $post_id_ ) );

	echo '<h3>Enemy Forces</h3>';
	echo esc_html( get_field( 'brief_enemy_forces', $post_id_ ) );

	echo '<h3>Friendly Forces</h3>';
	echo esc_html( get_field( 'brief_friendly_forces', $post_id_ ) );

	echo '<h3>Civilians</h3>';
	echo esc_html( get_field( 'brief_civilians', $post_id_ ) );

	echo '<h3>Plan</h3>';
	echo esc_html( get_field( 'brief_plan', $post_id_ ) );

	echo '<h3>Service Support</h3>';

	echo '<h4>Vehicles</h4>';
	echo esc_html( get_field( 'brief_vehicles', $post_id_ ) );

	echo '<h4>Supplies</h4>';
	echo esc_html( get_field( 'brief_supplies', $post_id_ ) );

	echo '<h4>Support</h4>';
	echo esc_html( get_field( 'brief_support', $post_id_ ) );

	echo '<h4>Reinforcements</h4>';
	echo esc_html( get_field( 'brief_reinforcements', $post_id_ ) );

	echo '<h3>Actions On</h3>';
	echo esc_html( get_field( 'brief_actions_on', $post_id_ ) );
	echo '<p><a href="/information-centre/generic-actions-on/">SOP: Actions On</a></p>';

	echo '<h3>Rules of Engagement</h3>';
	echo esc_html( get_field( 'brief_rules_of_engagement', $post_id_ ) );
	echo '<p><a href="/information-centre/rules-of-engagement/">SOP: ROE<br></a></p>';

	echo '<h3>Command and Signals</h3>';
	echo esc_html( get_field( 'brief_command_and_signals', $post_id_ ) );
	echo '<p><a href="/information-centre/command-and-signals-tfar/">SOP: C&S<br></a></p>';

	if ( tcb_roster_public_find_user_in_slotting( $post_id_, $user_id ) ) {
		echo '<br><br><a href="/mission-briefing-edit/?id=' . esc_attr( $post_id_ ) . '" class="button button-secondary">Edit Mission Briefing</a><br>';
	}

	echo '</div>';

	return ob_get_clean();
}
