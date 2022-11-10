<?php

function tcb_roster_public_interview_form_email_args($args, $form, $action){

	$query = array(
		'numberposts'	=> -1,
		'post_type'		=> 'service-record',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' =>  'duties',
				'value'  => 'rm',
				'compare' =>  'LIKE'
			),
			array(
				'key' =>  'duties',
				'value'  => 'rti',
				'compare' =>  'LIKE'
			)
		)
	);

	$listOfPosts = get_posts( $query );
	if ($listOfPosts) {
		foreach ( $listOfPosts as $post ) {
			setup_postdata( $post );

			$userId = get_field( 'user_id', $post );
			$user = get_user_by( 'id', $userId );
			$emails[] = $user->user_email;
		}
		$args['to'] = implode (", ", $emails );
	}

	wp_reset_postdata();

	return $args;
}