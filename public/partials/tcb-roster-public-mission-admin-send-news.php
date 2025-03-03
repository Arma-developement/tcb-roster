<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * Function to send mission news.
 *
 * @param int $post_id The ID of the post.
 */
function tcb_roster_public_mission_send_news( $post_id ) {

	$user = wp_get_current_user();

	// Early out for no user.
	if ( ! $user->exists() ) {
		return;
	}

	// Early out for logged out users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id = $user->ID;

	// Retrieve data.
	$title                   = get_field( 'title', $post_id );
	$brief_image             = get_the_post_thumbnail_url( $post_id, 'large' );
	$brief_situation         = get_field( 'brief_situation', $post_id );
	$brief_mission           = get_field( 'brief_mission', $post_id );
	$post_op_summary         = get_field( 'post_op_summary', $post_id );
	$post_op_image           = get_field( 'post_op_image', $post_id );
	$post_op_secondary_image = get_field( 'post_op_secondary_image', $post_id );

	// Build content.
	$content = '<h2>Situation</h2>' . $brief_situation . '<h2>Mission</h2>' . $brief_mission;

	if ( '' !== $post_op_summary ) {
		$content .= '<h2>AAR</h2><div>' . $post_op_summary . '</div>';
	}

	if ( '' !== $post_op_secondary_image ) {
		$content .= '<p><img src="' . esc_url( $post_op_secondary_image['url'] ) . '" ></p>';
	}

	$new_post    = array(
		'post_title'    => 'After Action Report: ' . $title,
		'post_content'  => $content,
		'post_status'   => 'publish',
		'post_author'   => $user_id,
		'post_type'     => 'post',
		'post_category' => array( get_cat_ID( 'After Action Report' ) ),
	);
	$new_post_id = wp_insert_post( $new_post );

	if ( $new_post_id ) {
		// Add post thumbnail.
		if ( '' !== $post_op_image ) {
			$image_id = $post_op_image['ID'];
			if ( $image_id ) {
				set_post_thumbnail( $new_post_id, $image_id );
			}
		} elseif ( ! empty( $brief_image ) ) {
			set_post_thumbnail( $new_post_id, $brief_image );
		}
	}
}
