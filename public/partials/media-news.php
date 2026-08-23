<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: media-news.php
 * Description: Handles the "submit media news" front-end feature - officers/SNCOs/admins can
 * submit a YouTube video link, title, short description, and associated event, which becomes a
 * published "Media" category news post with an automatically-derived video thumbnail.
 */

add_shortcode( 'tcbp_public_submit_media_news', 'tcbp_public_submit_media_news' );

/**
 * Renders the "submit media news" form. Restricted to officers, SNCOs, and administrators.
 */
function tcbp_public_submit_media_news() {

	$allowed_roles = array( 'officer', 'snco', 'administrator' );
	if ( ! array_intersect( $allowed_roles, wp_get_current_user()->roles ) ) {
		return;
	}

	ob_start();

	echo '<div class="tcb_submit_media_news">';

	acfe_form(
		array(
			'name' => 'submit_media_news',
		)
	);

	echo '</div>';

	return ob_get_clean();
}

add_action( 'acfe/form/submit/post/form=submit_media_news', 'tcbp_public_media_news_submission_callback', 10, 1 );

/**
 * Callback for the "submit media news" form. Builds the article's content (a clickable YouTube
 * thumbnail, the short description, and a link to the associated event), sets the Media category
 * and a sideloaded featured image, then publishes the post - which triggers the existing
 * tcbp_public_news_notify_discord_on_publish() Discord announcement automatically, the same way
 * it already does for mission news AAR posts (see mission-admin.php).
 *
 * @param int $post_id The ID of the post ACFE has already created (in draft status, via the
 *                      form's own Post Creation action) with the submitted field values saved.
 */
function tcbp_public_media_news_submission_callback( $post_id ) {

	$youtube_url = get_field( 'media_youtube_url', $post_id );
	$title       = get_field( 'media_title', $post_id );
	$description = get_field( 'media_description', $post_id );
	$event_id    = get_field( 'media_event', $post_id );

	$video_id = tcbp_public_extract_youtube_video_id( $youtube_url );

	$content = '';

	// The featured image (sideloaded below, from this same thumbnail) already gives the article
	// a large visual - a second copy of it in the content is redundant, so this is just a small
	// icon/button linking through to the video instead.
	if ( $video_id ) {
		// Sized via the .tcb_youtube_icon CSS class (theme stylesheet), not inline style/width/
		// height attributes - wp_update_post() runs post_content through KSES for users without
		// unfiltered_html (officers/SNCOs), which strips the style attribute (and possibly
		// width/height), leaving the icon to render at its native SVG size otherwise.
		$icon_url = plugins_url() . '/tcb-roster/images/icons/youtube.svg';
		$content .= '<p><a class="tcb_youtube_link" href="' . esc_url( $youtube_url ) . '"><img class="tcb_youtube_icon" src="' . esc_url( $icon_url ) . '" alt=""> Watch on YouTube</a></p>';
	}

	if ( $description ) {
		$content .= '<p>' . esc_html( $description ) . '</p>';
	}

	if ( $event_id ) {
		$content .= '<p>Captured during <a href="' . esc_url( get_permalink( $event_id ) ) . '">' . esc_html( get_the_title( $event_id ) ) . '</a></p>';
	}

	wp_update_post(
		array(
			'ID'            => $post_id,
			'post_title'    => $title,
			'post_content'  => $content,
			'post_status'   => 'publish',
			'post_category' => array( get_cat_ID( 'Media' ) ),
		)
	);

	// Sideload the YouTube thumbnail as the post's own featured image (rather than just linking
	// to it in the content), so it shows correctly in article listings/archives that rely on the
	// featured image - same reasoning as tcbp_public_mission_send_news()'s image handling.
	if ( $video_id ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$thumbnail_url = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
		$attachment_id = media_sideload_image( $thumbnail_url, $post_id, $title, 'id' );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}
}

/**
 * Extracts an 11-character YouTube video ID from any common URL format (watch?v=, youtu.be/,
 * embed/, shorts/), so the thumbnail can be built directly from YouTube's predictable image CDN
 * (https://img.youtube.com/vi/{id}/hqdefault.jpg) without needing an API key or oEmbed call.
 *
 * @param string $url The submitted YouTube URL.
 * @return string|false The video ID, or false if none could be found.
 */
function tcbp_public_extract_youtube_video_id( $url ) {
	if ( ! $url ) {
		return false;
	}
	if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $matches ) ) {
		return $matches[1];
	}
	return false;
}
