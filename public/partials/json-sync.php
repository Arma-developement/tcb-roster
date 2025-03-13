<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

add_filter( 'acf/settings/save_json', 'tcbp_public_json_save_point_1', 10, 1 );

/**
 * Syncs the roster data from a JSON file located at the specified path.
 */
function tcbp_public_json_save_point_1() {
	return plugin_dir_path( __DIR__ ) . 'acf-json';
}

add_filter( 'acf/settings/load_json', 'tcbp_public_json_load_point', 10, 1 );

/**
 * Loads the JSON data from the specified paths.
 *
 * @param array $paths An array of file paths to load JSON data from.
 *
 * @return array
 */
function tcbp_public_json_load_point( $paths ) {
	// append path.
	$paths[] = plugin_dir_path( __DIR__ ) . 'acf-json';
	return $paths;
}
