<?php

function tcb_roster_public_application_form_email_args($args, $form, $action){

	$queryArgs = array(
		'role'    => 'recruit_admin'
	);
	$listOfUsers = get_users( $queryArgs );

	if ($listOfUsers) {
		foreach ($listOfUsers as $user) {
			$emails[] = $user->user_email;
		}
		$args['to'] = implode (", ", $emails );
	}

	// Find and replace the tag with a link to the application
	// $contents = explode ('XXXXX', $args['content'] );
	// if (count($contents) == 3) {
	// 	$args['content'] = $contents[0] . '<a href="'. home_url() .'/application/' . $contents[1] . '">Authorize application</a>' . $contents[2];
	// }

	return $args;
}