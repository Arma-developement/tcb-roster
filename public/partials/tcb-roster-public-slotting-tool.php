<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: slotting-tool.php
 * Description: Handles the code associated with the slotting-tool in the tcb plugin.
 */

/**
 * Called buy a shortcode to display the slotting tool.
 */
function tcb_roster_public_slotting_tool() {

	// Ensure user is logged in.
	$user = wp_get_current_user();
	if ( ! $user->exists() ) {
		return;
	}

	$user_id    = $user->ID;
	$user_login = $user->user_login;

	// Early out if no post.
	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	// Early out for subscribers on private missions.
	if ( ( in_array( 'subscriber', $user->roles, true ) ) && ( 'private' === get_field( 'brief_mission_type', $post_id ) ) ) {
		return;
	}

	// Early out if no entries in slots field.
	if ( ! have_rows( 'slots' ) ) {
		return;
	}

	// Calculate number of users registered as attending.
	$attendance = 0;
	if ( have_rows( 'rsvp' ) ) {
		while ( have_rows( 'rsvp' ) ) :
			the_row();
			$user_ids = get_sub_field( 'user' );
			if ( $user_ids ) {
				$attendance = count( $user_ids );
			}
			break;
		endwhile;
	}

	// Calculate if already slotted.
	$previously_slotted = tcb_roster_public_find_user_in_slotting( $post_id, $user_login );

	echo '<div class=' . ( $previously_slotted ? '"slotTool slotPreviouslySlotted"' : '"slotTool"' ) . ' id="slotTool"><div class="inner">';
	echo '<h2>Priority placements</h2>';
	echo '<p>Button to left of slot name appears when logged in.<br>Avatar appears when member is slotted</p>';

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
				$profile_image        = '';
				$user_id              = 1;
				$slotted_member_login = get_sub_field( 'slot_member' );
				$attendance_threshold = get_sub_field( 'attendance_threshold' );
				$is_locked            = $attendance < $attendance_threshold;
				$is_owner             = $slotted_member_login === $user_login;
				$is_disabled          = ( ( '' !== $slotted_member_login ) && ! $is_owner ) || $is_locked;
				$slotted_user         = get_user_by( 'login', $slotted_member_login );
				if ( $slotted_user ) {
					$slotted_user_id      = $slotted_user->ID;
					$profile_image        = get_avatar_url( $slotted_user_id );
					$slotted_display_name = $user->display_name;
				} else {
					$slotted_display_name = '';
				}

				echo '<div class=' . ( $is_owner ? '"slotToolSlot slotIconCanDelete"' : '"slotToolSlot"' ) . ' id="slotToolSlot-' . esc_attr( $j ) . '-' . esc_attr( $k ) . '">';
				echo '<form class="slotForm">';
				echo '<input type="hidden" name="postId" class="postID" value="' . esc_attr( $post_id ) . '">';
				echo '<input type="hidden" name="userId" class="userID" value="' . esc_attr( $user_id ) . '">';
				echo '<input type="hidden" name="slot" class="slot" value="' . esc_attr( $i ) . ',' . esc_attr( $j ) . ',' . esc_attr( $k ) . '">';
				echo '<input class="slotIcon ' . ( $is_disabled ? 'disabled"' : '"' ) . 'type="submit"';
				echo ' style="background-image:url(' . esc_url( $profile_image ? $profile_image : '' ) . ')"';
				echo '>';
				echo '</form>';

				if ( $is_locked ) {
					if ( $attendance_threshold < 999 ) {
						echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  locked (requires ' . esc_html( $attendance_threshold ) . ' attendees)<br>';
					} else {
						echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . "</strong>  -  locked (command's decision)<br>";
					}
				} else {
					echo '<strong>' . esc_html( get_sub_field( 'slot_name' ) ) . '</strong>  -  <span class="slotMember"><a href="/user-info/?id=' . esc_attr( $user_id ) . '">' . esc_attr( $slotted_display_name ) . '</a></span><br>';
				}
				echo '</div>';
			endwhile;
			echo '</div>';
		endwhile;
	endwhile;
	echo '</div></div>';

	echo '<div class="slotToolButtons" id="slotToolButtons" >';
	$roles = $user->roles;
	if ( in_array( 'mission_admin', $roles, true ) || in_array( 'administrator', $roles, true ) || in_array( 'editor', $roles, true ) ) {
		echo '<a href="/mission-admin-panel/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission Admin Panel</a>';
	}

	if ( in_array( 'mission_admin', $roles, true ) || in_array( 'administrator', $roles, true ) || in_array( 'editor', $roles, true ) ) {
		echo '<a href="/mission-news-panel/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission News Panel</a>';
	}

	if ( tcb_roster_public_find_user_in_slotting( $post_id, $user->user_login ) ) {
		echo '<a href="/mission-briefing/?id=' . esc_attr( $post_id ) . '" class="button button-secondary">Mission Briefing</a>';
	}
	echo '</div>';
}
