<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Generates the email arguments for the public report form.
 */
function tcb_roster_public_attendance_roster() {

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
	echo get_the_author();
	echo '</div>';

	echo '<div class="one-third column"><h3>Map</h3>';
	echo esc_html( get_field( 'brief_map' ) );
	echo '</div>';

	echo '<div class="one-third column"><h3>Modset</h3>';
	echo esc_html( get_field( 'brief_modset' ) );
	echo '</div></div>';

	echo '<h3>Situation</h3>';
	echo esc_html( get_field( 'brief_situation' ) );

	echo '<h3>Mission</h3>';
	echo esc_html( get_field( 'brief_mission' ) );

	// Early out for subscribers on private missions.
	if ( ( in_array( 'subscriber', $current_user->roles, true ) ) && ( get_field( 'brief_mission_type' ) === 'private' ) ) {
		echo '<br><br><p>This is a 3CB members only mission</p>';
		echo '<p>For information about 3CB, click <a href="/information-centre/about-3cb">here</a></p>';
		echo '<p>Interested in joining 3CB, click <a href="/information-centre/the-recruitment-process">here</a></p>';
		echo '</div>';
		return;
	}

	// Password protection for subscribers on joint-op missions.
	if ( ( in_array( 'subscriber', $current_user->roles, true ) ) && ( get_field( 'brief_mission_type' ) === 'jo' ) ) {
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
	echo esc_html( get_field( 'brief_execution' ) );

	echo '<h3>Intel</h3>';
	echo esc_html( get_field( 'brief_intel' ) );

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// echo '<h3>Terrain</h3>';
	// echo get_field('terrain');
	// .

	echo '<div class="container briefing-meta"><div class="one-third column"><h3>Time</h3>';
	echo esc_html( get_field( 'brief_start_time' ) );
	echo '</div>';

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// echo '<h3>Weather</h3>';
	// echo get_field('weather');
	// echo '<h3>IED/Mine Threat</h3>';
	// echo get_field('iedmine_threat');
	// .

	echo '<div class="one-third column"><h3>Enemy Forces</h3>';
	echo esc_html( get_field( 'brief_enemy_forces' ) );
	echo '</div>';

	echo '<div class="one-third column"><h3>Friendly Forces</h3>';
	echo esc_html( get_field( 'brief_friendly_forces' ) );
	echo '</div></div>';

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// echo '<h3>Civilians</h3>';
	// echo get_field('civilians');
	// echo '<h3>Vehicles</h3>';
	// echo get_field('vehicles');
	// echo '<h3>Supplies</h3>';
	// echo get_field('supplies');
	// echo '<h3>Support</h3>';
	// echo get_field('support');
	// echo '<h3>Reinforcements</h3>';
	// echo get_field('reinforcements');
	// echo '<h3>Rules of Engagement</h3>';
	// echo get_field('rules_of_engagement');
	// echo '<h3>Command and Signals</h3>';
	// echo get_field('command_and_signals');
	// .

	echo '</div>';

	// Early out if no entries in rsvp field.
	if ( ! have_rows( 'rsvp' ) ) {
		return;
	}

	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// print_r ( get_field('rsvp') );
	// print_r ( get_field('time_stamp') );
	// .

	echo '<div id="attendanceRoster"><div class="inner">';
	echo '<h2>Attendance</h2>';
	echo '<div class="wrap">';

	while ( have_rows( 'rsvp' ) ) :
		the_row();
		$i = get_row_index();

		echo '<div class="attendanceCol" id="rsvpRow-' . esc_attr( $i ) . '">';
		echo '<h4>' . esc_html( get_sub_field( 'label' ) ) . '</h4>';

		$already_registered = false;
		$user_ids           = get_sub_field( 'user' );

		if ( $user_ids ) {
			// Check if user in list.
			if ( in_array( $current_user_id, $user_ids, true ) ) {
				$already_registered = true;
			}

			// Display list.
			echo '<ul>';
			foreach ( $user_ids as $user_id ) {
				$user_data = get_user_data( $user_id );
				$avatar    = get_avatar_url( $user_id );
				$avatar    = false;
				if ( $avatar ) {
					echo '<li><img src="' . esc_url( $avatar ) . '" alt="author-avatar"><a href="/user-info/?id=' . esc_attr( $user_id ) . '">' . esc_html( $user_data->display_name ) . '</a></li>';
				} else {
					echo '<li><a href="/user-info/?id=' . esc_attr( $user_id ) . '">' . esc_html( $user_data->display_name ) . '</a></li>';
				}
			}
			echo '</ul>';
		}

		echo '<form class="rsvpFormUnregister" id="rsvpFormUnregister-' . esc_attr( $i ) . '">';
		echo '<input type="hidden" name="postId" class="rsvpPostID" value="' . esc_attr( $post_id ) . '">';
		echo '<input type="hidden" name="user_id" class="rsvpuser_id" value="' . esc_attr( $current_user_id ) . '">';
		echo '<input type="hidden" name="selection" class="rsvpSelection" value="' . esc_attr( $i ) . '">';

		if ( $already_registered ) {
			echo '<input type="submit" value="Unregister"></form>';
		} else {
			echo '<input type="submit" value="Register"></form>';
		}

		echo '</div>';
	endwhile;
	echo '</div></div></div>';
}
