<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-stamp-date-repair.php
 * Description: One-time admin tool to find and correct "stamp" repeater rows (Event RSVP
 * signup timestamps) corrupted between 4-12 Aug 2026 by a bug in attendance.php, where ACF
 * misparsed a slash-separated d/m/Y date string as US month/day/year - swapping day and
 * month for any signup made on the 4th-12th of that month. See the fix in attendance.php
 * (writing stamp_date as Ymd instead). Safe to remove this file once the cleanup is done.
 */

add_action( 'admin_menu', 'tcbp_admin_stamp_date_repair_menu' );

/**
 * Registers the review/repair page under Tools.
 */
function tcbp_admin_stamp_date_repair_menu() {
	add_management_page(
		'Repair Stamp Dates',
		'Repair Stamp Dates',
		'manage_options',
		'tcbp-repair-stamp-dates',
		'tcbp_admin_stamp_date_repair_page'
	);
}

/**
 * Scans all Events for stamp rows matching the corruption signature: a stamp_date that
 * displays (per its d/m/Y Return Format) as 08/XX/2026, where XX is 04-12. That pattern can
 * only be produced by the real date being XX August 2026 and getting month/day-swapped by
 * the bug - genuine data can't collide with it (day 08 combined with a month of 04-12,
 * specifically for year 2026, is the exact and only fingerprint the bug leaves behind).
 *
 * @return array Candidate rows keyed by "{post_id}_{row_index}".
 */
function tcbp_admin_find_corrupted_stamp_rows() {

	$candidates = array();

	$events = get_posts(
		array(
			'post_type'      => 'tribe_events',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		)
	);

	foreach ( $events as $event ) {

		$rows = get_field( 'stamp', $event->ID );
		if ( ! $rows ) {
			continue;
		}

		foreach ( $rows as $index => $row ) {

			$stamp_date = $row['stamp_date'];
			if ( ! preg_match( '#^08/(0[4-9]|1[0-2])/2026$#', $stamp_date, $matches ) ) {
				continue;
			}

			$real_day  = $matches[1];
			$corrected = $real_day . '/08/2026';

			$user       = get_userdata( $row['stamp_user'] );
			$event_date = get_field( 'event_start_date', $event->ID );

			$candidates[ $event->ID . '_' . $index ] = array(
				'post_id'    => $event->ID,
				'row_index'  => $index,
				'stored'     => $stamp_date,
				'corrected'  => $corrected,
				'user_name'  => $user ? $user->display_name : ( 'Unknown (#' . $row['stamp_user'] . ')' ),
				'event_date' => $event_date,
			);
		}
	}

	return $candidates;
}

/**
 * Writes the corrected date (as Ymd, ACF's internal Date Picker storage format) for each
 * selected candidate row.
 *
 * @param array $candidates    All candidates, as returned by tcbp_admin_find_corrupted_stamp_rows().
 * @param array $selected_keys Keys (from $candidates) the admin ticked for correction.
 * @return int Number of rows corrected.
 */
function tcbp_admin_apply_stamp_repair( $candidates, $selected_keys ) {

	$fixed = 0;

	foreach ( $selected_keys as $key ) {
		if ( ! isset( $candidates[ $key ] ) ) {
			continue;
		}

		$c = $candidates[ $key ];

		list( $day, $month, $year ) = explode( '/', $c['corrected'] );
		$ymd                        = $year . $month . $day;

		// Row numbers for update_sub_field() are 1-based, but $c['row_index'] came from a
		// zero-indexed PHP array (get_field()'s row list) - same convention already used by
		// attendance.php's tcbp_public_attendance_remove_user() via get_row_index().
		update_sub_field( array( 'stamp', $c['row_index'] + 1, 'stamp_date' ), $ymd, $c['post_id'] );
		++$fixed;
	}

	return $fixed;
}

/**
 * Renders the Tools > Repair Stamp Dates admin page: a review table of candidate rows with
 * their event and stored/corrected values, and a form to apply the correction to whichever
 * rows are ticked (all ticked by default).
 */
function tcbp_admin_stamp_date_repair_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = '';

	if ( isset( $_POST['tcbp_apply_stamp_repair'] ) && check_admin_referer( 'tcbp_repair_stamp_dates' ) ) {
		$candidates    = tcbp_admin_find_corrupted_stamp_rows();
		$selected_keys = isset( $_POST['rows'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['rows'] ) ) : array();
		$fixed         = tcbp_admin_apply_stamp_repair( $candidates, $selected_keys );
		$notice        = $fixed . ' row(s) corrected.';
	}

	$candidates = tcbp_admin_find_corrupted_stamp_rows();

	echo '<div class="wrap"><h1>Repair Stamp Dates</h1>';

	if ( $notice ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>';
	}

	echo '<p>Rows below match the signature left by the 4-12 Aug 2026 date-swap bug (see attendance.php). Review the event date for context, untick anything that looks wrong, then apply.</p>';

	if ( ! $candidates ) {
		echo '<p>No candidate rows found.</p></div>';
		return;
	}

	echo '<form method="post">';
	wp_nonce_field( 'tcbp_repair_stamp_dates' );
	echo '<table class="widefat striped"><thead><tr><th></th><th>Event</th><th>Event Date</th><th>User</th><th>Stored (wrong)</th><th>Will become</th></tr></thead><tbody>';

	foreach ( $candidates as $key => $c ) {
		echo '<tr>';
		echo '<td><input type="checkbox" name="rows[]" value="' . esc_attr( $key ) . '" checked></td>';
		echo '<td><a href="' . esc_url( get_edit_post_link( $c['post_id'] ) ) . '">' . esc_html( get_the_title( $c['post_id'] ) ) . '</a></td>';
		echo '<td>' . esc_html( $c['event_date'] ) . '</td>';
		echo '<td>' . esc_html( $c['user_name'] ) . '</td>';
		echo '<td>' . esc_html( $c['stored'] ) . '</td>';
		echo '<td>' . esc_html( $c['corrected'] ) . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
	echo '<p><button type="submit" name="tcbp_apply_stamp_repair" class="button button-primary">Apply correction to checked rows</button></p>';
	echo '</form></div>';
}
