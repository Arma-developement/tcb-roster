<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Function to handle attendance roster updates.
 */
function tcb_roster_public_attendance_roster_update() {

	/**
	 * Function to handle attendance roster updates.
	 */
	function do_work() {
		if ( ! isset( $_POST['postId'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! isset( $_POST['userId'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! isset( $_POST['selection'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( ! isset( $_POST['nounce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$post_id_  = sanitize_text_field( wp_unslash( $_POST['postId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$user_id   = sanitize_text_field( wp_unslash( $_POST['userId'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selection = sanitize_text_field( wp_unslash( $_POST['selection'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nounce    = sanitize_text_field( wp_unslash( $_POST['nounce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Security check.
		if ( ! wp_verify_nonce( $nounce, 'attendance_roster_update_nounce' ) ) {
			return wp_send_json_error( 'Nounce failed' );
		}

		$registered_users = array();
		$fields           = get_field( 'rsvp', $post_id );
		if ( ! $fields ) {
			return wp_send_json_error( 'No fields in post' );
		}

		foreach ( $fields as $field ) {
			if ( $field['user'] ) {
				$registered_users = array_merge( $registered_users, $field['user'] );
			}
		}

		// New user, add to the appropriate list.
		if ( ! in_array( $user_id, $registered_users, true ) ) {
			add_sub_row( array( 'rsvp', $selection, 'user' ), $user_id, $post_id );

			// Check if user has previously registered.
			$previous_users = array();
			$fields         = get_field( 'time_stamp', $post_id );
			if ( $fields ) {
				foreach ( $fields as $field ) {
					$previous_users[] = $field['user'];
				}
			}

			// If a new user, then register the time.
			if ( ! in_array( $user_id, $previous_users, true ) ) {
				$date = getdate();
				add_row(
					'time_stamp',
					array(
						'user' => $user_id,
						'time' => $date,
						true,
					),
					$post_id
				);
				return wp_send_json_success( 'New user added, with timestamp' );
			}

			return wp_send_json_success( 'New user added' );
		}

		// Find and remove user.
		$i           = 0;
		$delete_only = false;
		while ( have_rows( 'rsvp', $post_id ) ) :
			the_row();
			$i     = get_row_index();
			$users = get_sub_field( 'user' );
			if ( $users ) {
				// Check if user in list.
				if ( in_array( $user_id, $users, true ) ) {

					// Check if already registered in this list.
					if ( $selection === $i ) {
						$delete_only = true;
					}

					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// error_log( print_r( 'Remove user: ' . $i . ' = ' . implode( ',', $users ), true ) );
					// .

					// Remove the user.
					$update_users = array_filter(
						$users,
						function ( $element ) use ( $user_id ) {
							return $element !== $user_id;
						}
					);
					$rc           = update_sub_field( array( 'rsvp', $i, 'user' ), $update_users, $post_id );
					if ( ! $rc ) {
						return wp_send_json_error( 'Failed to update user' );
					}

					break;
				}
			}
		endwhile;

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// while( have_rows('rsvp', $post_id) ) : the_row();
		// $j = get_row_index();
		// $users = get_sub_field('user');
		// if ($users) {
		// error_log( print_r("Remove user (final): " . $j . " = " . implode(',', $users), TRUE ));
		// } else {
		// error_log( print_r("Remove user (final): " . $j . " = []", TRUE ));
		// }
		// endwhile;
		// .

		if ( ! $delete_only ) {
			add_sub_row( array( 'rsvp', $selection, 'user' ), $user_id, $post_id );
		}

		// Remove from slotting.
		if ( 1 === $i ) {
			$user                = get_user_by( 'id', $user_id );
			$slotted_member_name = $user->user_login;
			tcb_roster_public_remove_from_slotting( $post_id, $slotted_member_name );
		}

		return wp_send_json_success( 'Existing user updated' );
	}

	do_work();
	wp_die();
}

/**
 * Updates the attendance roster.
 *
 * @param int   $post_id The ID of the post.
 * @param int   $user_id The ID of the user.
 * @param array $selection The selection data for the roster update.
 */
function tcb_roster_public_addToRSVP( $post_id, $user_id, $selection ) {
	$registered_users = array();
	$fields           = get_field( 'rsvp', $post_id );
	if ( ! $fields ) {
		return;
	}

	foreach ( $fields as $field ) {
		if ( $field['user'] ) {
			$registered_users = array_merge( $registered_users, $field['user'] );
		}
	}

	// New user, add to the appropriate list.
	if ( ! in_array( $user_id, $registered_users, true ) ) {
		add_sub_row( array( 'rsvp', $selection, 'user' ), $user_id, $post_id );

		// Check if user has previously registered.
		$previous_users = array();
		$fields         = get_field( 'time_stamp', $post_id );
		if ( $fields ) {
			foreach ( $fields as $field ) {
				$previous_users[] = $field['user'];
			}
		}

		// If a new user, then register the time.
		if ( ! in_array( $user_id, $previous_users, true ) ) {
			$date = getdate();
			add_row(
				'time_stamp',
				array(
					'user' => $user_id,
					'time' => $date,
				),
				$post_id
			);
		}
		return;
	}

	// Find and remove user.
	$delete_only = false;
	while ( have_rows( 'rsvp', $post_id ) ) :
		the_row();
		$i     = get_row_index();
		$users = get_sub_field( 'user' );
		if ( $users ) {
			// Check if user in list.
			if ( in_array( $user_id, $users, true ) ) {

				// Check if already registered in this list.
				if ( $selection === $i ) {
					return;
				}

				// Remove the user.
				$update_users = array_filter(
					$users,
					function ( $element ) use ( $user_id ) {
						return $element !== $user_id;
					}
				);
				$rc           = update_sub_field( array( 'rsvp', $i, 'user' ), $update_users, $post_id );
				if ( ! $rc ) {
					return wp_send_json_error( 'Failed to update user' );
				}

				break;
			}
		}
	endwhile;

	add_sub_row( array( 'rsvp', $selection, 'user' ), $user_id, $post_id );
}
