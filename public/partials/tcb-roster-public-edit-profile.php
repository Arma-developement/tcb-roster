<?php

function tcb_roster_public_edit_profile($attributes) {
	
	$user = wp_get_current_user();

	// Early out for no user
	if (!$user->exists())
		return;

	// Early out for logged out users
	if(!is_user_logged_in()){
		return;
	}

	$userId = $user->ID;
	$profileId = 'user_'.$userId;

	ob_start();

	//var_dump( get_field('communication_preference', 'user_'.$userId) );
	//var_dump( get_field('thechamp_dontupdate_avatar', 'user_'.$userId) );

	echo '<div class="tcb_edit_user_profile">';

	acfe_form( array( 
		'post_id' => $profileId,
		'name' => 'edit-user-profile',

		'map' => array (
			'field_679946c5aecd3' => array ( 'value' => get_the_author_meta( 'display_name', $userId ) ),
			'field_67993c0abf12c' => array ( 'value' => get_the_author_meta( 'first_name', $userId ) ),
			'field_67993c3bbf12d' => array ( 'value' => get_the_author_meta( 'last_name', $userId ) ),
			'field_67993c5cbf12f' => array ( 'value' => get_the_author_meta( 'user_email', $userId ) ),
			'field_67993c68bf130' => array ( 'value' => get_field( 'discord_id', $profileId ) ),
			'field_67993c75bf131' => array ( 'value' => get_field( 'communication_preference', $profileId ) ),
			'field_67993c4cbf12e' => array ( 'value' => get_field( 'user-location', $profileId ) ),
			'field_67993cabbf132' => array ( 'value' => get_field( 'thechamp_avatar', $profileId ) ),
			'field_67993cbfbf133' => array ( 'value' => get_field( 'thechamp_large_avatar', $profileId ) ),
			'field_67993ccfbf134' => array ( 'value' => get_field( 'thechamp_dontupdate_avatar', $profileId ) ),
		),

		'submit_value' => 'Update Profile',
		//'return' => add_query_arg( 'updated', 'true', get_permalink() ),
		'return' => wp_get_referer(),
		'updated_message' => false
	) );
	
	echo '</div>';

	return ob_get_clean();
}

function tcb_roster_public_edit_profile_submit($form) {

	$user = wp_get_current_user();

	// Early out for no user
	if (!$user->exists())
		return;

	// Early out for logged out users
	if(!is_user_logged_in()){
		return;
	}

	$userId = $user->ID;
	$profileId = 'user_'.$userId;

	$post_id = $form['post_id'];

	//var_dump( get_field('first_name'));
	//die();

	update_user_meta( $userId, 'first_name', get_field('first_name') );
	update_user_meta( $userId, 'last_name', get_field('last_name') );
	update_user_meta( $userId, 'display_name', get_field('display_name') );
	update_user_meta( $userId, 'user_email', get_field('user_email') );
	update_field( 'discord_id', get_field('discord_id'), $profileId );
	update_field( 'communication_preference', get_field('communication_preference'), $profileId );
	update_field( 'user-location', get_field('user-location'), $profileId );
	update_field( 'thechamp_avatar', get_field('small_avatar_url'), $profileId );
	update_field( 'thechamp_large_avatar', get_field('large_avatar_url'), $profileId );
	update_field( 'thechamp_dontupdate_avatar', get_field('steam_avatar_update'), $profileId );

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited own user profile' );
	}
}