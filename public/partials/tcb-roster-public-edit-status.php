<?php

function tcb_roster_public_edit_status($attributes) {

	$postId = $_GET['id'];
	if ($postId == "")
		return;

	// Security check
	if (($postId != "") && (!current_user_can( 'edit_post', $postId )))
		return;

	ob_start();+

	echo '<div class="tcb_edit_status">';

	acf_form( array( 
		'post_id' => $postId,
		'field_groups' => array( 'group_678bea513af25' ),
		'submit_value' => 'Update Status',
		'return' => wp_get_referer(),
		'updated_message' => false
	) );
	
	echo '</div>';

	if ( function_exists( 'SimpleLogger' ) ) {
		SimpleLogger()->info( 'Edited the status of postID=' . $postId );
	}

	return ob_get_clean();
}
