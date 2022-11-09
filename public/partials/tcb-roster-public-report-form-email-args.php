<?php

function tcb_roster_public_report_form_email_args($args, $form, $action){

	$queryArgs = array(
		'role'    => 'report_admin'
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