<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: transfer.php
 * Description: Handles the code associated with the transfer form in the tcb plugin.
 */

add_filter( 'acfe/form/submit/email_args/action=transfer_form_email', 'tcbp_public_transfer_form_email', 10, 1 );

/**
 * Handles the email after transfer form submission.
 *
 * @param int $args Arguments for the transfer partial.
 */
function tcbp_public_transfer_form_email( $args ) {

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

	// Send a message to the recruitment managers.
	// tcb_roster_admin_post_to_discord( 'Recruit Bot', 'recruitment-managers', '@here' . $args['subject'] );
	// .
	return $args;
}

/**
 * Function to copy elements of the transfer to the user profile.
 *
 * @param int $user_id The ID of the applicant.
 * @param int $transfer_id The ID of the transfer to be processed.
 */
function tcbp_public_transfer_to_profile( $user_id, $transfer_id ) {

	$profile_id = 'user_' . $user_id;

	$transfer_post = get_post( $transfer_id );
	if ( ! $transfer_post ) {
		return;
	}

	$discord_id = get_field( 'app_discord_id', $transfer_post );
	if ( '' !== $discord_id ) {
		update_field( 'discord_id', $discord_id, $profile_id );
	}

	$email = get_field( 'app_email', $transfer_post );
	if ( '' !== $email ) {
		update_user_meta( $user_id, 'user_email', $email );
	}

	$first_name = get_field( 'app_first_name', $transfer_post );
	if ( '' !== $first_name ) {
		update_user_meta( $user_id, 'first_name', $first_name );
	}

	$country = get_field( 'app_country', $transfer_post );
	if ( '' !== $country ) {
		update_field( 'user-location', $country, $profile_id );
	}
}
