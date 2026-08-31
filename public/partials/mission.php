<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Determines whether a subscriber is blocked from a mission based on its visibility type.
 * Private, private-action, mini-op and patrol-op missions are members only - subscribers can't
 * view, attend, or slot into them, and neither can they view training events (identified by the
 * "training" event_category term, rather than brief_mission_type). Joint-op missions
 * additionally require the subscriber to hold the mission's own per-user slotting password. Used
 * both to gate the mission page display and independently re-checked by the RSVP/slotting AJAX
 * endpoints, since the display check alone isn't an access control - a direct POST to those
 * endpoints would otherwise bypass it entirely.
 *
 * @param int   $post_id    The mission post ID.
 * @param array $user_roles The current user's roles, e.g. wp_get_current_user()->roles.
 * @return bool True if this user is blocked from this mission.
 */
function tcbp_public_mission_is_restricted_for_user( $post_id, $user_roles ) {
	if ( ! in_array( 'subscriber', $user_roles, true ) ) {
		return false;
	}

	if ( has_term( 'training', 'event_category', $post_id ) ) {
		return true;
	}

	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id );
	$brief_mission_type       = $brief_mission_type_array ? $brief_mission_type_array['value'] : '';

	if ( in_array( $brief_mission_type, array( 'private', 'privateaction', 'miniop', 'patrolop' ), true ) ) {
		return true;
	}

	if ( 'jo' === $brief_mission_type ) {
		return get_field( 'slotting_password', $post_id ) !== wp_get_current_user()->slotting_password;
	}

	return false;
}

add_action( 'tribe_events_single_event_after_the_meta', 'tcbp_public_mission_overview' );

/**
 * Display the mission overview for the events page - branches into either the standard mission
 * layout, or a shorter dedicated layout for "training" event_category events.
 */
function tcbp_public_mission_overview() {

	// Ensure user is logged in.
	$current_user = wp_get_current_user();
	if ( ! $current_user ) {
		return;
	}

	// Early out if no post.
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	if ( has_term( 'training', 'event_category', $post_id ) ) {
		tcbp_public_training_overview( $post_id, $current_user );
	} else {
		tcbp_public_standard_mission_overview( $post_id, $current_user );
	}
}

/**
 * Outputs the "Author / Modset / Map" header block shared by both the standard mission and
 * training overviews.
 *
 * @param string $heading The section heading to show above it, e.g. "Mission Details".
 */
