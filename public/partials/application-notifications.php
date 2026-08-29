<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: application-notifications.php
 * Description: Automates the messages that used to have to be sent manually every time an
 * applicant moves to a new stage of the selection process (tcb-selection taxonomy, on the
 * "application" post type) - an admin-channel message, an applicant message, or both,
 * depending on the stage. See tcbp_public_application_stage_notifications() for the full
 * per-stage behaviour.
 *
 * Deliberately does not fire for the "enrol an existing member" shortcut, where an application
 * jumps straight from Submission to Archived - each notification only fires the first time its
 * specific status is actually reached (see tcbp_public_notify_once()), so a status that's never
 * passed through along the way never notifies.
 */

/**
 * Notifies one user via their preferred communication method(s) - the same
 * communication_preference (a multi-select of "discord"/"email") + discord_id + wp_mail()
 * pattern tcbp_public_mission_send_password_notifications() (mission-admin.php) already uses
 * for op passwords. Silently does nothing if the user has no preference set, or no contact
 * info for their chosen method(s) - matches that existing function's own behaviour.
 *
 * @param int    $user_id         The user to notify.
 * @param string $discord_message Message text for the Discord DM.
 * @param string $email_subject   Email subject line.
 * @param string $email_message   Email body.
 */
function tcbp_public_notify_user_by_preference( $user_id, $discord_message, $email_subject, $email_message ) {

	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return;
	}

	$profile_id = 'user_' . $user_id;
	$preference = get_field( 'communication_preference', $profile_id );
	if ( ! $preference ) {
		return;
	}

	if ( in_array( 'discord', $preference, true ) ) {
		$discord_id = get_field( 'discord_id', $profile_id );
		if ( $discord_id ) {
			tcb_roster_admin_post_to_discord_dm( array( $discord_id ), $discord_message );
		}
	}

	if ( in_array( 'email', $preference, true ) ) {
		wp_mail( $user->user_email, $email_subject, $email_message );
	}
}

/**
 * Runs $callback exactly once per $post_id, tracked via a post-meta flag - re-saving the same
 * status (e.g. an admin resubmits the Edit Status form without changing anything) never
 * re-sends a notification that's already gone out.
 *
 * @param int      $post_id  The post to track the flag against.
 * @param string   $meta_key The flag's meta key, unique per notification.
 * @param callable $callback Runs only the first time this is called for this post/meta_key.
 */
function tcbp_public_notify_once( $post_id, $meta_key, $callback ) {
	if ( get_post_meta( $post_id, $meta_key, true ) ) {
		return;
	}
	update_post_meta( $post_id, $meta_key, current_time( 'mysql' ) );
	call_user_func( $callback );
}

add_action( 'acf/save_post', 'tcbp_public_application_stage_notifications', 30 );

/**
 * Dispatches the stage-appropriate notification(s) whenever an application's tcb-selection
 * status reaches Interview, Candidate, Recruit, Selection, Rejected, or (via promotion to
 * Marine) Archived - for the first time. Priority 30 so this always runs after
 * tcbp_public_interview_transition_status()'s priority-20 auto-transition, meaning a single
 * interview-form submission that moves an application straight to Candidate/Rejected already
 * sees that new status here, in the same save.
 *
 * @param mixed $post_id_ ACF's post_id for whatever was just saved.
 */
