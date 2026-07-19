<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * File: menu.php
 * Description: Handles the code associated with the admin menus
 */

add_action( 'add_meta_boxes', 'add_hide_in_menu_editor_field' );

/**
 * Adds a custom field to the editor for hiding items in the menu.
 *
 * This function is used to add a custom field in the WordPress editor
 * that allows users to hide specific items in the menu.
 *
 * @return void
 */
function add_hide_in_menu_editor_field() {
	add_meta_box( 'hide_in_menu_selector', 'Page menu option', 'hide_in_menu_editor_callback', 'page', 'normal', 'high' );
}

/**
 * Callback function to hide the editor in the menu.
 *
 * @param WP_Post $post The post object.
 */
function hide_in_menu_editor_callback( $post ) {
	global $post;
	$is_hidden = get_post_meta( $post->ID, 'hide_in_menu_selector', true );
	?>
	<label>
		<input type="checkbox" name="hide_in_menu_selector" value="1" <?php echo ( $is_hidden ? 'checked="checked"' : '' ); ?>/> Hide in menu page selector ?
	</label>
	<?php
}

add_action( 'save_post', 'save_hide_in_menu_selector' );

/**
 * Save the hide in menu selector for a post.
 *
 * @param int $post_id The ID of the post.
 */
function save_hide_in_menu_selector( $post_id ) {
	// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
	// update_post_meta( $post_id, 'hide_in_menu_selector', $_POST['hide_in_menu_selector']==="1");
	// .
}

add_filter( 'wp_nav_menu_objects', 'filter_draft_pages_from_menu', 10, 2 );

/**
 * Filters draft pages from the menu.
 *
 * @param array $items The menu items.
 * @return array The filtered menu items.
 */
function filter_draft_pages_from_menu( $items ) {
	foreach ( $items as $index => $obj ) {

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		// if ( !is_user_logged_in() && get_post_meta( $obj->object_id, 'hide_in_menu_selector', true ) ) {
		// .
		if ( get_post_meta( $obj->object_id, 'hide_in_menu_selector', true ) ) {
				unset( $items[ $index ] );
		}
	}
	return $items;
}

// Disable WordPress Administration Email verification Screen
//add_filter( 'admin_email_check_interval', '__return_false' );

add_action( 'user_profile_update_errors', 'tcb_roster_admin_allow_empty_user_email' );

/**
 * Removes the "empty email" validation error from the edit-user screen so admins can save a
 * user's profile even when that user has no email address (e.g. accounts created via Steam
 * login, since Steam's API doesn't provide one).
 *
 * @param WP_Error $errors Validation errors for the profile update, passed by reference by core.
 */
function tcb_roster_admin_allow_empty_user_email( $errors ) {
	$errors->remove( 'empty_email' );
}
