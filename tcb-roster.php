<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar
/**
 * Plugin Name: 3CB Roster
 * Plugin URI: https://github.com/Arma-developement/tcb-roster
 * Description: Design by Nick at Intention and Lifetap
 * Version: 1.3.65
 * Author: Lifetap / Badger
 * Author URI: https://github.com/Arma-developement/tcb-roster
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: roster
 * Domain Path: arma
 *
 * GitHub Plugin URI: https://github.com/Arma-developement/tcb-roster
 * Primary Branch: main
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'TCB_ROSTER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 */
function activate_tcb_roster() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-tcb-roster-activator.php';
	Tcb_Roster_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_tcb_roster() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-tcb-roster-deactivator.php';
	Tcb_Roster_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tcb_roster' );
register_deactivation_hook( __FILE__, 'deactivate_tcb_roster' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tcb-roster.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_tcb_roster() {
	$root          = isset( $_SERVER['DOCUMENT_ROOT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) : '';
	$env_file_path = "$root/tcb.env";

	if ( is_file( $env_file_path ) ) {
		$file = new \SplFileObject( $env_file_path );

		// Loop until we reach the end of the file.
		while ( ! $file->eof() ) {
			// Get the current line value, trim it and save by putenv.
			$line = trim( $file->fgets() );
			if ( strpos( $line, '=' ) !== false ) {
				list( $name, $value ) = explode( '=', $line, 2 );
				$_ENV[ $name ]        = $value;
			}
		}
	}

	$plugin = new Tcb_Roster();
	$plugin->run();
}
run_tcb_roster();