function tcbp_public_application_stage_notifications( $post_id_ ) {

	if ( 'application' !== get_post_type( $post_id_ ) ) {
		return;
	}

	$terms = wp_get_post_terms( $post_id_, 'tcb-selection', array( 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}
	$status = $terms[0];

	$user_id = (int) get_post_field( 'post_author', $post_id_ );
	$user    = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return;
	}
	$display_name = $user->display_name;
	$application_url = get_permalink( $post_id_ );

	switch ( $status ) {

		case 'interview':
			tcbp_public_notify_once(
				$post_id_,
				'_tcbp_notified_interview',
				function () use ( $display_name, $application_url, $user_id ) {
					$message = "{@Admins}\nInterview required for a new applicant: " . $display_name . "\n\n" . $application_url;
					tcb_roster_admin_post_to_discord_channel( 'recruitment-managers', $message );

					$applicant_message = 'Please look out for a message from an admin to organise an interview.';
					tcbp_public_notify_user_by_preference( $user_id, $applicant_message, '3CB Application - Interview', $applicant_message );
				}
			);
			break;

		case 'candidate':
			tcbp_public_notify_once(
				$post_id_,
				'_tcbp_notified_candidate',
				function () use ( $display_name, $application_url, $user_id ) {
					// '679687679863947264' is the basic-training-instructors channel - a raw
					// Discord channel ID, no named case needed (see the default branch in
					// tcb_roster_admin_post_to_discord_channel(), tcb-roster-admin-post-to-discord.php).
					$message = "{@Training Basic}\nBasic Training required for a new applicant: " . $display_name . "\n\n" . $application_url;
					tcb_roster_admin_post_to_discord_channel( '679687679863947264', $message );

					$onboarding_url    = home_url( '/information-centre/candidate-onboarding' );
					$applicant_message = "Congratulations, you're now a Candidate!\n\nPlease follow the onboarding instructions: " . $onboarding_url . "\n\nPlease look out for a message from an admin in the candidate-lobby to organise training.";
					tcbp_public_notify_user_by_preference( $user_id, $applicant_message, '3CB Application - Candidate', $applicant_message );
				}
			);
			break;

		case 'recruit':
			// Create the service record here (idempotent - a no-op if one already exists) so
			// it's always in place by the time anyone needs to view/edit it, the same way
			// tcbp_public_sr_create_if_missing() already runs for the Archived/Marine case
			// below. This is what lets the manual "Create Service Record" button go away.
			// Rank/training are only applied if this call actually creates a new record - see
			// tcbp_public_sr_create_if_missing()'s own doc comment.
			tcbp_public_sr_create_if_missing( $user_id, TCBP_RANK_RECRUIT, array( 'basic-1', 'basic-2' ) );

			tcbp_public_notify_once(
				$post_id_,
				'_tcbp_notified_recruit',
				function () use ( $user_id ) {
					$onboarding_url    = home_url( '/information-centre/recruit-onboarding' );
					$applicant_message = "Congratulations, you're now a Recruit!\n\nPlease follow the onboarding instructions: " . $onboarding_url;
					tcbp_public_notify_user_by_preference( $user_id, $applicant_message, '3CB Application - Recruit', $applicant_message );
				}
			);
			break;

		case 'selection':
			tcbp_public_notify_once(
				$post_id_,
				'_tcbp_notified_selection',
				function () use ( $display_name, $application_url ) {
					$message = '{@Admins}' . "\nRecruit " . $display_name . " has entered the selection phase.\n\nPlease leave feedback here: " . $application_url;
					tcb_roster_admin_post_to_discord_channel( 'recruitment-managers', $message );
				}
			);
			break;

		case 'rejected':
			tcbp_public_notify_once(
				$post_id_,
				'_tcbp_notified_rejected',
				function () use ( $user_id ) {
					$applicant_message = "Thanks very much for your application. Unfortunately you were not successful at this time. We'd welcome a new application again in the future, if the situation changes.";
					tcbp_public_notify_user_by_preference( $user_id, $applicant_message, '3CB Application - Outcome', $applicant_message );
				}
			);
			break;

		case 'archived':
			// tcbp_public_sr_create_if_missing()/tcbp_public_sr_promote_to_marine() (both
			// service-record.php) do all the work - creating a service record if the applicant
			// doesn't have one yet (rank/training only applied on actual creation - see that
			// function's own doc comment), syncing WP role/tcb-rank, and firing the Marine
			// congratulations message exactly once (idempotent, so this is also safe to run
			// again if this application is later re-saved while still Archived).
			$service_record_id = tcbp_public_sr_create_if_missing( $user_id, TCBP_RANK_MARINE, array( 'basic-1', 'basic-2' ) );
			if ( ! $service_record_id ) {
				break;
			}
			tcbp_public_sr_promote_to_marine( $user_id, $service_record_id );
			break;

		default:
			break;
	}
}
