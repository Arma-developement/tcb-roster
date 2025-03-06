<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

add_action( 'tribe_events_single_event_after_the_meta', 'tcbp_public_mission_overview' );

/**
 * Display the mission overview for the events page
 */
function tcbp_public_mission_overview() {

	// Ensure user is logged in.
	$current_user = wp_get_current_user();
	if ( ! $current_user ) {
		return;
	}
	$current_user_id = $current_user->ID;

	// Early out if no post.
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	// Output the briefing.
	echo '<div class="tcb_briefing" >';

	echo '<h2>Mission Details</h2>';

	echo '<div class="container briefing-meta"><div class="one-third column"><h3>Author</h3>';
	$author_id = get_the_author_meta( 'ID' );
	echo '<a href="/service-record/service-record-' . esc_attr( $author_id ) . '">' . esc_html( get_the_author_meta( 'display_name' ) ) . '</a>';
	echo '</div>';

	echo '<div class="one-third column"><h3>Map</h3>';
	echo get_field( 'brief_map' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';

	echo '<div class="one-third column"><h3>Modset</h3>';
	echo get_field( 'brief_modset' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div></div>';

	echo '<h3>Situation</h3>';
	echo get_field( 'brief_situation' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Mission</h3>';
	echo get_field( 'brief_mission' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// Early out for subscribers on private missions.
	$current_user_roles = $current_user->roles;
	if ( ( in_array( 'subscriber', $current_user_roles, true ) ) && ( get_field( 'brief_mission_type' ) === 'private' ) ) {
		echo '<br><br><p>This is a 3CB members only mission</p>';
		echo '<p>For information about 3CB, click <a href="/information-centre/about-3cb">here</a></p>';
		echo '<p>Interested in joining 3CB, click <a href="/information-centre/the-recruitment-process">here</a></p>';
		echo '</div>';
		return;
	}

	// Password protection for subscribers on joint-op missions.
	if ( ( in_array( 'subscriber', $current_user_roles, true ) ) && ( get_field( 'brief_mission_type' ) === 'jo' ) ) {
		if ( get_field( 'slotting_password' ) !== $current_user->slotting_password ) {

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
			echo '<p>For information about 3CB, click <a href="/information-centre/about-3cb">here</a></p>';
			echo '<p>Interested in joining 3CB, click <a href="/information-centre/the-recruitment-process">here</a></p>';
			echo '</div>';
			return;
		}
	}

	echo '<h3>Execution</h3>';
	echo get_field( 'brief_execution' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<h3>Intel</h3>';
	echo get_field( 'brief_intel' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	echo '<div class="container briefing-meta"><div class="one-third column"><h3>Time</h3>';
	echo get_field( 'brief_start_time' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';

	echo '<div class="one-third column"><h3>Enemy Forces</h3>';
	echo get_field( 'brief_enemy_forces' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';

	echo '<div class="one-third column"><h3>Friendly Forces</h3>';
	echo get_field( 'brief_friendly_forces' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div></div>';

	echo '</div>';

	// Early out if no entries in rsvp field.
	if ( ! have_rows( 'rsvp' ) ) {
		return;
	}

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	error_log( print_r( 'dynamicContent', true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
	// .

	echo '<div id="dynamicContent">';

	list( $attendance, $user_attending ) = tcbp_public_attendance_roster( $post_id, $current_user );
	if ( $user_attending ) {
		$user_slotted = tcbp_public_slotting_tool( $post_id, $current_user, $attendance );
	} else {
		$user_slotted = tcbp_public_slotting_tool_read_only( $post_id, $current_user, $attendance );
	}

	echo '<div class="slotToolButtons" id="slotToolButtons" >';

	$allowed_roles = array( 'mission_admin', 'snco', 'officer', 'administrator' );
	if ( array_intersect( $allowed_roles, $current_user_roles ) ) {
		echo '<a href="/mission-admin-panel/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission Admin Panel</a>';
		echo '<a href="/mission-news-panel/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission News Panel</a>';
	}

	if ( $user_slotted ) {
		echo '<a href="/mission-briefing/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission Briefing</a>';
	}

	echo '</div></div>';
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
		$i = get_row_index();

		echo '<div class="attendanceCol" id="rsvpRow-' . esc_attr( $i ) . '">';
		echo '<h4>' . esc_html( get_sub_field( 'label' ) ) . '</h4>';

		$unregister = false;
		$user_ids   = get_sub_field( 'user' );

		if ( $user_ids ) {
			// Check if user in list.
			if ( in_array( $current_user_id, $user_ids, true ) ) {
				$unregister = true;
			}

			// Display list.
			echo '<ul>';
			foreach ( $user_ids as $user_id ) {
				++$attendance;
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
		}

		echo '<form class="rsvpFormUnregister" id="rsvpFormUnregister-' . esc_attr( $i ) . '">';
		echo '<input type="hidden" name="postId" class="rsvpPostID" value="' . esc_attr( $post_id ) . '">';
		echo '<input type="hidden" name="userId" class="rsvpUserID" value="' . esc_attr( $current_user_id ) . '">';
		echo '<input type="hidden" name="selection" class="rsvpSelection" value="' . esc_attr( $i ) . '">';
		echo '<input type="hidden" name="registered" class="rsvpUnregister" value="' . esc_attr( $unregister ) . '">';

		if ( $unregister ) {
			echo '<input type="submit" value="Unregister"></form>';
		} else {
			echo '<input type="submit" value="Register"></form>';
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

				error_log( print_r( 'k: ' . $k . ' is_owner: ' . ( $is_owner ? 'true' : 'false' ) . ' is_disabled: ' . ( $is_disabled ? 'true' : 'false' ), true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r

				echo '<div class=' . ( $is_owner ? '"slotToolSlot slotIconCanDelete"' : '"slotToolSlot"' ) . ' id="slotToolSlot-' . esc_attr( $j ) . '-' . esc_attr( $k ) . '">';

				if ( $is_disabled ) {
					echo '<div class="slotToolSlotDummyImage" style="background-image:url(' . esc_url( $profile_image ) . ')"></div>';
				} else {
					echo '<form class="slotForm">';
					echo '<input type="hidden" name="postId" class="postID" value="' . esc_attr( $post_id ) . '">';
					echo '<input type="hidden" name="userId" class="userID" value="' . esc_attr( $current_user_id ) . '">';
					echo '<input type="hidden" name="slot" class="slot" value="' . esc_attr( $i ) . ',' . esc_attr( $j ) . ',' . esc_attr( $k ) . ',' . esc_attr( $is_owner ) . '">';
					echo '<input class="slotIcon" type="submit" style="background-image:url(' . esc_url( $profile_image ) . ')">';
					echo '</form>';
				}

				if ( $is_locked ) {
					if ( $attendance_threshold < 999 ) {
						echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  locked (requires ' . esc_html( $attendance_threshold ) . ' attendees)<br>';
					} else {
						echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . "</strong>  -  locked (command's decision)<br>";
					}
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
	echo '</div></div>';

	error_log( print_r( 'user_found: ' . $user_found, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r

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
					if ( $attendance_threshold < 999 ) {
						echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  locked (requires ' . esc_html( $attendance_threshold ) . ' attendees)<br>';
					} else {
						echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . "</strong>  -  locked (command's decision)<br>";
					}
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
	echo '</div></div>';

	return false;
}
