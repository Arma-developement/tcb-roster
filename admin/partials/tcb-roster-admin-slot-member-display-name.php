<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-slot-member-display-name.php
 * Description: The "slot_member" ACF User field (part of the Event's slots repeater - see
 * slotting.php/mission.php for how it's read/written on the front end) shows each user in its
 * wp-admin dropdown/search results using ACF's own default formatting, which doesn't
 * necessarily match the WP display name used everywhere else on the site (front-end slotting
 * tool, service records, etc.). This forces it to always show display_name.
 */

add_filter( 'acf/fields/user/result/name=slot_member', 'tcbp_admin_slot_member_user_result', 10, 2 );

/**
 * @param string  $text The text ACF would otherwise show for this user option.
 * @param WP_User $user The user being rendered.
 * @return string
 */
function tcbp_admin_slot_member_user_result( $text, $user ) {
	return $user->display_name;
}
