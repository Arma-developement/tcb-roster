<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: commendations.php
 * Description: Handles the code associated with commendations, a group within the service record, in the tcb plugin.
 */

add_shortcode( 'tcbp_public_archive_commendations', 'tcbp_public_archive_commendations' );

/**
 * Renders a ribbon image with a custom-styled tooltip (bold title, description directly below
 * with no gap, matching the site's own font/colours) rather than the native browser tooltip a
 * plain title attribute would give - which can't be restyled at all, since the browser renders
 * it outside the page's CSS entirely. If $slug is given and matches a term in the
 * "tcb-commendation" taxonomy with a description set, that description is shown; otherwise the
 * tooltip is just the title on its own.
 *
 * @param string $image_url The ribbon image URL.
 * @param string $title     The commendation's display title.
 * @param string $slug      The commendation's slug, for looking up its taxonomy description -
 *                          pass '' to skip the lookup (e.g. Long Service Medals, which aren't in
 *                          the taxonomy).
 * @param int    $width     Image width in pixels.
 * @param int    $height    Image height in pixels.
 */
function tcbp_public_commendation_image( $image_url, $title, $slug, $width, $height ) {
	$description = '';
	if ( $slug ) {
		$term = get_term_by( 'slug', $slug, 'tcb-commendation' );
		if ( $term && ! is_wp_error( $term ) ) {
			$description = $term->description;
		}
	}

	echo '<span class="tcb_commendation_tooltip">';
	echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '">';
	echo '<span class="tcb_commendation_tooltip_text"><strong>' . esc_html( $title ) . '</strong>';
	if ( $description ) {
		echo esc_html( $description );
	}
	echo '</span>';
	echo '</span>';
}

/**
 * Converts a user_id => award count map into display entries sorted by count (descending),
 * then display name (ascending) for ties. Used for the Leadership/Mention in Despatches/
 * Mission Creation groups on the commendations archive, which list every recipient once under
 * a single row per commendation type with their own count shown as a prefix, rather than the
 * old approach of splitting recipients into separate rows per award-count threshold tier.
 *
 * @param array $user_counts user_id => award count.
 * @return array Each entry: array( 'user_id' => ..., 'display_name' => ..., 'count' => ... ).
 */
function tcbp_public_sort_user_ids_by_count_then_display_name( $user_counts ) {
	$entries = array();
	foreach ( $user_counts as $user_id => $count ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			continue;
		}
		$entries[] = array(
			'user_id'      => $user_id,
			'display_name' => $user->get( 'display_name' ),
			'count'        => $count,
		);
	}

	usort(
		$entries,
		function ( $a, $b ) {
			if ( $a['count'] !== $b['count'] ) {
				return $b['count'] <=> $a['count'];
			}
			return strcasecmp( $a['display_name'], $b['display_name'] );
		}
	);

	return $entries;
}

/**
 * Returns the ordered child terms of a tcb-commendation taxonomy group, identified by its
 * parent term's slug. If $order is given, children are sorted to match it, with any child not
 * listed sorted after the listed ones - so a newly added commendation still shows (rather than
 * being silently dropped) while it's waiting to be placed in the manual order. Otherwise,
 * children are sorted alphabetically by name.
 *
 * @param string $group_slug The parent term's slug.
 * @param array  $order      Optional list of child slugs giving a manual display order.
 * @return WP_Term[] The group's child terms, or an empty array if the group doesn't exist.
 */
function tcbp_public_commendation_group_terms( $group_slug, $order = array() ) {
	$parent = get_term_by( 'slug', $group_slug, 'tcb-commendation' );
	if ( ! $parent || is_wp_error( $parent ) ) {
		return array();
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
		return array();
	}

	if ( $order ) {
		usort(
			$children,
			function ( $a, $b ) use ( $order ) {
				$pos_a = array_search( $a->slug, $order, true );
				$pos_b = array_search( $b->slug, $order, true );
				$pos_a = false === $pos_a ? PHP_INT_MAX : $pos_a;
				$pos_b = false === $pos_b ? PHP_INT_MAX : $pos_b;
				return $pos_a <=> $pos_b;
			}
		);
	}

	return $children;
}

