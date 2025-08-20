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

	$image_translation = array( 1, 4, 16, 64, 256, 1024 );

	$mention_in_despatches = array(
		'combat_medic'     => 'Combat Medic',
		'weapons_operator' => 'Weapons Operator',
		'armour_asset'     => 'Armour Asset',
		'air_asset'        => 'Air Asset',
		'man_of_the_match' => 'Man of the Match',
	);
	$leadership            = array(
		'troop'    => 'Troop Leadership',
		'section'  => 'Section Leadership',
		'fireteam' => 'Fireteam Leadership',
		'asset'    => 'Asset Leadership',
	);
	$mission_creation      = array(
		'mission_author' => 'Mission Author',
		'zeus'           => 'Zeus',
	);

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
					$index                                        = 'service-' . $year;
					$list_of_service_award_recipients[ $index ][] = $user_id;
					$list_of_service_award_titles[ $index ]       = 'Service award, year ' . $year;
				}
			}

			$list_of_awards = get_field( 'campaign_medals', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$index = $award['value'];
					$list_of_campaign_medal_recipients[ $index ][] = $user_id;
					$list_of_campaign_medal_titles[ $index ]       = $award['label'];
				}
			}

			$sub_field = get_field( 'leadership', $post );
			if ( $sub_field ) {
				foreach ( $leadership as $name => $title_ ) {
					if ( isset( $sub_field[ $name ] ) ) {
						$value = intval( $sub_field[ $name ] );
						if ( $value > 0 ) {
							foreach ( $image_translation as $idx => $img_val ) {
								if ( $img_val > $value ) {
									break;
								}
							}
							$index                                     = $name . '-' . $idx;
							$list_of_leadership_recipients[ $index ][] = $user_id;
							$list_of_leadership_titles[ $index ]       = $title_ . ' x ' . $image_translation[ $idx - 1 ];
						}
					}
				}
			}

			$sub_field = get_field( 'mention_in_despatches', $post );
			if ( $sub_field ) {
				foreach ( $mention_in_despatches as $name => $title_ ) {
					if ( isset( $sub_field[ $name ] ) ) {
						$value = intval( $sub_field[ $name ] );
						if ( $value > 0 ) {
							foreach ( $image_translation as $idx => $img_val ) {
								if ( $img_val > $value ) {
									break;
								}
							}
							$index = $name . '-' . $idx;
							$list_of_mention_in_despatches_recipients[ $index ][] = $user_id;
							$list_of_mention_in_despatches_titles[ $index ]       = $title_ . ' x ' . $image_translation[ $idx - 1 ];
						}
					}
				}
			}

			$sub_field = get_field( 'mission_creation', $post );
			if ( $sub_field ) {
				foreach ( $mission_creation as $name => $title_ ) {
					if ( isset( $sub_field[ $name ] ) ) {
						$value = intval( $sub_field[ $name ] );
						if ( $value > 0 ) {
							foreach ( $image_translation as $idx => $img_val ) {
								if ( $img_val > $value ) {
									break;
								}
							}
							$index = $name . '-' . $idx;
							$list_of_mission_creation_recipients[ $index ][] = $user_id;
							$list_of_mission_creation_titles[ $index ]       = $title_ . ' x ' . $image_translation[ $idx - 1 ];
						}
					}
				}
			}

			$list_of_awards = get_field( 'community_awards', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$index = $award['value'];
					$list_of_community_award_recipients[ $index ][] = $user_id;
					$list_of_community_award_titles[ $index ]       = $award['label'];
				}
			}
		}

		if ( ! empty( $list_of_service_award_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Long Service Medals</h4>';
			ksort( $list_of_service_award_titles );
			$column = 0;
			foreach ( $list_of_service_award_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_service_award_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				$column = ( ++$column ) % 3;
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}

		if ( ! empty( $list_of_campaign_medal_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Campaign Medals</h4>';
			ksort( $list_of_campaign_medal_titles );
			$column = 0;
			foreach ( $list_of_campaign_medal_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_campaign_medal_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				$column = ( ++$column ) % 3;
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}

		if ( ! empty( $list_of_leadership_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Leadership Commendations</h4>';
			ksort( $list_of_leadership_titles );
			$column = 0;
			foreach ( $list_of_leadership_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_leadership_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				$column = ( ++$column ) % 3;
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}

		if ( ! empty( $list_of_mention_in_despatches_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Mention in Despatches</h4>';
			ksort( $list_of_mention_in_despatches_titles );
			$column = 0;
			foreach ( $list_of_mention_in_despatches_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_mention_in_despatches_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				$column = ( ++$column ) % 3;
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}

		if ( ! empty( $list_of_mission_creation_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Mission Creation</h4>';
			ksort( $list_of_mission_creation_titles );
			$column = 0;
			foreach ( $list_of_mission_creation_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_mission_creation_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				$column = ( ++$column ) % 3;
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}

		if ( ! empty( $list_of_community_award_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Community Awards</h4>';
			ksort( $list_of_community_award_titles );
			$column = 0;
			foreach ( $list_of_community_award_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( $list_of_community_award_recipients[ $key ] as $user_id ) {
					$user         = get_user_by( 'id', $user_id );
					$display_name = $user->get( 'display_name' );
					echo '<li><a href="/service-record/service-record-' . esc_attr( $user_id ) . '">' . esc_html( $display_name ) . '</a></li>';
				}
				$column = ( ++$column ) % 3;
				echo '</ul>';
				echo '</div>';
			}
			echo '</div>';
		}
	}
	wp_reset_postdata();
	echo '</div>';
	return ob_get_clean();
}
