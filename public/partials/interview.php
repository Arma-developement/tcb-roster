<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: interview.php
 * Description: Handles the code associated with the interview form in the tcb plugin.
 */

add_action( 'acfe/form/submit/post/form=submit-interview', 'tcbp_public_submit_interview_action', 10, 1 );

/**
 * Handles the submission callback for the public interview form in the TCB Roster plugin.
 *
 * @param int $post_id_ The ID of the post being processed.
 */
function tcbp_public_submit_interview_action( $post_id_ ) {

	$user = get_field( 'applicant' );

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	$user_id    = $user->ID;
	$profile_id = 'user_' . $user_id;

	update_field( 'interview', $post_id_, $profile_id );
}

add_shortcode( 'tcbp_public_interview_form', 'tcbp_public_interview_form' );

/**
 * Shortcode to open the interview form and prefill with applicant ID.
 */
function tcbp_public_interview_form() {

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$applicant_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $applicant_id ) ) {
		return;
	}

	$applicant = get_user_by( 'id', $applicant_id );

	// Early out for no applicant.
	if ( ! $applicant->exists() ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_interview_form">';

	acfe_form(
		array(
			'post_id' => 'new_post',
			'name'    => 'submit-interview',
			'map'     => array(
				'field_636c1ff350232' => array( 'value' => $applicant->get( 'ID' ) ),
			),
		)
	);

	echo '</div>';

	return ob_get_clean();
}

add_filter( 'acfe/form/submit/email_args/action=interview_form_email', 'tcbp_public_interview_form_email', 10, 1 );

/**
 * Handles the email after application form submission.
 *
 * @param int $args Arguments for the interview partial.
 */
function tcbp_public_interview_form_email( $args ) {

	$query = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
		'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			array(
				'key'     => 'duties',
				'value'   => 'rm',
				'compare' => 'LIKE',
			),
			array(
				'key'     => 'duties',
				'value'   => 'rti',
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

	return $args;
}
