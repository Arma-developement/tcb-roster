<?php

function tcb_roster_public_subsection($attributes) {

	$meta_key = $attributes['rank'];
	
	$args = array(
		'meta_query' => array(
			array(
				'key' => 'rank',
				'value' => $meta_key,
				'compare' => '='
			)
		)
	);
	$user_query = new WP_User_Query( $args );
	foreach ( $user_query->get_results() as $user ) {
		$return .= '<a href="//localhost/wordpress/user-info/?id=' . $user->ID . '">' . $user->display_name . '</a></br>';
	}

	return $return;
}
