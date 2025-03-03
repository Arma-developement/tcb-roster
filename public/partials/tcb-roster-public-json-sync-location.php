<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * Syncs the roster data from a JSON file located at the specified path.
 */
function tcb_roster_public_json_save_point_1() {
	return plugin_dir_path( __DIR__ ) . 'acf-json';
}


/**
 * Syncs the roster data from a JSON file located at the specified path.
 */
function tcb_roster_public_json_save_point_2() {
	return plugin_dir_path( __DIR__ ) . 'acf-json';
}


/**
 * Loads the JSON data from the specified paths.
 *
 * @param array $paths An array of file paths to load JSON data from.
 *
 * @return array
 */
function tcb_roster_public_json_load_point( $paths ) {
	// append path.
	$paths[] = plugin_dir_path( __DIR__ ) . 'acf-json';
	return $paths;
}
