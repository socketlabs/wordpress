<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://socketlabs.com
 * @since             1.0.4
 * @package           SocketLabs
 *
 * @wordpress-plugin
 * Plugin Name:       SocketLabs
 * Plugin URI:        https://github.com/socketlabs/wordpress
 * Description:       Send emails using your SocketLabs account.
 * Version:           1.0.7
 * Author:            SocketLabs
 * Author URI:        https://socketlabs.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       socketlabs
 * Domain Path:       /languages
 */
 
 
 /*
The SocketLabs WordPress Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
The SocketLabs WordPress Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with The SocketLabs WordPress Plugin. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SOCKETLABS_VERSION', '1.0.7');
define("SOCKETLABS_OPTION_GROUP", 'socketlabs');
define("SOCKETLABS_API_KEY", 'socketlabs_api_key');
define("SOCKETLABS_SERVER_ID", 'socketlabs_server_id');
define("SOCKETLABS_INJECTION_URL", "https://inject.socketlabs.com/api/v1/email");

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_socketlabs() {
	delete_option(SOCKETLABS_OPTION_GROUP);
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-socketlabs-activator.php';
	Socketlabs_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-socketlabs-deactivator.php
 */
function deactivate_socketlabs() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-socketlabs-deactivator.php';
	Socketlabs_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_socketlabs' );
register_deactivation_hook( __FILE__, 'deactivate_socketlabs' );
//register_uninstall_hook( __FILE__, 'uninstall_socketlabs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-socketlabs.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_socketlabs() {
	$plugin = new Socketlabs();
	$plugin->run();


	$api_status = Socketlabs::get_api_status();
	if($api_status == Socketlabs_Api_Status::$SUCCESS && !function_exists("wp_mail")){
		function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {

            $socketlabs_mailer = new Socketlabs_Mailer($to, $subject, $message, $headers, $attachments);
            $response = $socketlabs_mailer->send();

            if ( is_wp_error( $response ) ) {
			   $error_message = $response->get_error_message();
               return false;
            } else {
               return true;
            }
        }
	}
}
run_socketlabs();

