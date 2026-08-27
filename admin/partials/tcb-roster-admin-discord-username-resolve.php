<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: tcb-roster-admin-discord-username-resolve.php
 * Description: When an administrator edits a user's Discord username directly via the
 * wp-admin user profile screen, re-runs the same username -> Discord ID lookup the front-end
 * profile form uses (tcbp_public_edit_profile_submit(), public/partials/user-profile.php), so
 * a backend edit can't leave discord_id stale/unresolved the way the original front-end-only
 * bug did. Also shows an admin notice if the lookup fails, mirroring the front-end fix, so a
 * mistyped username doesn't silently go unnoticed here either.
 */

add_action( 'acf/save_post', 'tcbp_admin_resolve_discord_id_on_backend_edit', 20 );

/**
 * @param mixed $post_id ACF's post_id for whatever was just saved - only "user_{id}" (a WP
 *                        user profile) is handled here; anything else (a numeric post ID,
 *                        options page, etc.) is ignored. Scoped to is_admin() so this doesn't
 *                        double up with the front-end profile form, which submits from a
 *                        public-facing page (is_admin() === false there) and already has its
 *                        own resolve-and-notify logic.
 */
function tcbp_admin_resolve_discord_id_on_backend_edit( $post_id ) {

	if ( ! is_admin() || 0 !== strpos( (string) $post_id, 'user_' ) ) {
		return;
	}

	$user_id = (int) substr( (string) $post_id, 5 );
	if ( ! $user_id ) {
		return;
	}

	$discord_username = get_field( 'discord_username', $post_id );
	if ( ! $discord_username ) {
		return;
	}

	$discord_id = tcb_roster_admin_query_discord_username( $discord_username );
	if ( $discord_id ) {
		update_field( 'discord_id', $discord_id, $post_id );
		return;
	}

	// Couldn't resolve it - flag this for an admin notice on the next page load (the redirect
	// wp-admin's own user-edit save flow already does), rather than leaving the field silently
	// unresolved the way the original front-end-only bug did.
	set_transient( 'tcbp_admin_discord_resolve_failed_' . $user_id, true, 60 );
}

add_action( 'admin_notices', 'tcbp_admin_discord_resolve_failed_notice' );

/**
 * Shows a one-time admin notice (via the short-lived transient set above) on the user-edit/
 * profile screen after a Discord username edit there couldn't be resolved to a Discord ID.
 */
function tcbp_admin_discord_resolve_failed_notice() {

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'user-edit', 'profile' ), true ) ) {
		return;
	}

	$user_id = isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : get_current_user_id(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $user_id ) {
		return;
	}

	$transient_key = 'tcbp_admin_discord_resolve_failed_' . $user_id;
	if ( ! get_transient( $transient_key ) ) {
		return;
	}
	delete_transient( $transient_key );

	echo '<div class="notice notice-error"><p>Could not resolve that Discord username to a Discord ID - please double check it and save again.</p></div>';
}
