<?php

function tcb_roster_public_application_view($attributes){

	if ((! in_array( 'training_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'recruit_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'administrator', wp_get_current_user()->roles)))
		return;

	if (!array_key_exists('post', $_GET))
		return; 

	$postID = $_GET['post'];
	if ($postID == "") 
		return;

	$return = '<div class="tcb_application_view">';

	setup_postdata( $postID );
	$fields = get_field_objects($postID);
	
	if ($fields) {
		$author_name = get_the_author_meta( 'display_name', get_post_field( 'post_author', $postID ) );
		$steam_name = get_field( 'app_steam_name', $postID );

		$return .= '<h2>' . $author_name . ' (' . $steam_name . ')</h2><ol>';
		foreach( $fields as $field ) {
			if ($field['name'] == 'tcb-status') {
				$return .= '<li><strong>' . $field['label'] . ' </strong><br>';
				$terms = get_the_terms( $postID, 'tcb-status' );
				if ($terms) {
					foreach($terms as $term) {
						$return .= $term->name;
					} 
				}
				$return .= '</li><br>';
			} else
				$return .= '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value'] . '</li><br>';
		}
		$return .= '</ol>';

		wp_reset_postdata();
	}

	$return .= '<a href="'. home_url() .'/edit-status/?id=' . $postID . '" class="button button-secondary">Edit Status</a><br>';

	$return .= '</div>';

	return $return;
}