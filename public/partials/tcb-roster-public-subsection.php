<?php

function tcb_roster_public_subsection($attributes) {

	$meta_key = $attributes['rank'];
	//$meta_key = $attributes;
	
	//echo "tcb_roster_subsection start\n";
	
	global $wpdb;
		
	$wpdb->suppress_errors(false);
	
	$arrayOfUsers = $wpdb->get_results( $wpdb->prepare(
		"
			SELECT tab3.ID, tab3.user_nicename
			FROM {$wpdb->prefix}groups_group tab1
			INNER JOIN {$wpdb->prefix}groups_user_group tab2
			ON tab1.group_id = tab2.group_id
			INNER JOIN {$wpdb->prefix}users tab3
			ON tab2.user_id = tab3.ID
			WHERE tab1.name = %s
		",
		$meta_key
	), ARRAY_N );

	//print_r($results);
	
	if ( $wpdb->last_error ) {
   		$wpdb->print_error();
	}
	
	$return = '';
	
	foreach ( $arrayOfUsers as $user ) {
		$return .= '<a href="//localhost/wordpress/user-info/?id=' . $user[0] . '">' . $user[1] . '</a></br>';
	}
	
	//echo "tcb_roster_subsection end\n";
	//

	return $return;
}
