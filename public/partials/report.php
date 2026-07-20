<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

add_filter( 'acfe/form/submit/email_args/action=report_form_email', 'tcbp_public_report_form_email_args', 10, 1 );

/**
 * Generates the email arguments for the public report form.
 *
 * @param array $args The arguments for the email.
 */
function tcbp_public_report_form_email_args( $args ) {

	$query = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
		'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'tcb-rank',
				'field'    => 'name',
				'terms'    => 'Officer',
			),
		),
	);

	$emails        = array();
	$list_of_posts = get_posts( $query );
	if ( $list_of_posts ) {
		foreach ( $list_of_posts as $post ) {
			setup_postdata( $post );

			$user_id = get_field( 'user_id', $post );
			$user    = get_user_by( 'id', $user_id );
			if ( $user ) {
				$emails[] = $user->user_email;
			}
		}
	}

	$args['to'] = $emails ? implode( ', ', $emails ) : get_option( 'admin_email' );

	wp_reset_postdata();

	return $args;
}
