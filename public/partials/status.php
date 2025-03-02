<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: status.php
 * Description: Handles the code associated with the setting the status opf a post in the tcb plugin.
 */

add_shortcode( 'tcbp_public_edit_status', 'tcbp_public_edit_status' );

/**
 * Called buy a shortcode to display the status change form.
 */
function tcbp_public_edit_status() {

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id ) ) {
		return;
	}

	// Security check.
	if ( ( '' !== $post_id ) && ( ! current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_status">';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_678bea513af25' ),
			'submit_value'    => 'Update Status',
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited the status of postID=' . $post_id );
	}

	return ob_get_clean();
}


add_shortcode( 'tcbp_public_edit_selection', 'tcbp_public_edit_selection' );

/**
 * Called buy a shortcode to display the status change form.
 */
function tcbp_public_edit_selection() {

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$post_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $post_id ) ) {
		return;
	}

	// Security check.
	if ( ( '' !== $post_id ) && ( ! current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_selection">';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_67c43a8f83925' ),
			'submit_value'    => 'Update Status',
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited the selection status of postID=' . $post_id );
	}

	return ob_get_clean();
}
