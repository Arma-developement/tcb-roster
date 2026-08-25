<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: commendation-awards.php
 * Description: Front-end bulk commendation awards tool. Lets an officer/SNCO/admin review and
 * commit Leadership, Mention in Despatches, and Mission Creation commendations for everyone
 * involved in a single event in one pass, instead of editing each recipient's service record
 * individually via tcbp_public_edit_sr_ribbons() (service-record.php). Leadership and Mission
 * Creation rows are pre-populated from the event's slotting table; Mention in Despatches starts
 * empty. Committing increments the relevant counters on each recipient's service record and
 * posts a single Discord announcement.
 */

/**
 * The roles allowed to use this tool - matches the existing gate on
 * tcbp_public_edit_sr_ribbons() (service-record.php), the manual per-user equivalent of this
 * page.
 *
 * @return array
 */
function tcbp_public_commendation_award_allowed_roles() {
	return array( 'commendation_admin', 'snco', 'officer', 'administrator' );
}

/**
 * Builds the "Award Commendations" admin button for the mission page, if the current user is
 * allowed to use it - null otherwise, so the caller can skip adding it.
 *
 * @param int    $post_id      The event post ID.
 * @param object $current_user The current user object.
 * @return array|null array( 'href' => ..., 'label' => ... ), or null.
 */
function tcbp_public_commendation_award_button( $post_id, $current_user ) {
	if ( ! array_intersect( tcbp_public_commendation_award_allowed_roles(), $current_user->roles ) ) {
		return null;
	}
	return array(
		'href'  => '/award-commendations/?id=' . $post_id,
		'label' => 'Award Commendations',
	);
}

/**
 * Flattens the event's slots repeater (slots > unit > slot) into a plain list of occupied
 * slots. Uses get_field() rather than have_rows()/the_row() deliberately - the latter shares a
 * single global position pointer per field+post, which is fragile across nested/repeated calls
 * (see mission-admin.php's signup_early() for a concrete case where that caused a real bug).
 * This function is called twice per page load (once each for the leadership and mission
 * creation prefill below), so it avoids that risk entirely rather than relying on each loop
 * running to completion.
 *
 * @param int $post_id The event post ID.
 * @return array List of array( 'slot_name' => string, 'user_id' => int ), one per occupied slot.
 */
function tcbp_public_commendation_award_get_occupied_slots( $post_id ) {
	$slots = get_field( 'slots', $post_id );
	if ( ! $slots ) {
		return array();
	}

	$occupied = array();
	foreach ( $slots as $slots_row ) {
		if ( empty( $slots_row['unit'] ) ) {
			continue;
		}
		foreach ( $slots_row['unit'] as $unit_row ) {
			if ( empty( $unit_row['slot'] ) ) {
				continue;
			}
			foreach ( $unit_row['slot'] as $slot_row ) {
				if ( empty( $slot_row['slot_member'] ) ) {
					continue;
				}
				$occupied[] = array(
					'slot_name' => (string) $slot_row['slot_name'],
					'user_id'   => (int) $slot_row['slot_member'],
				);
			}
		}
	}
	return $occupied;
}

/**
 * Guesses a leadership commendation slug from a slot's name, via an ordered, case-insensitive
 * keyword match - first match wins. More specific patterns are listed before shorter ones they'd
 * otherwise also match (e.g. "fire team leader" before the bare "team" catch-all).
 *
 * @param string $slot_name The slot's name.
 * @return string The guessed slug, or '' if nothing matched.
 */
function tcbp_public_commendation_award_guess_leadership_type( $slot_name ) {
	$slot_name_lower = strtolower( $slot_name );

	$patterns = array(
		'troop commander'  => 'troop',
		'troop 2ic'        => 'troop',
		'section leader'   => 'section',
		'fire team leader' => 'fireteam',
		'fireteam leader'  => 'fireteam',
		'team leader'      => 'team',
		'team'             => 'team',
	);

	foreach ( $patterns as $needle => $slug ) {
		if ( false !== strpos( $slot_name_lower, $needle ) ) {
			return $slug;
		}
	}
	return '';
}

