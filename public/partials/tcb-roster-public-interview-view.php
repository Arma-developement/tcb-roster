<?php

function tcb_roster_public_interview_view($attributes){

	if ((! in_array( 'training_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'recruit_admin', wp_get_current_user()->roles)) && 
		(! in_array( 'administrator', wp_get_current_user()->roles)))
		return;

	if (!array_key_exists('post', $_GET))
		return; 
		
	$postID = $_GET['post'];
	if ($postID == "") 
		return;

	ob_start();

	echo '<div class="tcb_interview_view">';

	setup_postdata( $postID );
	$fields = get_field_objects($postID);

	if ($fields) {
		$userData = get_field( 'applicant', $postID );
		echo '<h2>' . $userData['display_name'] . '</h2><ol>';

		// Rename the interview post to something more meaningful
		$postLocal = get_post($postID);
		if ($postLocal->post_title !== $userData['display_name']) {
			wp_update_post(
				array (
					'ID'         => $postID,
					'post_title' => $userData['display_name']
				)
			);
			error_log( print_r("Updated interview title to: " .$userData['display_name'], TRUE ));
		}

		foreach( $fields as $field ) {
			switch ($field['name']) {
				case 'applicant':
					echo '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value']['display_name'] . '</li><br>';
					break;
				case 'interviewers':
					echo '<li><strong>' . $field['label'] . ' </strong><br>';
					$nameList = [];
					foreach( $field['value'] as $interviewer )
						$nameList[] = '<a href="' . add_query_arg( 'id', $interviewer['ID'], home_url() . '/user-info' ) . '">' . $interviewer['display_name'] . '</a>';
						echo implode(', ', $nameList) . '</li><br>';
					break;
				case 'Interview_evaluation':
					echo '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value']['label'] . '</li><br>';
					break;
				default:
					if ($field['label'] == 'Status') {
						echo '<li><strong>' . $field['label'] . ' </strong><br>';
						$terms = get_the_terms( $postID, 'tcb-status' );
						if ($terms) {
							foreach($terms as $term) {
								echo $term->name;
							} 
						}
						echo '</li><br>';
					} else
					echo '<li><strong>' . $field['label'] . ' </strong><br>' . $field['value'] . '</li><br>';
			}
		}
		echo '</ol>';

		//var_dump($postLocal);
		global $post;
		//var_dump($post);
		$post = $postLocal;
		//var_dump($post);

		comments_template();

	} else {
		echo '<h2>No Interview Data</h2>';	
	}

	wp_reset_postdata();

	echo '<a href="'. home_url() .'/edit-status/?id=' . $postID . '" class="button button-secondary">Edit Status</a><br>';

	echo '</div>';

	return ob_get_clean();
}