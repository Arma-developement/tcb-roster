<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-rank-role-audit.php
 * Description: Read-only diagnostic tool - lists every service record whose linked user's
 * actual WP role(s) don't match what tcbp_public_sr_roles_for_rank() (service-record.php) says
 * their current tcb-rank should carry. Doesn't write anything; just reports.
 *
 * Written to chase down a wiki-access report: an officer couldn't see a wiki section an SNCO
 * could, despite both the article and its category being configured identically (member,
 * limited_member, subscriber). By design, both an SNCO and an Officer should hold "member"
 * alongside their own role (see tcbp_public_sr_roles_for_rank()) - wiki access (and anything
 * else gated on "member") is checked against that, not against officer/snco individually. If a
 * user's roles fell out of sync with their rank - e.g. promoted before the role-sync logic was
 * correct, or edited directly via wp-admin's native (single-role) Users screen instead of
 * through the roster's own rank field - they'd be missing "member" and silently lose access to
 * anything gated on it, without their rank itself looking wrong anywhere.
 */

add_action( 'admin_menu', 'tcbp_admin_rank_role_audit_menu' );

/**
 * Registers the audit page under Tools.
 */
function tcbp_admin_rank_role_audit_menu() {
	add_management_page(
		'Audit Rank Roles',
		'Audit Rank Roles',
		'manage_options',
		'tcbp-audit-rank-roles',
		'tcbp_admin_rank_role_audit_page'
	);
}

/**
 * Renders the audit report. Read-only - no form, no writes, just a scan run on every page load
 * of this Tools page.
 */
function tcbp_admin_rank_role_audit_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="wrap"><h1>Audit Rank Roles</h1>';
	echo '<p>Compares every service record\'s current rank against the WP role(s) tcbp_public_sr_roles_for_rank() says that rank should carry, and flags any user who is missing an expected role or is still carrying a stale one from a previous rank. Read-only - nothing here is changed automatically.</p>';

	$mismatches = tcbp_admin_find_rank_role_mismatches();

	if ( ! $mismatches ) {
		echo '<div class="notice notice-success"><p>No mismatches found - every service record\'s user roles match their current rank.</p></div>';
		echo '</div>';
		return;
	}

	echo '<table class="widefat striped"><thead><tr><th>User</th><th>Current Rank</th><th>Missing Role(s)</th><th>Stale Role(s)</th><th>Service Record</th></tr></thead><tbody>';
	foreach ( $mismatches as $row ) {
		echo '<tr>';
		echo '<td><a href="' . esc_url( get_edit_user_link( $row['user_id'] ) ) . '">' . esc_html( $row['display_name'] ) . '</a></td>';
		echo '<td>' . esc_html( $row['rank_name'] ) . '</td>';
		echo '<td>' . esc_html( $row['missing'] ? implode( ', ', $row['missing'] ) : '-' ) . '</td>';
		echo '<td>' . esc_html( $row['stale'] ? implode( ', ', $row['stale'] ) : '-' ) . '</td>';
		echo '<td><a href="' . esc_url( get_edit_post_link( $row['post_id'] ) ) . '">Edit</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	echo '</div>';
}

/**
 * Scans every service-record post and compares its linked user's actual roles against
 * tcbp_public_sr_roles_for_rank()'s expected roles for that record's current tcb-rank.
 *
 * "Stale" roles are reported against tcbp_public_sr_rank_reset_roles()'s full rank-tier role
 * set (minus "editor", which is a separately-granted capability, not rank-derived - see that
 * function's own doc comment) rather than just the previous rank's own role(s), since there's no
 * record of what rank someone held before - only whether they're currently holding a rank-tier
 * role that their CURRENT rank doesn't call for.
 *
 * @return array List of mismatch rows, each with user_id/display_name/rank_name/missing/stale/post_id.
 */
function tcbp_admin_find_rank_role_mismatches() {
	$mismatches = array();

	$service_records = get_posts(
		array(
			'post_type'      => 'service-record',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		)
	);

	foreach ( $service_records as $service_record ) {

		$terms = get_the_terms( $service_record->ID, 'tcb-rank' );
		if ( ! $terms || is_wp_error( $terms ) || empty( $terms[0] ) ) {
			continue;
		}
		$rank_term_id = (int) $terms[0]->term_id;

		$user_id = get_field( 'user_id', $service_record->ID );
		$user    = $user_id ? get_user_by( 'id', $user_id ) : false;
		if ( ! $user ) {
			continue;
		}

		$expected_roles     = tcbp_public_sr_roles_for_rank( $rank_term_id );
		$rank_tier_roles    = array_diff( tcbp_public_sr_rank_reset_roles( $rank_term_id ), array( 'editor' ) );
		$actual_roles       = $user->roles;

		$missing = array_diff( $expected_roles, $actual_roles );
		$stale   = array_diff( array_intersect( $actual_roles, $rank_tier_roles ), $expected_roles );

		if ( ! $missing && ! $stale ) {
			continue;
		}

		$mismatches[] = array(
			'user_id'      => $user_id,
			'display_name' => $user->display_name,
			'rank_name'    => $terms[0]->name,
			'missing'      => array_values( $missing ),
			'stale'        => array_values( $stale ),
			'post_id'      => $service_record->ID,
		);
	}

	return $mismatches;
}