/**
 * Builds the Leadership prefill: for every occupied slot, guesses a leadership type from its
 * name (tcbp_public_commendation_award_guess_leadership_type()) and adds that slot's user to the
 * candidate list for that type - but only if the guessed slug is actually a real leadership
 * commendation on this site right now, so a guess like "team" quietly drops instead of erroring
 * if that's not (yet) a real taxonomy child/ACF sub-field. The mission author is skipped
 * entirely here, even if they're also slotted as a leader - they're prefilled as Mission
 * Creation only, never double-counted as a leader too.
 *
 * @param int $post_id   The event post ID.
 * @param int $author_id The event post's author (get_post_field( 'post_author', $post_id )).
 * @return array slug => array of user IDs.
 */
function tcbp_public_commendation_award_prefill_leadership( $post_id, $author_id ) {
	$valid_slugs = wp_list_pluck( tcbp_public_commendation_group_terms( 'leadership_commendations' ), 'slug' );

	$prefill = array();
	foreach ( tcbp_public_commendation_award_get_occupied_slots( $post_id ) as $slot ) {
		if ( $slot['user_id'] === $author_id ) {
			continue;
		}
		$slug = tcbp_public_commendation_award_guess_leadership_type( $slot['slot_name'] );
		if ( ! $slug || ! in_array( $slug, $valid_slugs, true ) ) {
			continue;
		}
		if ( ! isset( $prefill[ $slug ] ) ) {
			$prefill[ $slug ] = array();
		}
		if ( ! in_array( $slot['user_id'], $prefill[ $slug ], true ) ) {
			$prefill[ $slug ][] = $slot['user_id'];
		}
	}
	return $prefill;
}

/**
 * Builds the Mission Creation prefill. Mission Creation commendation types are matched by
 * keyword in their own slug/name (not a fixed list, so a taxonomy rename/reorder doesn't break
 * this): a type mentioning "author" is prefilled with just the event's post author; a type
 * mentioning "zeus" is prefilled with anyone slotted into a "zeus"-named slot, excluding the
 * author - the author is only ever a mission author, never also counted as zeus, even if they
 * Zeused their own mission. Any other Mission Creation type (no known keyword match) is left
 * empty rather than guessed at.
 *
 * @param int $post_id   The event post ID.
 * @param int $author_id The event post's author (get_post_field( 'post_author', $post_id )).
 * @return array slug => array of user IDs.
 */
function tcbp_public_commendation_award_prefill_mission_creation( $post_id, $author_id ) {
	$terms = tcbp_public_commendation_group_terms( 'mission_creation' );
	if ( ! $terms ) {
		return array();
	}

	$zeus_user_ids = array();
	foreach ( tcbp_public_commendation_award_get_occupied_slots( $post_id ) as $slot ) {
		if ( false === strpos( strtolower( $slot['slot_name'] ), 'zeus' ) ) {
			continue;
		}
		if ( $slot['user_id'] === $author_id ) {
			continue;
		}
		if ( ! in_array( $slot['user_id'], $zeus_user_ids, true ) ) {
			$zeus_user_ids[] = $slot['user_id'];
		}
	}

	$prefill = array();
	foreach ( $terms as $term ) {
		$needle = strtolower( $term->slug . ' ' . $term->name );
		if ( false !== strpos( $needle, 'author' ) ) {
			$prefill[ $term->slug ] = $author_id ? array( $author_id ) : array();
		} elseif ( false !== strpos( $needle, 'zeus' ) ) {
			$prefill[ $term->slug ] = $zeus_user_ids;
		}
	}
	return $prefill;
}

/**
 * Every eligible award recipient - anyone with a service record - as {user_id, display_name}
 * entries sorted alphabetically, the same pool the commendations archive draws from
 * (commendations.php).
 *
 * @return array
 */
