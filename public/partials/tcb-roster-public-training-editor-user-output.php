<?php

function tcb_roster_public_training_editor_user_output($data, $user_id, $type, $args, $form, $action){
	print_r ($user_id);

	//$user_id = $_SESSION['foreign_user_id'];

	//$data['ID'] = $_SESSION['foreign_user_id'];

	//$data = get_user_by( 'id', $user_id );

	return $data;
}
