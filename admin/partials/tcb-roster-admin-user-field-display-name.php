<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-user-field-display-name.php
 * Description: Several ACF User fields on the Event post type - "slot_member" (the slots
 * repeater, see slotting.php/mission.php), "stamp_user" (the stamp repeater, see
 * attendance.php/mission-admin.php), and "user" (the rsvp repeater, see attendance.php) - show
 * each user in their wp-admin dropdown/search results using ACF's own default formatting,
 * which doesn't necessarily match the WP display name used everywhere else on the site
 * (front-end slotting tool, service records, etc.). This forces all three to always show
 * display_name.
 */

$tcbp_admin_user_field_names = array( 'slot_member', 'stamp_user', 'user' );
foreach ( $tcbp_admin_user_field_names as $tcbp_admin_user_field_name ) {
	add_filter( 'acf/fields/user/result/name=' . $tcbp_admin_user_field_name, 'tcbp_admin_user_field_display_name', 10, 2 );
}

/**
 * @param string  $text The text ACF would otherwise show for this user option.
 * @param WP_User $user The user being rendered.
 * @return string
 */
function tcbp_admin_user_field_display_name( $text, $user ) {
	return $user->display_name;
}
