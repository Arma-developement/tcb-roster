<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Tcb_Roster
 * @subpackage Tcb_Roster/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tcb_Roster
 * @subpackage Tcb_Roster/public
 * @author     Your Name <email@example.com>
 */
class Tcb_Roster_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Refactored code base 2025.
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/application.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/attendance.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/commendations.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/duties.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/json-sync.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/mission.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/mission-admin.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/mission-briefing.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/report.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/service-record.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/slotting.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/status.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/training.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/partials/user-profile.php';

		// Original code base from 2022.
		require_once plugin_dir_path( __DIR__ ) . '../action-scheduler/action-scheduler.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_register_script( 'tcb_roster_public_rsvp_register', plugin_dir_url( __FILE__ ) . 'js/tcb-roster-public-rsvp-register.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			'tcb_roster_public_rsvp_register',
			'rsvp_localize',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'ajax_nounce' => wp_create_nonce( 'attendance_roster_update_nounce' ),
			)
		);
		wp_enqueue_script( 'tcb_roster_public_rsvp_register' );

		wp_register_script( 'tcb_roster_public_slotting_register', plugin_dir_url( __FILE__ ) . 'js/tcb-roster-public-slotting-register.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			'tcb_roster_public_slotting_register',
			'slotting_localize',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'ajax_nounce' => wp_create_nonce( 'attendance_slotting_update_nounce' ),
			)
		);
		wp_enqueue_script( 'tcb_roster_public_slotting_register' );
	}
}
