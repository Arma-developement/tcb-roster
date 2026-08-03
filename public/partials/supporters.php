<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: supporters.php
 * Description: Handles the code associated with recognising Supporters and Donors, for display
 * on the public donation page, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_supporters_and_donors', 'tcbp_public_supporters_and_donors' );

/**
 * Shortcode to list current Supporters and Donors, for the donation page. Both are drawn from
 * the "community_awards" checkbox field (group_6356980addb3c, Service Record Commendations) on
 * each member's service record - the same source the commendations archive reads from - filtered
 * to the "supporter" and "donator" values respectively. Deliberately public (no login/role gate):
 * this is a public thank-you page, not an internal roster.
 */
function tcbp_public_supporters_and_donors() {

	$args = array(
		'numberposts' => -1,
		'post_type'   => 'service-record',
	);

	$supporter_ids = array();
	$donor_ids     = array();

	$list_of_posts = get_posts( $args );
	if ( $list_of_posts ) {
		foreach ( $list_of_posts as $post ) {
			setup_postdata( $post );
			$user_id        = get_field( 'user_id', $post );
			$list_of_awards = get_field( 'community_awards', $post );
			if ( ! $list_of_awards ) {
				continue;
			}
			foreach ( $list_of_awards as $award ) {
				if ( 'supporter' === $award['value'] ) {
					$supporter_ids[] = $user_id;
				} elseif ( 'donator' === $award['value'] ) {
					$donor_ids[] = $user_id;
				}
			}
		}
	}
	wp_reset_postdata();

	ob_start();

	echo '<div class="tcb_supporters_donors">';

	echo '<div class="tcb_supporter_group">';
	echo '<h3>Supporter</h3>';
	echo '<p class="tcb_award_note">Currently active subscribers.</p>';
	echo '<ul>';
	foreach ( tcbp_public_sort_user_ids_by_display_name( $supporter_ids ) as $entry ) {
		echo '<li>' . esc_html( $entry['display_name'] ) . '</li>';
	}
	echo '</ul>';
	echo '</div>';

	echo '<div class="tcb_supporter_group">';
	echo '<h3>Donor</h3>';
	echo '<p class="tcb_award_note">Donated within the past 3 months.</p>';
	echo '<ul>';
	foreach ( tcbp_public_sort_user_ids_by_display_name( $donor_ids ) as $entry ) {
		echo '<li>' . esc_html( $entry['display_name'] ) . '</li>';
	}
	echo '</ul>';
	echo '</div>';

	echo '</div>';

	return ob_get_clean();
}
