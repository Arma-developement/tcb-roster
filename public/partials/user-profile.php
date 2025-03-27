<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: user-profile.php
 * Description: Handles the code associated with front end access to the user profile, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_edit_profile', 'tcbp_public_edit_profile' );

/**
 * Shortcode to allow editing of user profile.
 */
function tcbp_public_edit_profile() {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id    = $user->ID;
	$profile_id = 'user_' . $user_id;

	ob_start();

	echo '<div class="tcb_edit_user_profile">';

	acfe_form(
		array(
			'post_id'         => $profile_id,
			'name'            => 'edit-user-profile',

			'map'             => array(
				'field_679946c5aecd3' => array( 'value' => get_the_author_meta( 'nickname', $user_id ) ),
				'field_67993c0abf12c' => array( 'value' => get_the_author_meta( 'first_name', $user_id ) ),
				'field_67993c3bbf12d' => array( 'value' => get_the_author_meta( 'last_name', $user_id ) ),
				'field_67993c5cbf12f' => array( 'value' => get_the_author_meta( 'user_email', $user_id ) ),
				'field_67993c68bf130' => array( 'value' => get_field( 'discord_username', $profile_id ) ),
				'field_67993c75bf131' => array( 'value' => get_field( 'communication_preference', $profile_id ) ),
				'field_67993c4cbf12e' => array( 'value' => get_field( 'user-location', $profile_id ) ),
				'field_67993cabbf132' => array( 'value' => get_field( 'thechamp_avatar', $profile_id ) ),
				'field_67993cbfbf133' => array( 'value' => get_field( 'thechamp_large_avatar', $profile_id ) ),
				'field_67993ccfbf134' => array( 'value' => get_field( 'thechamp_dontupdate_avatar', $profile_id ) ),
			),

			'submit_value'    => 'Update Profile',
			'return'          => wp_get_referer(),
			'updated_message' => false,
		)
	);

	echo '</div>';

	return ob_get_clean();
}

add_action( 'acfe/form/submit_form/form=edit-user-profile', 'tcbp_public_edit_profile_submit' );

/**
 * Callback method to respond to submission of user profile edit form.
 *
 * @param array $form The form data to be displayed in the user profile.
 */
function tcbp_public_edit_profile_submit( $form ) {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id    = $user->ID;
	$profile_id = 'user_' . $user_id;

	$post_id = $form['post_id'];

	wp_update_user(
		array(
			'ID'           => $user_id,
			'first_name'   => get_field( 'fe_first_name' ),
			'last_name'    => get_field( 'fe_last_name' ),
			'nickname'     => get_field( 'fe_display_name' ),
			'user_email'   => get_field( 'fe_email' ),
			'display_name' => get_field( 'fe_display_name' ),
		)
	);

	update_field( 'communication_preference', get_field( 'fe_communication_preference' ), $profile_id );
	update_field( 'user-location', get_field( 'fe_user_location' ), $profile_id );
	update_field( 'thechamp_avatar', get_field( 'fe_thechamp_avatar' ), $profile_id );
	update_field( 'thechamp_large_avatar', get_field( 'fe_thechamp_large_avatar' ), $profile_id );
	update_field( 'thechamp_dontupdate_avatar', get_field( 'fe_thechamp_dontupdate_avatar' ), $profile_id );

	$discord_username = get_field( 'fe_discord_username' );
	update_field( 'discord_username', $discord_username, $profile_id );
	if ( $discord_username ) {
		$discord_id = tcb_roster_admin_query_discord_username( $discord_username );
		if ( $discord_id ) {
			update_field( 'discord_id', $discord_id, $profile_id );
			tcb_roster_admin_post_to_discord_dm( $discord_id, 'Your profile has been updated.' );
		}
	}

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited own user profile' );
	}
}
