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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-edit-training.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-edit-ribbons.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-edit-service-record.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-training.php';
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-training-editor-user-source.php';
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-training-editor-user-output.php';
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/tcb-roster-public-acf-admin-show.php';

		add_shortcode('tcb_roster_public_subsection', 'tcb_roster_public_subsection');
		add_shortcode('tcb_roster_public_user_info', 'tcb_roster_public_user_info');
		add_shortcode('tcb_roster_public_user_training', 'tcb_roster_public_user_training');
		add_shortcode('tcb_roster_public_user_ribbons', 'tcb_roster_public_user_ribbons');
		add_shortcode('tcb_roster_public_edit_training', 'tcb_roster_public_edit_training');
		add_shortcode('tcb_roster_public_edit_ribbons', 'tcb_roster_public_edit_ribbons');
		add_shortcode('tcb_roster_public_edit_service_record', 'tcb_roster_public_edit_service_record');
		add_shortcode('tcb_roster_public_training', 'tcb_roster_public_training');

		//add_filter('acfe/form/load/user_id/form=user-training-editor', 'tcb_roster_public_training_editor_user_source', 10, 3);	
		//add_filter('acfe/form/output/user/form=user-training-editor', 'tcb_roster_public_training_editor_user_output', 10, 6);	
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
