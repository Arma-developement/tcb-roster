<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: application.php
 * Description: Handles the code associated with the application form in the tcb plugin.
 */

add_action( 'acfe/form/submit/post/form=submit-application', 'tcbp_public_submit_application_action', 10, 1 );

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
