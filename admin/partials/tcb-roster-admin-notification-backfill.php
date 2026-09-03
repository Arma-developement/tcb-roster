<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-notification-backfill.php
 * Description: One-time admin tool to backfill the "_tcbp_notified_*" idempotency flags (see
 * tcbp_public_notify_once(), application-notifications.php) for applications/service records
 * that already reached a given stage *before* the automated stage-notification feature
 * existed. Without this, tcbp_public_notify_once() finds no flag on such a record and treats
 * its *next* unrelated resave as that stage being reached for the first time, sending a stale
 * "Congratulations, you're now a X!" message.
 *
 * This is exactly what happened for Marines: tcbp_public_edit_sr_info_submission_callback()
 * (service-record.php) unconditionally calls tcbp_public_sr_promote_to_marine() on *every*
 * service-record save whenever the SR's current rank is already Marine - not only when it
 * changes to Marine - relying entirely on the idempotency flag to make that safe. A Marine
 * promoted long before this feature existed never got that flag set, so the next edit to their
 * service record for any reason (here, awarding a Meetups commendation) looked like a fresh
 * promotion and re-sent the welcome message. The same gap exists for the interview/candidate/
 * recruit/selection/rejected application-stage messages, for any application resting at one of
 * those statuses from before this feature existed.
 *
 * Safe to run more than once (already-flagged records are left untouched) and safe to remove
 * this file once the backfill has been run.
 */

add_action( 'admin_menu', 'tcbp_admin_notification_backfill_menu' );

/**
 * Registers the tool page under Tools.
 */
function tcbp_admin_notification_backfill_menu() {
	add_management_page(
		'Backfill Notification Flags',
		'Backfill Notification Flags',
		'manage_options',
		'tcbp-backfill-notification-flags',
		'tcbp_admin_notification_backfill_page'
	);
}

/**
 * Application statuses handled by tcbp_public_application_stage_notifications()'s switch,
 * mapped to their idempotency meta key. "archived" is deliberately not included here - that
 * case delegates entirely to the service record side (see tcbp_admin_run_notification_backfill()
 * below), so there's no meta key on the application post itself for it.
 *
 * @return array Status slug => meta key.
 */
function tcbp_admin_notification_backfill_status_map() {
	return array(
		'interview' => '_tcbp_notified_interview',
		'candidate' => '_tcbp_notified_candidate',
		'recruit'   => '_tcbp_notified_recruit',
		'selection' => '_tcbp_notified_selection',
		'rejected'  => '_tcbp_notified_rejected',
	);
}

/**
 * Renders the tool page and runs the backfill when its form is submitted.
 */
function tcbp_admin_notification_backfill_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	echo '<div class="wrap"><h1>Backfill Notification Flags</h1>';
	echo '<p>Marks every application/service record already at a given stage as already-notified, so the automated stage-notification feature doesn&rsquo;t treat the next unrelated edit as a fresh transition and send a stale message. Safe to run more than once - already-flagged records are left untouched.</p>';

	if ( isset( $_POST['tcbp_backfill_run'] ) && check_admin_referer( 'tcbp_backfill_notification_flags' ) ) {
		$results = tcbp_admin_run_notification_backfill();
		echo '<div class="notice notice-success"><p>Done. ' . esc_html( $results['applications'] ) . ' application flag(s) and ' . esc_html( $results['service_records'] ) . ' service record flag(s) newly set (out of ' . esc_html( $results['applications_scanned'] ) . ' application(s) and ' . esc_html( $results['service_records_scanned'] ) . ' Marine service record(s) scanned).</p></div>';
	}

	echo '<form method="post">';
	wp_nonce_field( 'tcbp_backfill_notification_flags' );
	echo '<p><button type="submit" name="tcbp_backfill_run" value="1" class="button button-primary">Run backfill</button></p>';
	echo '</form></div>';
}

/**
 * Does the actual backfill: for every application currently at a status
 * tcbp_admin_notification_backfill_status_map() covers, and for every service record currently
 * at Marine rank, sets the matching "_tcbp_notified_*" flag if it isn't already set. Only ever
 * writes the flag - never calls the notification callbacks themselves, so this can't send any
 * messages on its own.
 *
 * @return array {
 *     @type int $applications             Application flags newly set.
 *     @type int $service_records          Service record flags newly set.
 *     @type int $applications_scanned     Applications scanned.
 *     @type int $service_records_scanned  Marine service records scanned.
 * }
 */
function tcbp_admin_run_notification_backfill() {
	$status_map        = tcbp_admin_notification_backfill_status_map();
	$applications_set  = 0;

	$applications = get_posts(
		array(
			'post_type'      => 'application',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		)
	);

	foreach ( $applications as $application ) {
		$terms = wp_get_post_terms( $application->ID, 'tcb-selection', array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}
		$status = $terms[0];
		if ( ! isset( $status_map[ $status ] ) ) {
			continue;
		}
		$meta_key = $status_map[ $status ];
		if ( get_post_meta( $application->ID, $meta_key, true ) ) {
			continue;
		}
		update_post_meta( $application->ID, $meta_key, current_time( 'mysql' ) );
		++$applications_set;
	}

	$service_records_set = 0;

	$service_records = get_posts(
		array(
			'post_type'      => 'service-record',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'tcb-rank',
					'field'    => 'term_id',
					'terms'    => TCBP_RANK_MARINE,
				),
			),
		)
	);

	foreach ( $service_records as $service_record ) {
		if ( get_post_meta( $service_record->ID, '_tcbp_notified_marine', true ) ) {
			continue;
		}
		update_post_meta( $service_record->ID, '_tcbp_notified_marine', current_time( 'mysql' ) );
		++$service_records_set;
	}

	return array(
		'applications'            => $applications_set,
		'service_records'         => $service_records_set,
		'applications_scanned'    => count( $applications ),
		'service_records_scanned' => count( $service_records ),
	);
}
