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

	// Matches the "Edit Status" button's own visibility on both single-report.php and
	// single-loa.php (role_edit => officer, administrator) - previously this list was broader
	// than what the UI ever exposed a button for, so training_admin/recruit_admin/mission_admin/
	// snco could reach this endpoint directly even though they'd never see a way to get here.
	$allowed_roles = array( 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	// The Status field is now a dedicated field group per post type - LOA_Status and
	// Report_Status - rather than the single shared "Status" group (group_678bea513af25) this
	// used before. Bail for any other post type, since there's no group configured for it.
	$status_field_groups = array(
		'loa'    => 'group_6a63834d79fbe', // LOA_Status.
		'report' => 'group_6a6383594c9f0', // Report_Status.
	);
	$post_type = get_post_type( $post_id );
	if ( ! isset( $status_field_groups[ $post_type ] ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_status">';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( $status_field_groups[ $post_type ] ),
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

	$allowed_roles = array( 'recruit_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
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
