<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to edit the mission briefing.
 */
function tcb_roster_public_mission_briefing_edit() {

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