function tcbp_public_commendation_award_all_players() {
	$post_ids = get_posts(
		array(
			'post_type'      => 'service-record',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$user_ids = array();
	foreach ( $post_ids as $post_id ) {
		$user_id = get_field( 'user_id', $post_id );
		if ( $user_id ) {
			$user_ids[] = $user_id;
		}
	}

	return tcbp_public_sort_user_ids_by_display_name( $user_ids );
}

add_shortcode( 'tcbp_public_award_commendations', 'tcbp_public_award_commendations' );

/**
 * Renders the bulk commendation awards page for a single event.
 */
function tcbp_public_award_commendations() {

	if ( ! array_intersect( tcbp_public_commendation_award_allowed_roles(), wp_get_current_user()->roles ) ) {
		return;
	}

	if ( ! isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$post_id = (int) sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || 'tribe_events' !== get_post_type( $post_id ) ) {
		return;
	}

	// Field name (ACF group on the service record) => taxonomy parent term slug + manual
	// child display order. "leadership" is the odd one out - the ACF field is just
	// "leadership", but its taxonomy group parent is "leadership_commendations".
	$groups = array(
		'leadership'            => array(
			'label' => 'Leadership Commendations',
			'group' => 'leadership_commendations',
			'order' => array( 'troop', 'section', 'fireteam', 'patrol', 'asset' ),
		),
		'mention_in_despatches' => array(
			'label' => 'Mention in Despatches',
			'group' => 'mention_in_despatches',
			'order' => array(),
		),
		'mission_creation'      => array(
			'label' => 'Mission Creation',
			'group' => 'mission_creation',
			'order' => array(),
		),
	);

	$author_id = (int) get_post_field( 'post_author', $post_id );

	$prefill = array(
		'leadership'            => tcbp_public_commendation_award_prefill_leadership( $post_id, $author_id ),
		'mention_in_despatches' => array(),
		'mission_creation'      => tcbp_public_commendation_award_prefill_mission_creation( $post_id, $author_id ),
	);

	ob_start();

	echo '<h2>Award Commendations - ' . esc_html( get_the_title( $post_id ) ) . '</h2>';

	echo '<div class="tcb_commendation_awards" id="tcbCommendationAwards">';

	$path = plugins_url() . '/tcb-roster/images/ribbons/';

	foreach ( $groups as $field_name => $group_info ) {
		$terms = tcbp_public_commendation_group_terms( $group_info['group'], $group_info['order'] );
		if ( ! $terms ) {
			continue;
		}

		echo '<div class="tcb_award_group"><h3>' . esc_html( $group_info['label'] ) . '</h3>';

		foreach ( $terms as $term ) {
			$slug            = $term->slug;
			$image_url       = $path . $slug . '-1.png';
			$prefill_ids     = isset( $prefill[ $field_name ][ $slug ] ) ? $prefill[ $field_name ][ $slug ] : array();
			$prefill_entries = tcbp_public_sort_user_ids_by_display_name( $prefill_ids );

			echo '<div class="tcb_award_row" data-group="' . esc_attr( $field_name ) . '" data-slug="' . esc_attr( $slug ) . '">';
			// A plain <img>, not tcbp_public_commendation_image() (commendations.php) - that
			// wraps the image in a hover tooltip repeating the title/description, which is
			// redundant here since both are already shown as plain text next to the image.
			echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $term->name ) . '" width="175" height="47">';
			echo '<div class="tcb_award_row_text"><strong>' . esc_html( $term->name ) . '</strong>';
			if ( $term->description ) {
				echo '<p>' . esc_html( $term->description ) . '</p>';
			}
			echo '</div>';

			echo '<div class="tcb_award_row_players">';
			echo '<ul class="tcb_award_chip_list">';
			foreach ( $prefill_entries as $entry ) {
				echo '<li data-user-id="' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . ' <button type="button" class="tcb_award_chip_remove">&times;</button></li>';
			}
			echo '</ul>';
			echo '<input type="text" class="tcb_award_add_player" placeholder="Add player&hellip;">';
			echo '<ul class="tcb_award_add_player_results"></ul>';
			echo '</div>';

			echo '</div>';
		}

		echo '</div>';
	}

	echo '<button type="button" id="tcbCommendationAwardCommit" class="button button-primary">Commit Awards</button>';
	echo '<div id="tcbCommendationAwardResult"></div>';

	echo '</div>';

	// Registered (not auto-enqueued) with in_footer=true in class-tcb-roster-public.php's
	// enqueue_scripts() specifically so it prints after this shortcode's own output below - a
	// head-printed script (like the rsvp/slotting scripts) would already be output by the time
	// this shortcode runs, too late for this inline data to reach it.
	wp_enqueue_script( 'tcb_roster_public_commendation_award_register' );

	$inline_data = array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'tcbp_commendation_award_nonce' ),
		'postId'       => $post_id,
		'missionTitle' => get_the_title( $post_id ),
		'players'      => tcbp_public_commendation_award_all_players(),
	);
	echo '<script>var tcbpCommendationAwardData = ' . wp_json_encode( $inline_data ) . ';</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	return ob_get_clean();
}

