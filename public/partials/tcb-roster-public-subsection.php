<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Render the public subsection of the TCB Roster plugin.
 *
 * @param array $attributes Attributes passed to the subsection.
 */
function tcb_roster_public_subsection( $attributes ) {
	$return = '';

	$is_non_member = in_array( 'subscriber', wp_get_current_user()->roles, true );

	if ( array_key_exists( 'rank', $attributes ) ) {

		$rank = $attributes['rank'];

		$args = array(
			'numberposts' => -1,
			'post_type'   => 'service-record',
			'meta_key'    => 'rank', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $rank, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		);

		$return .= '<div class="tcb_subsection">';

		$list_of_posts = get_posts( $args );
		if ( $list_of_posts ) {
			$return .= '<ul>';
			foreach ( $list_of_posts as $post ) {
				setup_postdata( $post );

				$user_id = get_field( 'user_id', $post );
				$user    = get_user_by( 'id', $user_id );

				// Check for old service records, with no user.
				if ( ! $user ) {
					continue;
				}

				$display_name = $user->get( 'display_name' );

				if ( $is_non_member ) {
					$return .= '<li>' . $display_name . '</li>';
				} elseif ( ( get_field( 'loa', $post ) === 1 ) && ( 'Res' !== $rank ) ) {
						$return .= '<li><a href="' . home_url() . '/user-info/?id=' . $user_id . '">' . $display_name . '</a> (LOA)</li>';
				} else {
					$return .= '<li><a href="' . home_url() . '/user-info/?id=' . $user_id . '">' . $display_name . '</a></li>';
				}
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		$return .= '</div>';

		return $return;
	}

	if ( array_key_exists( 'duty', $attributes ) ) {

		$args = array(
			'numberposts'            => -1,
			'post_type'              => 'service-record',
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'duties',
					'value'   => $attributes['duty'],
					'compare' => 'LIKE',
				),
			),
			'cache_results'          => true, // Enable caching.
			'update_post_meta_cache' => true, // Enable post meta caching.
			'update_post_term_cache' => true, // Enable term caching.
		);

		$return .= '<div class="tcb_subsection">';

		$list_of_posts = get_posts( $args );
		if ( $list_of_posts ) {
			$return .= '<ul>';
			foreach ( $list_of_posts as $post ) {
				setup_postdata( $post );

				$user_id = get_field( 'user_id', $post );
				$user    = get_user_by( 'id', $user_id );

				// Check for old service records, with no user.
				if ( ! $user ) {
					continue;
				}

				$display_name = $user->get( 'display_name' );

				if ( $is_non_member ) {
					$return .= '<li>' . $display_name . '</li>';
				} else {
					$return .= '<li><a href="' . home_url() . '/user-info/?id=' . $user_id . '">' . $display_name . '</a></li>';
				}
			}
			$return .= '</ul>';
		}
		wp_reset_postdata();

		$return .= '</div>';

		return $return;
	}
	return $return;
}
