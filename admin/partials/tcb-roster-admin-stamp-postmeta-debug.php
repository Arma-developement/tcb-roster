<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-stamp-postmeta-debug.php
 * Description: One-time, read-only admin tool to inspect the raw "stamp" postmeta for a
 * single Event post - the ACF row-count value plus every stamp_N_* key actually stored -
 * bypassing get_field()/have_rows(), which only ever show rows up to the stored count and so
 * can't reveal a count/row desync. Used to investigate a report of a stamp row's date/time
 * apparently being overwritten in place rather than a new row being added. Safe to remove
 * this file once the investigation is done.
 */

add_action( 'admin_menu', 'tcbp_admin_stamp_postmeta_debug_menu' );

/**
 * Registers the debug page under Tools.
 */
function tcbp_admin_stamp_postmeta_debug_menu() {
	add_management_page(
		'Stamp Postmeta Debug',
		'Stamp Postmeta Debug',
		'manage_options',
		'tcbp-stamp-postmeta-debug',
		'tcbp_admin_stamp_postmeta_debug_page'
	);
}

/**
 * Renders the Tools > Stamp Postmeta Debug admin page: every raw stamp/stamp_* postmeta
 * key/value for a given post ID, in storage order, alongside what get_field('stamp', ...)
 * currently returns for comparison.
 */
function tcbp_admin_stamp_postmeta_debug_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Read-only GET form - no state is changed by this page, so no nonce is needed.
	$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 2776; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	echo '<div class="wrap"><h1>Stamp Postmeta Debug</h1>';
	echo '<form method="get"><input type="hidden" name="page" value="tcbp-stamp-postmeta-debug">';
	echo '<label>Post ID: <input type="number" name="post_id" value="' . esc_attr( $post_id ) . '"></label> ';
	echo '<button type="submit" class="button">Load</button></form>';

	if ( ! $post_id || ! get_post( $post_id ) ) {
		echo '<p>No post found for that ID.</p></div>';
		return;
	}

	echo '<h2>' . esc_html( get_the_title( $post_id ) ) . ' (#' . (int) $post_id . ')</h2>';

	// Raw postmeta, unfiltered by ACF's row-count - reveals orphaned rows a count desync
	// would otherwise hide from get_field()/have_rows().
	$all_meta   = get_post_meta( $post_id );
	$stamp_meta = array();
	foreach ( $all_meta as $key => $values ) {
		if ( 'stamp' === $key || 0 === strpos( $key, 'stamp_' ) || 0 === strpos( $key, '_stamp' ) ) {
			$stamp_meta[ $key ] = $values;
		}
	}
	ksort( $stamp_meta );

	echo '<h3>Raw postmeta (stamp*)</h3>';
	echo '<table class="widefat striped"><thead><tr><th>meta_key</th><th>meta_value</th></tr></thead><tbody>';
	foreach ( $stamp_meta as $key => $values ) {
		foreach ( $values as $value ) {
			echo '<tr><td><code>' . esc_html( $key ) . '</code></td><td><code>' . esc_html( $value ) . '</code></td></tr>';
		}
	}
	echo '</tbody></table>';

	echo '<h3>get_field(\'stamp\', ' . (int) $post_id . ') - what ACF currently exposes</h3>';
	$rows = get_field( 'stamp', $post_id );
	if ( ! $rows ) {
		echo '<p>(empty)</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr><th>row index</th><th>stamp_user</th><th>stamp_date</th><th>stamp_time</th></tr></thead><tbody>';
		foreach ( $rows as $index => $row ) {
			echo '<tr><td>' . (int) $index . '</td><td>' . esc_html( wp_json_encode( $row['stamp_user'] ) ) . '</td><td>' . esc_html( $row['stamp_date'] ) . '</td><td>' . esc_html( $row['stamp_time'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	echo '</div>';
}
