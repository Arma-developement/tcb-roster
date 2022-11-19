<?php

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-display.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-subsection.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-user-info.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-user-training.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-user-ribbons.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-user-edit-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-edit-training.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-edit-ribbons.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-edit-service-record.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-training.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-commendations.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-application-form-email-args.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-report-form-email-args.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-interview-form-email-args.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-application-list.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-application-view.php'; 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-interview-list.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-interview-view.php'; 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-attendance-roster.php'; 

		add_shortcode('tcb_roster_public_subsection', 'tcb_roster_public_subsection');
		add_shortcode('tcb_roster_public_user_info', 'tcb_roster_public_user_info');
		add_shortcode('tcb_roster_public_user_training', 'tcb_roster_public_user_training');
		add_shortcode('tcb_roster_public_user_ribbons', 'tcb_roster_public_user_ribbons');
		add_shortcode('tcb_roster_public_user_edit_options', 'tcb_roster_public_user_edit_options');
		add_shortcode('tcb_roster_public_edit_training', 'tcb_roster_public_edit_training');
		add_shortcode('tcb_roster_public_edit_ribbons', 'tcb_roster_public_edit_ribbons');
		add_shortcode('tcb_roster_public_edit_service_record', 'tcb_roster_public_edit_service_record');
		add_shortcode('tcb_roster_public_training', 'tcb_roster_public_training');
		add_shortcode('tcb_roster_public_commendations', 'tcb_roster_public_commendations');
		add_shortcode('tcb_roster_public_application_list', 'tcb_roster_public_application_list');
		add_shortcode('tcb_roster_public_application_view', 'tcb_roster_public_application_view');
		add_shortcode('tcb_roster_public_interview_list', 'tcb_roster_public_interview_list');
		add_shortcode('tcb_roster_public_interview_view', 'tcb_roster_public_interview_view');

		// add_action( 'tribe-events-before-html', function( $file, $name, $template ) {
  		// 	echo 'Hello World';
		// }, 10, 3 );

		//add_action( 'tribe_events_single_event_before_the_content', function() { echo 'Hello World'; } );
		//add_action( 'tribe_events_single_event_before_the_content', 'attendanceRoster' );
		add_action( 'tribe_events_single_event_before_the_content', 'tcb_roster_public_attendance_roster' );

		add_filter('acfe/form/submit/email_args/action=application_form_email', 'tcb_roster_public_application_form_email_args', 10, 3);
		add_filter('acfe/form/submit/email_args/action=report_form_email', 'tcb_roster_public_report_form_email_args', 10, 3);
		add_filter('acfe/form/submit/email_args/action=interview_form_email', 'tcb_roster_public_interview_form_email_args', 10, 3);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tcb-roster-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tcb-roster-public.js', array( 'jquery' ), $this->version, false );

	}

	// public function acf_admin_show() {
	// 	return current_user_can('manage_options');
	// }
}
