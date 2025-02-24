<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: commendations.php
 * Description: Handles the code associated with commendations, a group within the service record, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_archive_commendations', 'tcbp_public_archive_commendations' );

/**
 * Shortcode to generate an archive for all commendations.
 */
function tcbp_public_archive_commendations() {

	$args = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
	);

	ob_start();

	echo '<div class="tcb_commendations">';
	echo '<p><a href="/information-centre/commendations/">Description of Commendations</a></p><br>';

	$path   = plugins_url() . '/tcb-roster/images/ribbons/';
	$width  = 350 / 2;
	$height = 94 / 2;
	$now    = new DateTime( 'now' );

	// Build a list of awards titles and recipients, dynamically from the service records.
	$list_of_posts = get_posts( $args );
	if ( $list_of_posts ) {
		foreach ( $list_of_posts as $post ) {
			setup_postdata( $post );
			$user_id = get_field( 'user_id', $post );

			$date_str = get_field( 'passing_out_date', $post );
			$date     = DateTime::createFromFormat( 'd/m/Y', $date_str );
			if ( $date ) {
				$interval = $date->diff( $now );
				$year     = $interval->y;
				if ( $year > 0 ) {
					$list_of_recipients[ 'service-' . $year ][]         = $user_id;
					$list_of_service_award_titles[ 'service-' . $year ] = 'Service award, year ' . $year;
				}
			}

			$list_of_awards = get_field( 'operational_awards', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$list_of_recipients[ $award['value'] ][]             = $user_id;
					$list_of_operational_award_titles[ $award['value'] ] = $award['label'];
				}
			}

			$list_of_awards = get_field( 'community_awards', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$list_of_recipients[ $award['value'] ][]           = $user_id;
					$list_of_community_award_titles[ $award['value'] ] = $award['label'];
				}
			}
		}

		if ( ! empty( $list_of_service_award_titles ) ) {
			ksort( $list_of_service_award_titles );
			foreach ( $list_of_service_award_titles as $key => $title ) {
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				echo '</ul>';
			}
		}

		if ( ! empty( $list_of_operational_award_titles ) ) {
			ksort( $list_of_operational_award_titles );
			foreach ( $list_of_operational_award_titles as $key => $title ) {
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				echo '</ul>';
			}
		}

		if ( ! empty( $list_of_community_award_titles ) ) {
			ksort( $list_of_community_award_titles );
			foreach ( $list_of_community_award_titles as $key => $title ) {
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				echo '</ul>';
			}
		}
	}
	wp_reset_postdata();
	echo '</div>';
	return ob_get_clean();
}
