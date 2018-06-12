<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://socketlabs.com
 * @since      1.0.0
 *
 * @package    Socketlabs
 * @subpackage Sl_SocketlabsSignup/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Socketlabs
 * @subpackage Socketlabs/includes
 * @author     Your Name <info@socketlabs.com>
 */
class Socketlabs {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Socketlabs_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		$this->plugin_name = SOCKETLABS_OPTION_GROUP; //So happens to be the same as the option group name.
		
		register_setting( SOCKETLABS_OPTION_GROUP, $this->plugin_name);
		
		if ( defined( 'SOCKETLABS_VERSION' ) ) {
			$this->version = SOCKETLABS_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->api_status_manager = new Socketlabs_Api_Status();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Socketlabs_Loader. Orchestrates the hooks of the plugin.
	 * - Socketlabs_i18n. Defines internationalization functionality.
	 * - Socketlabs_Admin. Defines all hooks for the admin area.
	 * - Socketlabs_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-socketlabs-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-socketlabs-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-socketlabs-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-socketlabs-public.php';
		
		/**
		 * The api status constants
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-socketlabs-api-status.php';
		
		/**
		 * Service that will assemble and send an injection api message given a wordpress email message
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-socketlabs-mailer.php';
		
		/**
		 * Helper functions for file managment
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-socketlabs-io.php';
		
		
		$this->loader = new Socketlabs_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Socketlabs_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Socketlabs_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Socketlabs_Admin( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Socketlabs_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sl_Signup_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the SocketLabs api key.
	 *
	 * @since     1.0.0
	 * @return    string
	 */
	public static function get_api_key(){
		$options = get_option( SOCKETLABS_OPTION_GROUP );
		return isset($options[SOCKETLABS_API_KEY]) ? $options[SOCKETLABS_API_KEY] : '';
	}

	/**
	 * Retrieve the server id.
	 *
	 * @since     1.0.0
	 * @return    number
	 */
	public static function get_server_id(){
		$options = get_option( SOCKETLABS_OPTION_GROUP );
		return isset($options[SOCKETLABS_SERVER_ID]) ? $options[SOCKETLABS_SERVER_ID] : '';
	}

	/**
	 * Retrieve the the api status.
	 *
	 * @since     1.0.0
	 * @return    string 
	 */
	public static function get_api_status(){
		
		//Check to see if credentials are set
		$serverId = Socketlabs::get_server_id();
		$apiKey = Socketlabs::get_api_key();

		if(!defined("SOCKETLABS_API_STATUS") && (empty($serverId) || empty($apiKey))){
			define("SOCKETLABS_API_STATUS", Socketlabs_Api_Status::$NO_CREDENTIALS);
		}

		//Attempt an api call with the available credentials
        if(!defined("SOCKETLABS_API_STATUS")){
            $payload = (object) array(
                "ServerId" => $serverId,
                "ApiKey"=> $apiKey,
                "Messages"=> array((object)array())
			);

            $response = wp_remote_post( SOCKETLABS_INJECTION_URL, array(
                'method' => 'POST',
				'body' => json_encode($payload),
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept' => 'application/json'
				)
			));
			
            if ( is_wp_error( $response ) ) {
				define("SOCKETLABS_API_STATUS", Socketlabs_Api_Status::$NETWORK_ERROR);
            } else {
				$jsonResponse = json_decode($response['body']);

				if(isset($jsonResponse->ErrorCode)){
					$errorCode = $jsonResponse->ErrorCode;
					switch(strtolower($errorCode)){
						case "invalidauthentication":
						define("SOCKETLABS_API_STATUS", SocketLabs_Api_Status::$BAD_CREDENTIALS);
						break;
						case "novalidrecipients":
						define("SOCKETLABS_API_STATUS", Socketlabs_Api_Status::$SUCCESS);
						break;
						default : 
						define("SOCKETLABS_API_STATUS", $errorCode);
					}
				}
				else{
					define("SOCKETLABS_API_STATUS", SocketLabs_Api_Status::$UNKNOWN);
				}
			}
		}
        return SOCKETLABS_API_STATUS;    
    }
}