add_action( 'wp_ajax_tcbp_public_commendation_award_commit', 'tcbp_public_commendation_award_commit' );

/**
 * AJAX handler: commits a full set of commendation awards for one event - increments the
 * relevant counter on every recipient's service record, then posts one Discord announcement
 * summarising who got what.
 */
function tcbp_public_commendation_award_commit() {

	/**
	 * Does the actual work, so any return exits cleanly into wp_die() below rather than the
	 * whole request lifecycle.
	 */
	function do_work() {

		if ( ! array_intersect( tcbp_public_commendation_award_allowed_roles(), wp_get_current_user()->roles ) ) {
			return wp_send_json_error( 'Not authorized' );
		}

		if ( ! isset( $_POST['postId'], $_POST['awards'], $_POST['nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return wp_send_json_error( 'Parameters missing' );
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! wp_verify_nonce( $nonce, 'tcbp_commendation_award_nonce' ) ) {
			return wp_send_json_error( 'Nonce failed' );
		}

		$post_id = (int) sanitize_text_field( wp_unslash( $_POST['postId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$awards = json_decode( wp_unslash( $_POST['awards'] ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_array( $awards ) ) {
			return wp_send_json_error( 'Invalid awards payload' );
		}

		$valid_groups = array( 'leadership', 'mention_in_despatches', 'mission_creation' );
		$summary      = array(); // user_id => array of commendation titles.

		foreach ( $awards as $group => $slugs ) {
			if ( ! in_array( $group, $valid_groups, true ) || ! is_array( $slugs ) ) {
				continue;
			}

			foreach ( $slugs as $slug => $user_ids ) {
				if ( ! is_array( $user_ids ) ) {
					continue;
				}

				$term = get_term_by( 'slug', sanitize_key( $slug ), 'tcb-commendation' );
				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}

				foreach ( $user_ids as $user_id ) {
					$user_id = (int) $user_id;
					if ( ! $user_id ) {
						continue;
					}

					$sr_post_id = get_field( 'service_record', 'user_' . $user_id );
					if ( ! $sr_post_id ) {
						continue;
					}

					$group_data                = get_field( $group, $sr_post_id );
					$group_data                = is_array( $group_data ) ? $group_data : array();
					$group_data[ $term->slug ] = ( isset( $group_data[ $term->slug ] ) ? (int) $group_data[ $term->slug ] : 0 ) + 1;
					update_field( $group, $group_data, $sr_post_id );

					if ( ! isset( $summary[ $user_id ] ) ) {
						$summary[ $user_id ] = array();
					}
					$summary[ $user_id ][] = $term->name;
				}
			}
		}

		if ( ! $summary ) {
			return wp_send_json_error( 'No awards to commit' );
		}

		tcbp_public_commendation_announce_discord( $summary, get_the_title( $post_id ) );

		return wp_send_json_success( 'Awards committed' );
	}

	do_work();
	wp_die();
}

/**
 * Posts a single Discord announcement summarising a batch of commendation awards. Kept separate
 * from the update logic above so a future per-player DM path (tcb_roster_admin_post_to_discord_dm()
 * already exists in tcb-roster-admin-post-to-discord.php) can be added alongside it later without
 * touching how the awards themselves get applied.
 *
 * @param array  $summary       user_id => array of commendation title strings.
 * @param string $mission_title The event's title, for the announcement header.
 */
function tcbp_public_commendation_announce_discord( $summary, $mission_title ) {

	$lines = array( '🎖️ Commendations awarded for "' . $mission_title . '"', '' );

	foreach ( $summary as $user_id => $titles ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			continue;
		}
		$lines[] = $user->display_name . ' — ' . implode( ', ', $titles );
	}

	// '494511486715297794' is the test channel for announcements during development - see
	// tcb-roster-admin-post-to-discord.php's default case, which treats any numeric channel ID
	// as a raw Discord channel ID rather than needing a named case added for it.
	tcb_roster_admin_post_to_discord_channel( '494511486715297794', implode( "\n", $lines ) );
}
