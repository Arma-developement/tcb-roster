<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to handle the mission news panel.
 */
function tcb_roster_public_mission_news() {

	$allowed_roles = array( 'mission_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id_ = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id_ ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_mission_news">';

	acfe_form(
		array(
			'name'            => 'submit_mission_news',
			'post_id'         => $post_id_,
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title( $post_id_ ) . ' via the Mission News Panel' );
	}

	echo '</div>';

	return ob_get_clean();
}
