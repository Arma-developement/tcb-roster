<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to handle the mission admin.
 */
function tcb_roster_public_mission_admin() {

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

	echo '<div class="tcb_mission_admin">';

	acfe_form(
		array(
			'name'    => 'send-announcement',
			'post_id' => $post_id_,
		)
	);

	acfe_form(
		array(
			'name'    => 'send-password',
			'post_id' => $post_id_,
		)
	);

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . get_the_title( $post_id_ ) . ' via the Mission Admin Panel' );
	}

	echo '</div>';

	return ob_get_clean();
}
