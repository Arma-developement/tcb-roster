<?php

function tcb_roster_public_application_view($attributes){

	if ((! in_array( 'training_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'recruit_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'administrator', wp_get_current_user()->roles)))
		return;

	$post = $_GET['post'];
	if ($post == "") 
		return;

	$return = '';

	setup_postdata( $post );
	$fields = get_field_objects($post);

	if ($fields) {
		$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $post ) );
		$steam_name = get_field( 'app_steam_name', $post );
		$return .= '<h4>' . $author_name . ' (' . $steam_name . ')</h4><ol>';
		foreach( $fields as $field ) {
			$return .= '<li><b>' . $field['label'] . ' </b><br>' . $field['value'] . '</li>';
		}
		$return .= '</ol>';
		wp_reset_postdata();		
	}

	return $return;
}