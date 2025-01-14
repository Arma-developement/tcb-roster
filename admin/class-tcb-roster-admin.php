<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Tcb_Roster
 * @subpackage Tcb_Roster/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tcb_Roster
 * @subpackage Tcb_Roster/admin
 * @author     Your Name <email@example.com>
 */
class Tcb_Roster_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/tcb-roster-admin-display.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/tcb-roster-admin-edit-user-profile.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/tcb-roster-admin-post-to-discord.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/tcb-roster-admin-post-to-discordDM.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/tcb-roster-admin-hide-in-menu.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/tcb-roster-admin-hide-create-post.php';


		add_action( 'edit_user_profile', 'tcb_roster_admin_edit_user_profile' );
		add_action( 'add_meta_boxes', 'add_hide_in_menu_editor_field' );
		add_action( 'save_post', 'save_hide_in_menu_selector' );
		add_action( 'init', 'tcb_roster_admin_hide_create_post' );

		add_filter ( 'wp_nav_menu_objects', 'filter_draft_pages_from_menu', 10, 2 );
	}

	/**
	 * Register the stylesheets for the admin area.
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
		
		// Nick - comment out css
		// 	wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tcb-roster-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tcb-roster-admin.js', array( 'jquery' ), $this->version, false );

	}

}
