<?php

function tcb_roster_public_json_save_point_1($path) {
	return plugin_dir_path( dirname( __FILE__ ) ) . 'acf-json';
}

function tcb_roster_public_json_save_point_2($path, $form) {
	return plugin_dir_path( dirname( __FILE__ ) ) . 'acf-json';
}

function tcb_roster_public_json_load_point($paths) {
    // append path
    $paths[] = plugin_dir_path( dirname( __FILE__ ) ) . 'acf-json';
    return $paths;
}