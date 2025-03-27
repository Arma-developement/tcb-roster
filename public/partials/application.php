<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: application.php
 * Description: Handles the code associated with the application form in the tcb plugin.
 */

add_action( 'acfe/form/submit/post/form=submit-application', 'tcbp_public_submit_application_action', 20, 1 );

/**
 * Handles the submission callback for the public application form.
 *
 * @param int $post_id_ The ID of the post being processed.
 */
function tcbp_public_submit_application_action( $post_id_ ) {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id    = $user->ID;
	$profile_id = 'user_' . $user_id;

	update_field( 'application', $post_id_, $profile_id );

	// Replace email name with app email if the user's profile does not show evidence that the email has been previously edited.
	if ( ! get_field( 'fe_email', $profile_id ) ) {
		$email     = esc_attr( get_field( 'app_email', $post_id_ ) );
		$user_data = wp_update_user(
			array(
				'ID'         => $user_id,
				'user_email' => $email,
			)
		);
		if ( is_wp_error( $user_data ) ) {
			error_log( 'Failure: Email not updated to: ' . $email . ' for userID ' . $user_id );
		}
	}

	// Replace first name with app first name if the user's profile does not show evidence that the first name has been previously edited.
	if ( ! get_field( 'fe_first_name', $profile_id ) ) {
		update_user_meta( $user_id, 'first_name', get_field( 'app_first_name', $post_id_ ) );
	}

	// Replace location with app location if the user's profile does not show evidence that the location has been previously edited.
	if ( ! get_field( 'fe_user_location', $profile_id ) ) {
		update_field( 'user-location', get_field( 'app_country', $post_id_ ), $profile_id );
	}

	wp_set_post_terms( $post_id_, 'Submission phase', 'tcb-selection' );
}

add_filter( 'acfe/form/submit/email_args/action=application_form_email', 'tcbp_public_application_form_email', 10, 1 );

/**
 * Handles the email after application form submission.
 *
 * @param int $args Arguments for the application partial.
 */
function tcbp_public_application_form_email( $args ) {

	$query = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
		'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'duties',
				'value'   => 'rm',
				'compare' => 'LIKE',
			),
		),
	);

	$list_of_posts = get_posts( $query );
	if ( $list_of_posts ) {
		foreach ( $list_of_posts as $post ) {
			setup_postdata( $post );

			$user_id  = get_field( 'user_id', $post );
			$user     = get_user_by( 'id', $user_id );
			$emails[] = $user->user_email;
		}
		$args['to'] = implode( ', ', $emails );
	}

	wp_reset_postdata();

	tcb_roster_admin_post_to_discord( 'Recruit Bot', 'recruitment-managers', '@here' . $args['subject'] );

	// Find and replace the tag with a link to the application.
	// $contents = explode ('XXXXX', $args['content'] );
	// if (count($contents) == 3) {
	// $args['content'] = $contents[0] . '<a href="'. home_url() .'/application/' . $contents[1] . '">Authorize application</a>' . $contents[2];
	// }.

	return $args;
}

add_shortcode( 'tcbp_public_edit_app_interview', 'tcbp_public_edit_app_interview' );

/**
 * Called buy a shortcode to edit the status portion of the ribbons form.
 */
function tcbp_public_edit_app_interview() {

	$allowed_roles = array( 'recruit_admin', 'snco', 'officer', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$user_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $user_id ) ) {
		return;
	}

	$user = get_user_by( 'id', $user_id );

	// Early out for no user.
	if ( ! $user ) {
		return;
	}

	$display_name = $user->get( 'display_name' );
	$profile_id   = 'user_' . $user_id;
	$post_id      = get_field( 'application', $profile_id );

	if ( ! $post_id ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_edit_interview">';
	echo '<h2>' . esc_html( $display_name ) . '</h2>';

	acf_form(
		array(
			'post_id'         => $post_id,
			'field_groups'    => array( 'group_67c439b282575' ),
			'return'          => wp_get_referer(),
			'submit_value'    => 'Updated ' . $display_name . "'s Interview",
			'updated_message' => false,
		)
	);

	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited ' . $display_name . "'s Commendations" );
	}

	return ob_get_clean();
}
