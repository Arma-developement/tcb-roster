<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-stamp-duplicate-cleanup.php
 * Description: One-time admin tool to find and remove duplicate "stamp" repeater rows (Event
 * RSVP signup timestamps) for the same user on the same event. Caused by a strict-type ===
 * comparison bug in attendance.php/mission-admin.php that could fail to recognise a user's
 * existing stamp row (now fixed via tcbp_public_normalize_user_id()), letting a fresh stamp
 * get added every time that user fully unregistered and re-registered. For each duplicate
 * group this keeps the earliest row (the original signup time, matching design intent) and
 * lets an admin review/remove the rest. Safe to delete this file once the cleanup is done.
 */

add_action( 'admin_menu', 'tcbp_admin_stamp_duplicate_cleanup_menu' );

/**
 * Registers the review/cleanup page under Tools.
 */
function tcbp_admin_stamp_duplicate_cleanup_menu() {
	add_management_page(
		'Clean Up Duplicate Stamps',
		'Clean Up Duplicate Stamps',
		'manage_options',
		'tcbp-stamp-duplicate-cleanup',
		'tcbp_admin_stamp_duplicate_cleanup_page'
	);
}

/**
 * Scans all Events for stamp rows sharing the same (normalised) stamp_user within the same
 * event. Rows that can't be confidently ordered (unparseable stamp_date/stamp_time) are
 * included in the group but never auto-selected for removal.
 *
 * @return array Duplicate groups keyed by "{post_id}_{user_id}", each with 'post_id',
 *               'user_name', and 'rows' (each row: 'row_index', 'label', 'keep', 'unparseable').
 */
function tcbp_admin_find_duplicate_stamp_rows() {

	$groups = array();

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

		// Bucket this event's rows by normalised user ID.
		$by_user = array();
		foreach ( $rows as $index => $row ) {
			$user_id             = tcbp_public_normalize_user_id( $row['stamp_user'] );
			$by_user[ $user_id ] = isset( $by_user[ $user_id ] ) ? $by_user[ $user_id ] : array();
			$by_user[ $user_id ][] = array(
				'row_index'  => $index,
				'stamp_date' => $row['stamp_date'],
				'stamp_time' => $row['stamp_time'],
			);
		}

		foreach ( $by_user as $user_id => $user_rows ) {
			if ( count( $user_rows ) < 2 ) {
				continue;
			}

			// Determine each row's timestamp so the earliest can be kept; rows that fail to
			// parse are never auto-selected for removal, since we can't confidently order them.
			foreach ( $user_rows as $key => $row ) {
				$datetime = DateTimeImmutable::createFromFormat( 'd/m/Y H:i:s', $row['stamp_date'] . ' ' . $row['stamp_time'] );

				$user_rows[ $key ]['timestamp']    = $datetime ? $datetime->getTimestamp() : null;
				$user_rows[ $key ]['unparseable']  = ! $datetime;
				$user_rows[ $key ]['label']        = $row['stamp_date'] . ' ' . $row['stamp_time'];
			}

			$parseable_timestamps = array_filter(
				wp_list_pluck( $user_rows, 'timestamp' ),
				function ( $t ) {
					return null !== $t;
				}
			);
			$earliest = $parseable_timestamps ? min( $parseable_timestamps ) : null;

			foreach ( $user_rows as $key => $row ) {
				$user_rows[ $key ]['keep'] = $row['unparseable'] || ( null !== $earliest && $row['timestamp'] === $earliest );
			}

			$user = get_userdata( $user_id );

			$groups[ $event->ID . '_' . $user_id ] = array(
				'post_id'   => $event->ID,
				'user_name' => $user ? $user->display_name : ( 'Unknown (#' . $user_id . ')' ),
				'rows'      => $user_rows,
			);
		}
	}

	return $groups;
}

/**
 * Removes the rows selected for deletion, per event, highest row index first so earlier
 * indices in the same post stay valid as each delete_row() call shifts the rest down.
 *
 * @param array $to_delete Array of array( 'post_id' => int, 'row_index' => int ) (zero-indexed,
 *                          as returned by get_field()).
 * @return int Number of rows removed.
 */
