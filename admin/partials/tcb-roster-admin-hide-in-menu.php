<?php

function add_hide_in_menu_editor_field() {
    add_meta_box('hide_in_menu_selector','Page menu option', 'hide_in_menu_editor_callback', 'page', 'normal', 'high');
 }
 
 function hide_in_menu_editor_callback( $post ) {
    global $post;
    $isHidden=get_post_meta( $post->ID, 'hide_in_menu_selector', true );
     ?>
     <label>
        <input type="checkbox" name="hide_in_menu_selector" value="1" <?php echo ($isHidden ? 'checked="checked"': '');?>/> Hide in menu page selector ?
     </label>
     <?php
 }
 
 function save_hide_in_menu_selector($post_id) { 
//    update_post_meta( $post_id, 'hide_in_menu_selector', $_POST['hide_in_menu_selector']==="1");
 }

function filter_draft_pages_from_menu ($items, $args) {
    foreach ($items as $index => $obj) {
        //if ( !is_user_logged_in() && get_post_meta( $obj->object_id, 'hide_in_menu_selector', true )) {
        if ( get_post_meta( $obj->object_id, 'hide_in_menu_selector', true )) {
                unset ($items[$index]);
        }
    }
    return $items;
}