<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: duties.php
 * Description: Handles the code associated with duties, a group within the service record, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_archive_duties', 'tcbp_public_archive_duties' );

/**
 * Shortcode to generate an archive for all duties.
 */
function tcbp_public_archive_duties() {

	$terms = get_terms(
		array(
			'taxonomy'   => 'tcb-duty',
			'hide_empty' => false,
		),
	);

	if ( empty( $terms ) ) {
		return;
	}

	ob_start();

	echo '<p>Named roles with responsibility for specific areas of support within 3CB</p>';

	echo '<div class="tcb_duties">';

	foreach ( $terms as $term ) {
		$term_id          = $term->term_id;
		$term_name        = $term->name;
		$term_description = $term->description;
		echo '<h2>' . esc_html( $term_name ) . '</h2>';
		echo '<p>' . esc_html( $term_description ) . '</p>';

		$query_args = array(
			'post_type'              => 'service-record',
			'posts_per_page'         => -1,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'tcb-duty',
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			),
			'no_found_rows'          => true, // Improve performance by not counting total rows.
			'update_post_meta_cache' => false, // Improve performance by not updating post meta cache.
			'update_post_term_cache' => false, // Improve performance by not updating post term cache.
		);

		$posts_ = new WP_Query( $query_args );
		if ( ! $posts_->have_posts() ) {
			echo '<p>Vacancy</p>';
			continue;
		}

		echo '<ul>';
		while ( $posts_->have_posts() ) {
			$posts_->the_post();
			$post_id_ = get_the_ID();
			$user_id  = get_field( 'user_id', $post_id_ );
			$user     = get_user_by( 'id', $user_id );
			if ( ! $user ) {
				continue;
			}
			$display_name = $user->get( 'display_name' );
			echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( $display_name ) . '</a></li>';
		}
		echo '</ul>';
		wp_reset_postdata();
	}

	echo '</div>';
	echo '<hr>';
	echo '<div class="tcb_generic_duties">';
	echo '<h1>Rank related duties</h1>';
	echo '<p>Ranks with responsibility for generic administrative support within 3CB</p>';

	$admin_ranks = get_terms(
		array(
			'taxonomy'   => 'tcb-rank',
			'hide_empty' => false,
			'slug'       => array( 'lt', 'csgt', 'sgt' ),
		),
	);

	foreach ( $admin_ranks as $rank ) {
		$rank_id          = $rank->term_id;
		$rank_name        = $rank->name;
		$rank_description = $rank->description;
		echo '<h2>' . esc_html( $rank_name ) . '</h2>';
		echo '<p>' . esc_html( $rank_description ) . '</p>';

		$query_args = array(
			'post_type'              => 'service-record',
			'posts_per_page'         => -1,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'tcb-rank',
					'field'    => 'term_id',
					'terms'    => $rank_id,
				),
			),
			'no_found_rows'          => true, // Improve performance by not counting total rows.
			'update_post_meta_cache' => false, // Improve performance by not updating post meta cache.
			'update_post_term_cache' => false, // Improve performance by not updating post term cache.
		);

		$posts_ = new WP_Query( $query_args );
		if ( ! $posts_->have_posts() ) {
			echo '<p>Vacancy</p>';
			continue;
		}

		echo '<ul>';
		while ( $posts_->have_posts() ) {
			$posts_->the_post();
			$post_id_ = get_the_ID();
			$user_id  = get_field( 'user_id', $post_id_ );
			$user     = get_user_by( 'id', $user_id );
			if ( ! $user ) {
				continue;
			}
			$display_name = $user->get( 'display_name' );
			echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( $display_name ) . '</a></li>';
		}
		echo '</ul>';
		wp_reset_postdata();
	}

	echo '<br>';
	echo '</div>';

	return ob_get_clean();
}