function tcbp_public_mission_overview_header( $heading ) {
	echo '<h2>' . esc_html( $heading ) . '</h2>';

	echo '<div class="container briefing-meta">';
	echo '<div class="one-third column"><h3>Author</h3>';
	$author_id = get_the_author_meta( 'ID' );
	echo '<a href="/service-record/service-record-' . esc_attr( $author_id ) . '">' . esc_html( get_the_author_meta( 'display_name' ) ) . '</a>';
	echo '</div>';
	echo '<div class="one-third column"><h3>Modset</h3>';
	$modset = get_field( 'brief_modset' );
	if ( is_array( $modset ) && ! empty( $modset ) ) {
		foreach ( $modset as $mod ) {
			echo esc_html( $mod ) . '<br>';
		}
	} else {
		echo 'TBA';
	}
	echo '</div>';
	echo '<div class="one-third column"><h3>Map</h3>';
	echo get_field( 'brief_map' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';
	echo '</div>';
}

/**
 * Outputs the attendance/slotting tool and action buttons shared by both the standard mission
 * and training overviews.
 *
 * @param int    $post_id               The event post ID.
 * @param object $current_user          The current user object.
 * @param array  $admin_buttons         Ordered list of admin-only buttons to show, each
 *                                      array( 'href' => ..., 'label' => ... ) - only shown to
 *                                      mission_admin/commendation_admin/snco/officer/administrator.
 * @param bool   $show_briefing_button  Whether to show the "Mission Briefing" button for a
 *                                      slotted user.
 */
function tcbp_public_mission_overview_dynamic_content( $post_id, $current_user, $admin_buttons, $show_briefing_button ) {

	// Early out if no entries in rsvp field.
	if ( ! have_rows( 'rsvp' ) ) {
		return;
	}

	echo '<div id="dynamicContent">';

	list( $attendance, $user_attending ) = tcbp_public_attendance_roster( $post_id, $current_user );
	if ( $user_attending ) {
		$user_slotted = tcbp_public_slotting_tool( $post_id, $current_user, $attendance );
	} else {
		$user_slotted = tcbp_public_slotting_tool_read_only( $post_id, $current_user, $attendance );
	}

	echo '<div class="slotToolButtons" id="slotToolButtons" >';

	// commendation_admin included alongside the other admin/duty roles so a user whose only
	// relevant role is commendation_admin still sees $admin_buttons - it may contain the Award
	// Commendations button (tcbp_public_commendation_award_button(), commendation-awards.php),
	// which is gated to exactly that broader set including commendation_admin.
	$allowed_roles = array( 'mission_admin', 'commendation_admin', 'snco', 'officer', 'administrator' );
	if ( array_intersect( $allowed_roles, $current_user->roles ) ) {
		foreach ( $admin_buttons as $button ) {
			echo '<a href="' . esc_url( $button['href'] ) . '" class="button button-secondary">' . esc_html( $button['label'] ) . '</a>';
		}
	}

	if ( $show_briefing_button && $user_slotted ) {
		echo '<a href="/mission-briefing/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission Briefing</a>';
	}

	echo '</div></div>';
}

/**
 * The standard mission overview layout - Situation/Mission preview, the subscriber access
 * gate, then (unless this is an "actions" event_category mission) the full Execution/Intel/
 * Enemy Forces/Friendly Forces/Section Composition briefing, followed by attendance/slotting.
 *
 * @param int    $post_id      The event post ID.
 * @param object $current_user The current user object.
 */
function tcbp_public_standard_mission_overview( $post_id, $current_user ) {

	$current_user_id = $current_user->ID;

	echo '<div class="tcb_briefing" >';

	tcbp_public_mission_overview_header( 'Mission Details' );

	echo '<h3>Situation</h3>';
	echo get_field( 'brief_situation' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Mission</h3>';
	echo get_field( 'brief_mission' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// Early out for subscribers blocked from this mission - private/miniop/patrolop missions
	// entirely, or a joint-op mission without the correct per-user slotting password.
	$current_user_roles       = $current_user->roles;
	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id );
	$brief_mission_type       = $brief_mission_type_array ? $brief_mission_type_array['value'] : '';
	if ( tcbp_public_mission_is_restricted_for_user( $post_id, $current_user_roles ) ) {
		if ( 'jo' === $brief_mission_type ) {
			echo '<div class="tcb_submit_slotting_password">';

			acfe_form(
				array(
					'post_id'         => 'user_' . $current_user_id,
					'name'            => 'submit-slotting-password',
					'submit_value'    => 'Submit',
					'return'          => add_query_arg( 'updated', 'true', get_permalink() ),
					'updated_message' => false,
				)
			);

			echo '</div>';

			echo '<br><br><p>This is a joint operations, open to 3CB guests only</p>';
		} else {
			echo '<p class="info">This is a 3CB members only mission</p>';
		}
		echo '<p>For information about 3CB, click <a href="/information-centre/about-3cb">here</a></p>';
		echo '<p>Interested in joining 3CB, click <a href="/information-centre/the-recruitment-process">here</a></p>';
		echo '</div>';
		return;
	}

	// The "actions" event_category is a trimmed-down version of this same briefing - same
	// access control as above, just without these five sections. No category (or any category
	// other than actions, e.g. "default") shows them as before.
	if ( ! has_term( 'actions', 'event_category', $post_id ) ) {

		$brief_friendly_forces_conops = get_field( 'brief_friendly_forces_conops', $post_id_ );

		if ( $brief_friendly_forces_conops ) {
			echo '<h3>Higher Commander\'s Intent</h3>';
			echo get_field( 'brief_execution', $post_id_ ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<h3>CONOPS</h3>';
			echo $brief_friendly_forces_conops; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<h3>Execution</h3>';
			echo get_field( 'brief_execution' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<h3>Intel</h3>';
		echo get_field( 'brief_intel' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<div class="container briefing-meta"><div class="one-half column"><h3>Enemy Forces</h3>';
		echo get_field( 'brief_enemy_forces' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';

		echo '<div class="one-half column"><h3>Friendly Forces</h3>';
		echo get_field( 'brief_friendly_forces' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		echo '</div>';

		echo '<h3>Section Composition</h3>';
		echo get_field( 'brief_section_composition' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</div>';

	$admin_buttons = array(
		array(
			'href'  => '/mission-admin-panel/?id=' . $post_id,
			'label' => 'Mission Admin Panel',
		),
		array(
			'href'  => '/mission-news-panel/?id=' . $post_id,
			'label' => 'Mission News Panel',
		),
	);
	$commendation_award_button = tcbp_public_commendation_award_button( $post_id, $current_user );
	if ( $commendation_award_button ) {
		$admin_buttons[] = $commendation_award_button;
	}

	tcbp_public_mission_overview_dynamic_content(
		$post_id,
		$current_user,
		$admin_buttons,
		true
	);
}

/**
 * The "training" event_category overview layout - a trimmed-down version of the standard
 * mission overview: Author/Modset/Map, then "Aim" (brief_mission) and "Description"
 * (brief_execution) in place of Situation/Mission/Execution/Intel/etc., the same subscriber
 * access gate, then attendance/slotting with only a single (renamed) admin button.
 *
 * @param int    $post_id      The event post ID.
 * @param object $current_user The current user object.
 */
function tcbp_public_training_overview( $post_id, $current_user ) {

	$current_user_id = $current_user->ID;

	echo '<div class="tcb_briefing" >';

	tcbp_public_mission_overview_header( 'Training Details' );

	echo '<h3>Aim</h3>';
	echo get_field( 'brief_mission' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// Same access gate as the standard mission overview - see
	// tcbp_public_standard_mission_overview() for the full rationale.
	$current_user_roles       = $current_user->roles;
	$brief_mission_type_array = get_field( 'brief_mission_type', $post_id );
	$brief_mission_type       = $brief_mission_type_array ? $brief_mission_type_array['value'] : '';
	if ( tcbp_public_mission_is_restricted_for_user( $post_id, $current_user_roles ) ) {
		if ( 'jo' === $brief_mission_type ) {
			echo '<div class="tcb_submit_slotting_password">';

			acfe_form(
				array(
					'post_id'         => 'user_' . $current_user_id,
					'name'            => 'submit-slotting-password',
					'submit_value'    => 'Submit',
					'return'          => add_query_arg( 'updated', 'true', get_permalink() ),
					'updated_message' => false,
				)
			);

			echo '</div>';

			echo '<br><br><p>This is a joint operations, open to 3CB guests only</p>';
		} else {
			echo '<p class="info">This is a 3CB members only mission</p>';
		}
		echo '<p>For information about 3CB, click <a href="/information-centre/about-3cb">here</a></p>';
		echo '<p>Interested in joining 3CB, click <a href="/information-centre/the-recruitment-process">here</a></p>';
		echo '</div>';
		return;
	}

	echo '<h3>Description</h3>';
	echo get_field( 'brief_execution' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '</div>';

	$admin_buttons = array(
		array(
			'href'  => '/mission-admin-panel/?id=' . $post_id,
			'label' => 'Admin panel',
		),
	);
	$commendation_award_button = tcbp_public_commendation_award_button( $post_id, $current_user );
	if ( $commendation_award_button ) {
		$admin_buttons[] = $commendation_award_button;
	}

	tcbp_public_mission_overview_dynamic_content(
		$post_id,
		$current_user,
		$admin_buttons,
		false
	);
}


/**
 * Counts every user signed up as "Attending" for a mission - just the first rsvp repeater row
 * (see tcbp_public_mission_briefing_submission_callback(), which adds Attending/Maybe/Not
 * Attending in that order), not Maybe/Not Attending as well. Used by the slotting AJAX handler
 * to independently re-verify a slot's attendance_threshold lock server-side.
 *
 * Uses get_field() rather than have_rows()/the_row() deliberately - the latter shares a single
 * global position pointer per field+post, which is fragile across multiple/nested calls (see
 * mission-admin.php's signup_early() for a concrete case where that caused a real bug).
 *
 * @param int $post_id The mission post ID.
 * @return int The number of users signed up as attending.
 */
function tcbp_public_get_attendance_count( $post_id ) {
	$rows = get_field( 'rsvp', $post_id );
	if ( ! $rows || empty( $rows[0]['user'] ) ) {
		return 0;
	}
	return count( $rows[0]['user'] );
}

/**
 * Generates the public attendance roster.
 *
 * This function is responsible for generating and displaying the public attendance roster
 * for the TCB Roster plugin.
 *
 * @param int    $post_id The post ID.
 * @param object $current_user The current user object.
 * @return array $attendance The number of users registered as attending, $user_found Whether the current user is registered as attending.
 */
function tcbp_public_attendance_roster( $post_id, $current_user ) {

	$current_user_id = $current_user->ID;

	echo '<div id="attendanceRoster"><div class="inner">';
	echo '<h2>Attendance</h2>';
	echo '<div class="wrap">';

	$attendance = 0;
	$user_found = false;
	while ( have_rows( 'rsvp' ) ) :
		the_row();
		$i          = get_row_index();
		$user_ids   = get_sub_field( 'user' );
		$unregister = false;

		echo '<div class="attendanceCol" id="rsvpRow-' . esc_attr( $i ) . '">';

		if ( $user_ids ) {
			echo '<h5>' . esc_html( get_sub_field( 'label' ) ) . ' - ' . count( $user_ids ) . '</h5>';

			// Check if user in list.
			if ( in_array( $current_user_id, $user_ids, true ) ) {
				$unregister = true;
			}

			// Display list.
			echo '<ul>';
			foreach ( $user_ids as $user_id ) {
				// Only the "Attending" row (index 1) counts toward a slot's attendance_threshold -
				// Maybe/Not Attending shouldn't count toward unlocking a slot that requires
				// genuine attendance. Matches tcbp_public_get_attendance_count().
				if ( 1 === $i ) {
					++$attendance;
				}
				$user   = get_user_by( 'id', $user_id );
				$avatar = get_avatar_url( $user_id );
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// $avatar = false; // Uncomment to disable avatars.
				if ( $avatar ) {
					echo '<li><img src="' . esc_url( $avatar ) . '" alt="author-avatar"><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $user->display_name ) . '</a></li>';
				} else {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $user->display_name ) . '</a></li>';
				}
			}
			echo '</ul>';
		} else {
			echo '<h5>' . esc_html( get_sub_field( 'label' ) ) . ' - 0</h5>';
		}

		echo '<form class="rsvpFormUnregister" id="rsvpFormUnregister-' . esc_attr( $i ) . '">';
		echo '<input type="hidden" name="postId" class="rsvpPostID" value="' . esc_attr( $post_id ) . '">';
		echo '<input type="hidden" name="selection" class="rsvpSelection" value="' . esc_attr( $i ) . '">';
		echo '<input type="hidden" name="registered" class="rsvpUnregister" value="' . esc_attr( $unregister ) . '">';

		if ( $unregister ) {
			echo '<input type="submit" value="Unregister"><span class="spinner unreg"></span></form>';
		} else {
			echo '<input type="submit" value="Register"><span class="spinner reg"></span></form>';
		}

		echo '</div>';

		// Check if user is in attending list.
		if ( 1 === $i ) {
			$user_found = $unregister;
		}
	endwhile;
	echo '</div></div></div>';

	return array( $attendance, $user_found );
}

/**
 * Builds the reason text shown for a locked slot, based on its attendance_threshold. Below 999
 * it's treated as a genuine minimum-attendance number; 999+ are sentinel values that convey a
 * fixed reason instead, with specific ones (1001-1004) spelling out exactly who the slot is
 * reserved for rather than falling back to the generic "Coy's decision".
 *
 * @param int $attendance_threshold The slot's configured attendance_threshold.
 * @return string The reason text to show in parentheses next to the slot name.
 */
function tcbp_public_slotting_get_locked_reason( $attendance_threshold ) {
	$reasons = array(
		1001 => 'Locked for Candidate',
		1002 => 'Locked for Recruit',
		1003 => 'Locked for Buddy',
		1004 => 'Locked for Mentor',
	);

	if ( isset( $reasons[ $attendance_threshold ] ) ) {
		return $reasons[ $attendance_threshold ];
	}

	if ( $attendance_threshold < 999 ) {
		return $attendance_threshold . ' attending';
	}

	return "Coy's decision";
}

/**
 * Generates the public slotting tool.
 *
 * This function is responsible for generating and displaying the public slotting tool
 * for the TCB Roster plugin.
 *
 * @param int    $post_id The post ID.
 * @param object $current_user The current user object.
 * @param int    $attendance The number of users registered as attending.
 * @return bool  $user_found Whether the current user is slotted.
 */
function tcbp_public_slotting_tool( $post_id, $current_user, $attendance ) {

	// Early out if no entries in slots field.
	if ( ! have_rows( 'slots' ) ) {
		return;
	}

	$user_found         = false;
	$current_user_id    = $current_user->ID;
	$current_user_login = $current_user->user_login;

	error_log( print_r( 'attendance: ' . $attendance, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r

	echo '<div class="slotTool" id="slotTool"><div class="inner">';
	echo '<h2>Priority placements</h2>';

	// Loop through slot rows.
	while ( have_rows( 'slots' ) ) :
		the_row();
		$i = get_row_index();

		// Continue to next slot if unit is empty.
		if ( ! have_rows( 'unit' ) ) {
			continue;
		}

		// Loop through unit rows.
		while ( have_rows( 'unit' ) ) :
			the_row();
			$j = get_row_index();

			echo '<div class="unit" >';
			echo '<h3>' . esc_html( get_sub_field( 'name' ) ) . '</h3>';

			// Continue to next unit if slot is empty.
			if ( ! have_rows( 'slot' ) ) {
				continue;
			}

			// Loop through rows.
			while ( have_rows( 'slot' ) ) :
				the_row();
				$k = get_row_index();

				// Get profile pic for slotted member.
				$attendance_threshold = get_sub_field( 'attendance_threshold' );
				$is_locked            = $attendance < $attendance_threshold;
				$slotted_user_id      = get_sub_field( 'slot_member' );
				$is_owner             = $slotted_user_id === $current_user_id;
				$is_disabled          = $is_locked || ( $slotted_user_id && ! $is_owner );
				$profile_image        = '';

				if ( $slotted_user_id ) {
					$slotted_user         = get_user_by( 'id', $slotted_user_id );
					$profile_image        = get_avatar_url( $slotted_user_id );
					$slotted_display_name = $slotted_user->display_name;
				}
				$user_found |= $is_owner;

				//error_log( print_r( 'k: ' . $k . ' is_owner: ' . ( $is_owner ? 'true' : 'false' ) . ' is_disabled: ' . ( $is_disabled ? 'true' : 'false' ), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r

				echo '<div class=' . ( $is_owner ? '"slotToolSlot slotIconCanDelete"' : '"slotToolSlot"' ) . ' id="slotToolSlot-' . esc_attr( $j ) . '-' . esc_attr( $k ) . '">';

				if ( $is_disabled ) {
					echo '<div class="slotToolSlotDummyImage" style="background-image:url(' . esc_url( $profile_image ) . ')"></div>';
				} else {
					echo '<form class="slotForm">';
					echo '<input type="hidden" name="postId" class="postID" value="' . esc_attr( $post_id ) . '">';
					echo '<input type="hidden" name="slot" class="slot" value="' . esc_attr( $i ) . ',' . esc_attr( $j ) . ',' . esc_attr( $k ) . '">';
					echo '<input class="slotIcon" type="submit" style="background-image:url(' . esc_url( $profile_image ) . ')">';
					echo '</form>';
				}

				if ( $is_locked ) {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  (' . esc_html( tcbp_public_slotting_get_locked_reason( $attendance_threshold ) ) . ')<br>';
				} elseif ( $slotted_user_id ) {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  <span class="slotMember"><a href="/service-record/service-record-' . esc_attr( $slotted_user_id ) . '">' . esc_attr( $slotted_display_name ) . '</a></span><br>';
				} else {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  <br>';
				}
				echo '</div>';
			endwhile;
			echo '</div>';
		endwhile;
	endwhile;
	echo '<p class="slotToolNote">Note: Placements are not guaranteed and are subject to change by the CO to meet mission requirements.</p>';
	echo '</div></div>';

	//error_log( print_r( 'user_found: ' . $user_found, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r

	return $user_found;
}

/**
 * Generates the public slotting tool (read only).
 *
 * This function is responsible for generating and displaying the public slotting tool
 * for the TCB Roster plugin.
 *
 * @param int    $post_id The post ID.
 * @param object $current_user The current user object.
 * @param int    $attendance The number of users registered as attending.
 * @return bool  $user_found Whether the current user is slotted.
 */
function tcbp_public_slotting_tool_read_only( $post_id, $current_user, $attendance ) {

	// Early out if no entries in slots field.
	if ( ! have_rows( 'slots' ) ) {
		return;
	}

	echo '<div class="slotTool" id="slotTool"><div class="inner">';
	echo '<h2>Priority placements</h2>';

	// Loop through slot rows.
	while ( have_rows( 'slots' ) ) :
		the_row();
		$i = get_row_index();

		// Continue to next slot if unit is empty.
		if ( ! have_rows( 'unit' ) ) {
			continue;
		}

		// Loop through unit rows.
		while ( have_rows( 'unit' ) ) :
			the_row();
			$j = get_row_index();

			echo '<div class="unit" >';
			echo '<h3>' . esc_html( get_sub_field( 'name' ) ) . '</h3>';

			// Continue to next unit if slot is empty.
			if ( ! have_rows( 'slot' ) ) {
				continue;
			}

			// Loop through rows.
			while ( have_rows( 'slot' ) ) :
				the_row();
				$k = get_row_index();

				// Get profile pic for slotted member.
				$attendance_threshold = get_sub_field( 'attendance_threshold' );
				$is_locked            = $attendance < $attendance_threshold;
				$slotted_user_id      = get_sub_field( 'slot_member' );

				$profile_image        = '';
				$slotted_display_name = '';

				if ( $slotted_user_id ) {
					$slotted_user         = get_user_by( 'id', $slotted_user_id );
					$profile_image        = get_avatar_url( $slotted_user_id );
					$slotted_display_name = $slotted_user->display_name;
				}

				echo '<div class="slotToolSlot" id="slotToolSlot-' . esc_attr( $j ) . '-' . esc_attr( $k ) . '">';
				echo '<div class="slotToolSlotDummyImage" style="background-image:url(' . esc_url( $profile_image ) . ')"></div>';
				if ( $is_locked ) {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  (' . esc_html( tcbp_public_slotting_get_locked_reason( $attendance_threshold ) ) . ')<br>';
				} elseif ( $slotted_user_id ) {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  <span class="slotMember"><a href="/service-record/service-record-' . esc_attr( $slotted_user_id ) . '">' . esc_attr( $slotted_display_name ) . '</a></span><br>';
				} else {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  <br>';
				}
				echo '</div>';
			endwhile;
			echo '</div>';
		endwhile;
	endwhile;
	echo '<p class="slotToolNote">Note: Placements are not guaranteed and are subject to change by the CO to meet mission requirements.</p>';
	echo '</div></div>';

	return false;
}
