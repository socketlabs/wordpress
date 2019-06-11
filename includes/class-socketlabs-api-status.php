<?php

/**
 * All of the possible api status possibilities
 *
 * @link       http://socketlabs.com
 * @since      1.0.0
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/includes
 */

/**
 * Maintain the status of the SocketLabs injection api.
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/includes
 * @author     SocketLabs Dev Team <support@socketlabs.com>
 */

class Socketlabs_Api_Status {
    
    public static $SUCCESS = "Success";
    public static $NETWORK_ERROR = "Your firewall or network settings may be blocking SocketLabs from connecting to its API...";
    public static $NO_CREDENTIALS = "Mailings will not be handled by this plugin until the server id and api key have both been configured.";
    public static $BAD_CREDENTIALS = "The supplied server id or api key is not correct.";
    public static $UNKNOWN = "There was an unknown error.";
    
}
