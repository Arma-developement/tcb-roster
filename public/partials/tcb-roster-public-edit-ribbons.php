<?php

function tcb_roster_public_edit_ribbons($attributes) {

	if (! in_array( 'commendation_admin', wp_get_current_user()->roles))
		return '';

	$user_id = $_GET['id'];

	if ($user_id != "") {
		$user = get_user_by( 'id', $user_id );
	} else {
		$user = wp_get_current_user();
	}

//		'field_groups' => array( 'key' => 'group_6356980addb3c' ),


	$myoptions = array( 
		'post_id' => 'user_' . $user->ID,
		'field_groups' => array( 'key' => 'group_6356980addb3c' ),
		'return' => add_query_arg( 'id', $user->ID, '//localhost/wordpress/user-info' ),
		'submit_value' => 'Update ' . $user->get( 'display_name' ) . "'s Commendations",
		'updated_message' => __("Commendations Updated", 'acf'),
	 );
	
	acf_form( $myoptions );	

	return '';
}
