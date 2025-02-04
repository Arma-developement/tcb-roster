<?php

function tcb_roster_public_mission_send_news ($postId, $type, $args, $form, $action) {
	
	$user = wp_get_current_user();

	// Early out for no user
	if (!$user->exists())
		return;

	// Early out for logged out users
	if(!is_user_logged_in()){
		return;
	}

	$userId = $user->ID;

	// Retrieve data
	$title = get_field('title', $postId);
	$brief_situation = get_field('brief_situation', $postId);
	$brief_mission = get_field('brief_mission', $postId);
	$post_op_summary = get_field('post_op_summary', $postId);
	$post_op_image = get_field('post_op_image', $postId);
	$post_op_secondary_image = get_field('post_op_secondary_image', $postId);

	// Build content
	$content = '<h2>Situation</h2>' . $brief_situation . '<h2>Mission</h2>' . $brief_mission;

	if ($post_op_summary != "") {
		$content .= '<h2>AAR</h2>' . $post_op_summary;
	}
	
	if ( !empty( $post_op_image ) ) {
		$content .= '<img src="' . esc_url($post_op_image['url']) . '" />';
	}

	if ($post_op_secondary_image != "") {
		$content .= '<img src="' . esc_url($post_op_secondary_image['url']) . '" />';
	}

	$new_post = array (
		'post_title' => 'After Action Report: ' . $title,
		'post_content' => $content,
		'post_status' => 'publish',
		'post_author' => $userId,
		'post_type' => 'post',
		'post_category' => array(get_cat_ID( 'After Action Report' ))
	);
	$new_post_id = wp_insert_post($new_post);

	if ($new_post_id) {
		// Add post thumbnail
		$image_id = $post_op_image['ID'];
		if ($image_id)
			set_post_thumbnail( $new_post_id, $image_id );
	}
}
