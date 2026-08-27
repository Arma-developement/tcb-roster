<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-discord-username-check.php
 * Description: Read-only admin tool to quickly find non-administrator users with a Discord
 * username problem - either none set at all, or one set that never resolved to a Discord ID
 * (see tcbp_public_edit_profile_submit() in user-profile.php, and the "couldn't find that
 * Discord username" error it now surfaces on the front-end profile form). Grouped by role
 * (Limited Members / Members / Subscribers) so issues can be triaged by who's affected.
 */

add_action( 'admin_menu', 'tcbp_admin_discord_username_check_menu' );

/**
 * Registers the check page under Tools.
 */
function tcbp_admin_discord_username_check_menu() {
	add_management_page(
		'Discord Username Check',
		'Discord Username Check',
		'manage_options',
		'tcbp-discord-username-check',
		'tcbp_admin_discord_username_check_page'
	);
}

/**
 * Scans every non-administrator user for a Discord username problem, grouped by role.
 *
 * @return array role_key => array( 'label' => ..., 'users' => array of entries ).
 */
function tcbp_admin_discord_username_check_scan() {

	$groups = array(
		'limited_member' => array(
			'label' => 'Limited Members',
			'users' => array(),
		),
		'member'         => array(
			'label' => 'Members',
			'users' => array(),
		),
		'subscriber'     => array(
			'label' => 'Subscribers',
			'users' => array(),
		),
		// Catch-all for anyone not carrying one of the three roles above (and not an
		// administrator) - e.g. banned, or no role at all - so they're surfaced rather than
		// silently dropped.
		'other'          => array(
			'label' => 'Other / Unclassified',
			'users' => array(),
		),
	);

	$users = get_users( array( 'fields' => array( 'ID' ) ) );

	foreach ( $users as $row ) {
		$user = get_userdata( $row->ID );
		if ( ! $user || in_array( 'administrator', $user->roles, true ) ) {
			continue;
		}

		if ( in_array( 'limited_member', $user->roles, true ) ) {
			$group_key = 'limited_member';
		} elseif ( in_array( 'member', $user->roles, true ) ) {
			// Every rank above Recruit (NCO/SNCO/Officer) carries the "member" role alongside
			// its own duty role (see tcbp_public_sr_assign_role_by_rank(), service-record.php),
			// so grouping on "member" naturally covers all of them under one bucket, matching
			// the three-way split asked for rather than needing a rank-by-rank breakdown.
			$group_key = 'member';
		} elseif ( in_array( 'subscriber', $user->roles, true ) ) {
			$group_key = 'subscriber';
		} else {
			$group_key = 'other';
		}

		$profile_id       = 'user_' . $user->ID;
		$discord_username = get_field( 'discord_username', $profile_id );
		$discord_id       = get_field( 'discord_id', $profile_id );

		if ( ! $discord_username ) {
			$status = 'No Discord username set';
		} elseif ( ! $discord_id ) {
			$status = 'Username set, but never resolved to a Discord ID';
		} else {
			continue; // Nothing wrong - don't list.
		}

		$groups[ $group_key ]['users'][] = array(
			'user_id'          => $user->ID,
			'display_name'     => $user->display_name,
			'discord_username' => $discord_username,
			'status'           => $status,
		);
	}

	foreach ( $groups as $key => $group ) {
		usort(
			$groups[ $key ]['users'],
			function ( $a, $b ) {
				return strcasecmp( $a['display_name'], $b['display_name'] );
			}
		);
	}

	return $groups;
}

/**
 * Renders the Tools > Discord Username Check admin page.
 */
function tcbp_admin_discord_username_check_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$groups = tcbp_admin_discord_username_check_scan();

	echo '<div class="wrap"><h1>Discord Username Check</h1>';
	echo '<p>Non-administrator users with no Discord username set, or one that never resolved to a Discord ID. Read-only - fix a player\'s username via their profile or service record, then reload this page.</p>';

	$total = 0;
	foreach ( $groups as $group ) {
		$total += count( $group['users'] );
	}
	if ( ! $total ) {
		echo '<p>No issues found.</p></div>';
		return;
	}

	foreach ( $groups as $group ) {
		if ( ! $group['users'] ) {
			continue;
		}

		echo '<h2>' . esc_html( $group['label'] ) . ' (' . count( $group['users'] ) . ')</h2>';
		echo '<table class="widefat striped"><thead><tr><th>Player</th><th>Discord Username</th><th>Issue</th></tr></thead><tbody>';
		foreach ( $group['users'] as $entry ) {
			echo '<tr>';
			echo '<td><a href="' . esc_url( get_edit_user_link( $entry['user_id'] ) ) . '">' . esc_html( $entry['display_name'] ) . '</a></td>';
			echo '<td>' . esc_html( $entry['discord_username'] ? $entry['discord_username'] : '—' ) . '</td>';
			echo '<td>' . esc_html( $entry['status'] ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	echo '</div>';
}
