<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: training.php
 * Description: Handles the code associated with training, a group within the service record, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_archive_training', 'tcbp_public_archive_training' );

/**
 * Shortcode to generate an archive for all training.
 */
function tcbp_public_archive_training() {

	// Members only - don't rely solely on the page-level restriction, since this shortcode's
	// output could be reached some other way (a different page, a widget, etc.).
	if ( ! is_user_logged_in() || in_array( 'subscriber', wp_get_current_user()->roles, true ) ) {
		return;
	}

	$args = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
	);

	ob_start();

	echo '<div class="tcb_training">';

	// Build a list of course titles and course attendance, dynamically from the service records.
	$list_of_titles     = array();
	$list_of_attendance = array();
	$list_of_posts      = get_posts( $args );
	if ( $list_of_posts ) {
		foreach ( $list_of_posts as $post ) {
			setup_postdata( $post );
			$user_id         = get_field( 'user_id', $post );
			$list_of_courses = get_field( 'courses_completed', $post );
			if ( $list_of_courses ) {
				foreach ( $list_of_courses as $course ) {
					$list_of_attendance[ $course['value'] ][] = $user_id;
					$list_of_titles[ $course['value'] ]       = $course['label'];
				}
			}
		}

		ksort( $list_of_titles );

		foreach ( $list_of_titles as $key => $title ) {
			echo '<h3>' . esc_html( $title ) . '</h3><ul>';
			foreach ( $list_of_attendance[ $key ] as $user_id ) {
				$user = get_user_by( 'id', $user_id );
				if ( ! $user ) {
					continue;
				}
				$display_name = $user->get( 'display_name' );
				echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
			}
			echo '</ul>';
		}
	}
	wp_reset_postdata();
	echo '</div>';
	return ob_get_clean();
}