function tcbp_admin_apply_stamp_duplicate_cleanup( $to_delete ) {

	// Group by post, then sort each post's row indices descending.
	$by_post = array();
	foreach ( $to_delete as $item ) {
		$by_post[ $item['post_id'] ][] = $item['row_index'];
	}

	$removed = 0;

	foreach ( $by_post as $post_id => $row_indices ) {
		rsort( $row_indices );
		foreach ( $row_indices as $row_index ) {
			// delete_row() row numbers are 1-based, same convention as update_sub_field()
			// elsewhere in this plugin (see tcbp_admin_apply_stamp_repair()).
			if ( delete_row( 'stamp', $row_index + 1, $post_id ) ) {
				++$removed;
			}
		}
	}

	return $removed;
}

/**
 * Renders the Tools > Clean Up Duplicate Stamps admin page: a review table grouped by
 * event/user, with the row to keep pre-selected and the rest pre-ticked for removal.
 */
function tcbp_admin_stamp_duplicate_cleanup_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = '';

	if ( isset( $_POST['tcbp_apply_stamp_duplicate_cleanup'] ) && check_admin_referer( 'tcbp_stamp_duplicate_cleanup' ) ) {
		$selected = isset( $_POST['remove'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['remove'] ) ) : array();

		$to_delete = array();
		foreach ( $selected as $value ) {
			// Each checkbox value is "{post_id}_{row_index}".
			$parts = explode( '_', $value );
			if ( 2 !== count( $parts ) ) {
				continue;
			}
			$to_delete[] = array(
				'post_id'   => (int) $parts[0],
				'row_index' => (int) $parts[1],
			);
		}

		$removed = tcbp_admin_apply_stamp_duplicate_cleanup( $to_delete );
		$notice  = $removed . ' duplicate row(s) removed.';
	}

	$groups = tcbp_admin_find_duplicate_stamp_rows();

	echo '<div class="wrap"><h1>Clean Up Duplicate Stamps</h1>';

	if ( $notice ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $notice ) . '</p></div>';
	}

	echo '<p>Each group below is one user with more than one stamp row on the same event. The earliest row (their original signup time) is pre-selected to keep; the rest are pre-ticked for removal. Rows whose date/time couldn\'t be parsed are never pre-ticked - review those manually.</p>';

	if ( ! $groups ) {
		echo '<p>No duplicate stamp rows found.</p></div>';
		return;
	}

	echo '<form method="post">';
	wp_nonce_field( 'tcbp_stamp_duplicate_cleanup' );
	echo '<table class="widefat striped"><thead><tr><th>Remove?</th><th>Event</th><th>User</th><th>Stamp</th><th>Status</th></tr></thead><tbody>';

	foreach ( $groups as $group ) {
		foreach ( $group['rows'] as $row ) {
			$status = $row['unparseable'] ? 'Unparseable - review manually' : ( $row['keep'] ? 'Earliest - keep' : 'Duplicate' );
			echo '<tr>';
			if ( $row['keep'] ) {
				echo '<td>&mdash;</td>';
			} else {
				$value = $group['post_id'] . '_' . $row['row_index'];
				echo '<td><input type="checkbox" name="remove[]" value="' . esc_attr( $value ) . '" checked></td>';
			}
			echo '<td><a href="' . esc_url( get_edit_post_link( $group['post_id'] ) ) . '">' . esc_html( get_the_title( $group['post_id'] ) ) . '</a></td>';
			echo '<td>' . esc_html( $group['user_name'] ) . '</td>';
			echo '<td>' . esc_html( $row['label'] ) . '</td>';
			echo '<td>' . esc_html( $status ) . '</td>';
			echo '</tr>';
		}
	}

	echo '</tbody></table>';
	echo '<p><button type="submit" name="tcbp_apply_stamp_duplicate_cleanup" class="button button-primary">Remove checked rows</button></p>';
	echo '</form></div>';
}
