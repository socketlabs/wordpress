<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://socketlabs.com
 * @since      1.0.0
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/public
 * @author     SocketLabs Dev Team <support@socketlabs.com>
 */
class Socketlabs_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $socketlabs    The ID of this plugin.
	 */
	private $socketlabs;

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
	 * @param      string    $socketlabs       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $socketlabs, $version ) {

		$this->socketlabs = $socketlabs;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Socketlabs_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Socketlabs_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->socketlabs, plugin_dir_url( __FILE__ ) . 'css/socketlabs-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Socketlabs_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Socketlabs_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->socketlabs, plugin_dir_url( __FILE__ ) . 'js/socketlabs-public.js', array( 'jquery' ), $this->version, false );

	}

}
