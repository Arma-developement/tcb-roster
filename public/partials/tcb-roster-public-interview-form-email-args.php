<?php

function tcb_roster_public_interview_form_email_args($args, $form, $action){

	$queryArgs = array(
		'role__in' => array ('recruit_admin', 'training_admin')
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