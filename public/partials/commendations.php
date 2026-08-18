<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: commendations.php
 * Description: Handles the code associated with commendations, a group within the service record, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_archive_commendations', 'tcbp_public_archive_commendations' );

/**
 * Builds a commendation's image tooltip text: its title, plus (if a matching term exists in
 * the "tcb-commendation" taxonomy) that term's description appended on a new line. The term is
 * looked up by slug, which matches the value already stored in the relevant ACF field/sub-field.
 *
 * @param string $title The commendation's display title.
 * @param string $slug  The commendation's slug.
 * @return string The tooltip text.
 */
function tcbp_public_commendation_tooltip( $title, $slug ) {
	$term = get_term_by( 'slug', $slug, 'tcb-commendation' );
	if ( ! $term || is_wp_error( $term ) || ! $term->description ) {
		return $title;
	}
	return $title . "\n\n" . $term->description;
}

/**
 * Shortcode to generate an archive for all commendations.
 */
function tcbp_public_archive_commendations() {

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

	echo '<div class="tcb_commendations">';
	echo '<p><a href="/commendation-descriptions/">Description of Commendations</a></p><br>';

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
					$list_of_service_award_recipients[ $year ][] = $user_id;
					$list_of_service_award_titles[ $year ]       = 'Service award, year ' . $year;
					$list_of_service_award_image[ $year ]	     = 'service-' . $year;
				}
			}

			$list_of_awards = get_field( 'campaign_medals', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$index = $award['value'];
					$list_of_campaign_medal_recipients[ $index ][] = $user_id;
					$list_of_campaign_medal_titles[ $index ]       = tcbp_public_commendation_tooltip( $award['label'], $index );
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
							$list_of_leadership_titles[ $index ]       = tcbp_public_commendation_tooltip( $title_ . ' x ' . $image_translation[ $idx - 1 ], $name );
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
							$list_of_mention_in_despatches_titles[ $index ]       = tcbp_public_commendation_tooltip( $title_ . ' x ' . $image_translation[ $idx - 1 ], $name );
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
							$list_of_mission_creation_titles[ $index ]       = tcbp_public_commendation_tooltip( $title_ . ' x ' . $image_translation[ $idx - 1 ], $name );
						}
					}
				}
			}

			$list_of_awards = get_field( 'community_awards', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$index = $award['value'];
					$list_of_community_award_recipients[ $index ][] = $user_id;
					$list_of_community_award_titles[ $index ]       = tcbp_public_commendation_tooltip( $award['label'], $index );
				}
			}
		}

		if ( ! empty( $list_of_service_award_titles ) ) {
			echo '<div class="tcb_award">';
			echo '<h4>Long Service Medals</h4>';
			krsort( $list_of_service_award_titles );
			$column = 0;
			foreach ( $list_of_service_award_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $list_of_service_award_image[ $key ] . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( tcbp_public_sort_user_ids_by_display_name( $list_of_service_award_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . '</a></li>';
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
				foreach ( tcbp_public_sort_user_ids_by_display_name( $list_of_campaign_medal_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . '</a></li>';
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
			krsort( $list_of_leadership_titles );
			$column = 0;
			foreach ( $list_of_leadership_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( tcbp_public_sort_user_ids_by_display_name( $list_of_leadership_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . '</a></li>';
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
			krsort( $list_of_mention_in_despatches_titles );
			$column = 0;
			foreach ( $list_of_mention_in_despatches_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( tcbp_public_sort_user_ids_by_display_name( $list_of_mention_in_despatches_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . '</a></li>';
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
			krsort( $list_of_mission_creation_titles );
			$column = 0;
			foreach ( $list_of_mission_creation_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				echo '<img src="' . esc_url( $path . $key . '.png' ) . '" title="' . esc_attr( $title ) . '" style="width:' . esc_attr( $width ) . 'px;height:' . esc_attr( $height ) . 'px;"><ul>';
				foreach ( tcbp_public_sort_user_ids_by_display_name( $list_of_mission_creation_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . '</a></li>';
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
				foreach ( tcbp_public_sort_user_ids_by_display_name( $list_of_community_award_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">' . esc_html( $entry['display_name'] ) . '</a></li>';
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

add_shortcode( 'tcbp_public_commendation_descriptions', 'tcbp_public_commendation_descriptions' );

/**
 * Shortcode: lists every commendation - name, description, and ribbon image - grouped by the
 * commendation's own parent term in the tcb-commendation taxonomy, which is now the single
 * source of truth for group membership (rather than hardcoded per-commendation lists or ACF
 * field choices). Long Service Medals has no taxonomy entries - it's generated dynamically per
 * year of service, not a fixed list of commendations - so it stays a fixed paragraph.
 */
function tcbp_public_commendation_descriptions() {

	$path = plugins_url() . '/tcb-roster/images/ribbons/';

	ob_start();
	echo '<div class="tcb_commendation_descriptions">';

	echo '<div class="tcb_award">';
	echo '<h4>Long Service Medals</h4>';
	echo '<p>Awarded automatically for every full year of service, based on a member&rsquo;s passing-out date. There is a distinct ribbon design for each year of service.</p>';
	echo '</div>';

	// Leadership/Mention in Despatches/Mission Creation commendations have one ribbon image per
	// level (e.g. troop-1.png through troop-6.png) rather than one per commendation - the
	// level-1 image represents the commendation here, since this page lists each once. There's
	// nothing in the taxonomy itself marking a group as "leveled", so this stays a fixed list.
	$leveled_groups = array( 'leadership_commendations', 'mention_in_despatches', 'mission_creation' );

	$groups = array( 'campaign_medals', 'leadership_commendations', 'mention_in_despatches', 'mission_creation', 'community_awards' );
	foreach ( $groups as $group_slug ) {
		tcbp_public_commendation_descriptions_group( $group_slug, in_array( $group_slug, $leveled_groups, true ), $path );
	}

	echo '</div>';
	return ob_get_clean();
}

/**
 * Renders one grouped section of the commendation descriptions page, sourced entirely from the
 * tcb-commendation taxonomy: the group's own parent term supplies the heading, and its child
 * terms each supply a commendation's name, description, and (if set) wiki link.
 *
 * @param string $group_slug The parent term's slug.
 * @param bool   $leveled    Whether this group's commendations use a level-1 suffixed image
 *                           (e.g. troop-1.png) rather than a plain one (e.g. afghan17.png).
 * @param string $path       Base URL for ribbon images.
 */
function tcbp_public_commendation_descriptions_group( $group_slug, $leveled, $path ) {
	$parent = get_term_by( 'slug', $group_slug, 'tcb-commendation' );
	if ( ! $parent || is_wp_error( $parent ) ) {
		return;
	}

	$children = get_terms(
		array(
			'taxonomy'   => 'tcb-commendation',
			'parent'     => $parent->term_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	if ( ! $children || is_wp_error( $children ) ) {
		return;
	}

	echo '<div class="tcb_award">';
	echo '<h4>' . esc_html( $parent->name ) . '</h4>';

	foreach ( $children as $term ) {
		$image    = $path . $term->slug . ( $leveled ? '-1' : '' ) . '.png';
		$wiki_url = get_field( 'wiki_url', 'tcb-commendation_' . $term->term_id );

		echo '<div class="tcb_commendation_entry">';
		echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $term->name ) . '">';
		echo '<div class="tcb_commendation_entry_text">';

		if ( $wiki_url ) {
			echo '<h5><a href="' . esc_url( $wiki_url ) . '">' . esc_html( $term->name ) . '</a></h5>';
		} else {
			echo '<h5>' . esc_html( $term->name ) . '</h5>';
		}

		if ( $term->description ) {
			echo '<p>' . esc_html( $term->description ) . '</p>';
		}

		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}
