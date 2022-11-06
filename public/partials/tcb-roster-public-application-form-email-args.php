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

	return $args;
}