/**
 * Converts a list of taxonomy terms into a slug => name array, the shape the archive page's
 * per-commendation loops expect (previously hardcoded per group).
 *
 * @param WP_Term[] $terms Terms to convert.
 * @return array
 */
function tcbp_public_commendation_names_from_terms( $terms ) {
	$names = array();
	foreach ( $terms as $term ) {
		$names[ $term->slug ] = $term->name;
	}
	return $names;
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

	// Names now come from the tcb-commendation taxonomy's group child terms, so a newly added
	// sub-field (e.g. "patrol") is picked up automatically once a matching term exists - no code
	// change needed here, unlike the previous hardcoded per-group lists.
	$mention_in_despatches = tcbp_public_commendation_names_from_terms( tcbp_public_commendation_group_terms( 'mention_in_despatches' ) );
	$leadership            = tcbp_public_commendation_names_from_terms( tcbp_public_commendation_group_terms( 'leadership_commendations', array( 'troop', 'section', 'fireteam', 'patrol', 'asset' ) ) );
	$mission_creation      = tcbp_public_commendation_names_from_terms( tcbp_public_commendation_group_terms( 'mission_creation' ) );

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
					$list_of_campaign_medal_titles[ $index ]       = array(
						'title' => $award['label'],
						'slug'  => $index,
					);
				}
			}

			// Leadership/Mention in Despatches/Mission Creation can each be awarded to a player
			// any number of times - rather than splitting recipients into separate rows per
			// threshold tier (the old "x1"/"x4"/"x16"... banding), every recipient is listed once
			// under a single row per commendation type, with their own raw count shown next to
			// their name (see tcbp_public_sort_user_ids_by_count_then_display_name() below).
			$sub_field = get_field( 'leadership', $post );
			if ( $sub_field ) {
				foreach ( $leadership as $name => $title_ ) {
					$value = isset( $sub_field[ $name ] ) ? intval( $sub_field[ $name ] ) : 0;
					if ( $value > 0 ) {
						$list_of_leadership_recipients[ $name ][ $user_id ] = $value;
						$list_of_leadership_titles[ $name ]                 = array(
							'title' => $title_,
							'slug'  => $name,
						);
					}
				}
			}

			$sub_field = get_field( 'mention_in_despatches', $post );
			if ( $sub_field ) {
				foreach ( $mention_in_despatches as $name => $title_ ) {
					$value = isset( $sub_field[ $name ] ) ? intval( $sub_field[ $name ] ) : 0;
					if ( $value > 0 ) {
						$list_of_mention_in_despatches_recipients[ $name ][ $user_id ] = $value;
						$list_of_mention_in_despatches_titles[ $name ]                 = array(
							'title' => $title_,
							'slug'  => $name,
						);
					}
				}
			}

			$sub_field = get_field( 'mission_creation', $post );
			if ( $sub_field ) {
				foreach ( $mission_creation as $name => $title_ ) {
					$value = isset( $sub_field[ $name ] ) ? intval( $sub_field[ $name ] ) : 0;
					if ( $value > 0 ) {
						$list_of_mission_creation_recipients[ $name ][ $user_id ] = $value;
						$list_of_mission_creation_titles[ $name ]                 = array(
							'title' => $title_,
							'slug'  => $name,
						);
					}
				}
			}

			$list_of_awards = get_field( 'community_awards', $post );
			if ( $list_of_awards ) {
				foreach ( $list_of_awards as $award ) {
					$index = $award['value'];
					$list_of_community_award_recipients[ $index ][] = $user_id;
					$list_of_community_award_titles[ $index ]       = array(
						'title' => $award['label'],
						'slug'  => $index,
					);
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
				tcbp_public_commendation_image( $path . $list_of_service_award_image[ $key ] . '.png', $title, '', $width, $height );
				echo '<ul>';
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
				tcbp_public_commendation_image( $path . $key . '.png', $title['title'], $title['slug'], $width, $height );
				echo '<ul>';
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
			// No krsort() - $list_of_leadership_titles is already in the manual display order
			// tcbp_public_commendation_group_terms() built $leadership in (troop/section/
			// fireteam/patrol/asset), since it's populated by iterating that same array above.
			$column = 0;
			foreach ( $list_of_leadership_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				// Always the level-1 image - every recipient is listed under this one row
				// regardless of their own award count, which is shown as a prefix instead (see
				// tcbp_public_sort_user_ids_by_count_then_display_name()).
				tcbp_public_commendation_image( $path . $key . '-1.png', $title['title'], $title['slug'], $width, $height );
				echo '<ul>';
				foreach ( tcbp_public_sort_user_ids_by_count_then_display_name( $list_of_leadership_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">x' . esc_html( $entry['count'] ) . ' ' . esc_html( $entry['display_name'] ) . '</a></li>';
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
			$column = 0;
			foreach ( $list_of_mention_in_despatches_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				tcbp_public_commendation_image( $path . $key . '-1.png', $title['title'], $title['slug'], $width, $height );
				echo '<ul>';
				foreach ( tcbp_public_sort_user_ids_by_count_then_display_name( $list_of_mention_in_despatches_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">x' . esc_html( $entry['count'] ) . ' ' . esc_html( $entry['display_name'] ) . '</a></li>';
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
			$column = 0;
			foreach ( $list_of_mission_creation_titles as $key => $title ) {
				echo '<div class="tcb_award_col' . esc_attr( $column + 1 ) . '">';
				tcbp_public_commendation_image( $path . $key . '-1.png', $title['title'], $title['slug'], $width, $height );
				echo '<ul>';
				foreach ( tcbp_public_sort_user_ids_by_count_then_display_name( $list_of_mission_creation_recipients[ $key ] ) as $entry ) {
					echo '<li><a href="/service-record/service-record-' . esc_attr( $entry['user_id'] ) . '">x' . esc_html( $entry['count'] ) . ' ' . esc_html( $entry['display_name'] ) . '</a></li>';
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
				tcbp_public_commendation_image( $path . $key . '.png', $title['title'], $title['slug'], $width, $height );
				echo '<ul>';
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

	// A manual display order for groups that need one - anything not listed here falls back to
	// alphabetical (by term name). Terms found in the taxonomy but missing from a group's list
	// still show, just sorted after the listed ones, so a newly added commendation isn't
	// silently dropped while waiting to be placed.
	$manual_order = array(
		'leadership_commendations' => array( 'troop', 'section', 'fireteam', 'patrol', 'asset' ),
	);

	$groups = array( 'campaign_medals', 'leadership_commendations', 'mention_in_despatches', 'mission_creation', 'community_awards' );
	foreach ( $groups as $group_slug ) {
		tcbp_public_commendation_descriptions_group(
			$group_slug,
			in_array( $group_slug, $leveled_groups, true ),
			$path,
			isset( $manual_order[ $group_slug ] ) ? $manual_order[ $group_slug ] : array()
		);
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
 * @param array  $order      Optional list of child slugs giving a manual display order. Any
 *                            child not listed here is sorted after the listed ones.
 */
function tcbp_public_commendation_descriptions_group( $group_slug, $leveled, $path, $order = array() ) {
	$parent = get_term_by( 'slug', $group_slug, 'tcb-commendation' );
	if ( ! $parent || is_wp_error( $parent ) ) {
		return;
	}

	$children = tcbp_public_commendation_group_terms( $group_slug, $order );
	if ( ! $children ) {
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
