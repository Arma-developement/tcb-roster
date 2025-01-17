<?php

function tcb_roster_public_interview_view($attributes){

	if ((! in_array( 'training_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'recruit_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'administrator', wp_get_current_user()->roles)))
		return;

	if (!array_key_exists('post', $_GET))
		return; 
		
	$post_id = $_GET['post'];
	if ($post_id == "") 
		return;

	$return = '';
	$return .= '<div class="tcb_interview_view">';

	setup_postdata( $post_id );
	$fields = get_field_objects($post_id);

	if ($fields) {
		$userData = get_field( 'applicant', $post_id );
		$return .= '<h2>' . $userData['display_name'] . '</h2><ol>';

		// Rename the interview post to something more meaningful
		$post = get_post($post_id);
		if ($post->post_title !== $userData['display_name']) {
			wp_update_post(
				array (
					'ID'         => $post_id,
					'post_title' => $userData['display_name']
				)
			);
			error_log( print_r("Updated interview title to: " .$userData['display_name'], TRUE ));
		}

		foreach( $fields as $field ) {
			switch ($field['name']) {
				case 'applicant':
					$return .= '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value']['display_name'] . '</li><br>';
					break;
				case 'interviewers':
					$return .= '<li><strong>' . $field['label'] . ' </strong><br>';
					$nameList = [];
					foreach( $field['value'] as $interviewer )
						$nameList[] = '<a href="' . add_query_arg( 'id', $interviewer['ID'], home_url() . '/user-info' ) . '">' . $interviewer['display_name'] . '</a>';
					$return .= implode(', ', $nameList) . '</li><br>';
					break;
				case 'Interview_evaluation':
					$return .= '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value']['label'] . '</li><br>';
					break;
				default:
					$return .= '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value'] . '</li><br>';
			}
		}
		$return .= '</ol>';
		wp_reset_postdata();		
	}
	$return .= '</div>';

	return $return;
}