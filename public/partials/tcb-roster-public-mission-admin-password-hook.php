<?php

function tcb_roster_public_application_form_email_args($args, $form, $action){

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

	tcb_roster_admin_post_to_discord ('Recruit Bot', 'recruitment-managers', '@here' . $args['subject'] );

	// Find and replace the tag with a link to the application
	// $contents = explode ('XXXXX', $args['content'] );
	// if (count($contents) == 3) {
	// 	$args['content'] = $contents[0] . '<a href="'. home_url() .'/application/' . $contents[1] . '">Authorize application</a>' . $contents[2];
	// }

	return $args;
}