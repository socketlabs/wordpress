<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://socketlabs.com
 * @since      1.0.0
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/admin
 * @author     Socketlabs Dev Team <info@socketlabs.com>
 */
class Socketlabs_Admin {

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
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Sl_Signup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sl_Signup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/socketlabs-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Sl_Signup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sl_Signup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/socketlabs-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add the admin submenu options.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_submenu_page(
			get_admin_page_parent(), 
			'SocketLabs Options', 
			'SocketLabs', 
			'administrator', 
			$this->plugin_name, 
			array($this, 'create_admin_page') );
	}

	/**
	 * Create the admin UI.
	 *
	 * @since    1.0.0
	 */
	public function create_admin_page()
	{
		require_once plugin_dir_path( __FILE__ ) . 'partials/socketlabs-admin-display.php';
	}

}
?>
