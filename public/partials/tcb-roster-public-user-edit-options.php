<?php

function tcb_roster_public_user_edit_options($attributes) {

	$userId = $_GET['id'];	

	if ($userId == "") {
		$userId = wp_get_current_user()->ID;
	}

	$return = '';
	$roles = wp_get_current_user()->roles;

	if (in_array( 'training_admin', $roles)) {
		$return .= '<br><a href="'. home_url() .'/edit-training-record/?id=' . $userId . '" class="button button-secondary">Edit Training Record</a><br>';
	}

	if (in_array( 'commendation_admin', $roles)) {
		$return .= '<br><a href="'. home_url() .'/edit-ribbons/?id=' . $userId . '" class="button button-secondary">Edit Commendations</a><br>';
	}

	if (in_array( 'administrator', $roles)) {
		$return .= '<br><a href="'. home_url() .'/edit-service-record/?id=' . $userId . '" class="button button-secondary">Edit Service Record</a><br>';
	}

	return $return;
}
