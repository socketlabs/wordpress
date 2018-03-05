<?php
/**
 * SocketLabs mailing manager.
 *
 * @link       http://socketlabs.com
 * @since      1.0.0
 *
 * @package    Socketlabs
 * @subpackage Socketlabs/includes
 */

/**
 * SocketLabs mailing manager.
 *
 * This class will assemble the SocketLabs api message, and send the message.
 *
 * @since      1.0.0
 * @package    Socketlabs
 * @subpackage Socketlabs/includes
 * @author     SocketLabs Dev Team <info@socketlabs.com>
 */
class Socketlabs_Mailer{
        
        const header_regex = "/^([\w-]+):\s*(.*)$/";
        const charset_regex = "/charset=(.+)$/";
        const contact_regex = "/^["|']?([^"|']*)["|']?\s*<(.*)>.*$/";
        
        private $api_url;
        private $to;
        private $subject;
        private $message;
        private $headers;
        private $attachments;

        private $api_message = array(
            "To"=> array(),
            "From"=> null,
            "Subject"=> null,
            "TextBody"=> null,
            "HtmlBody"=> null,
            "ApiTemplate"=> null,
            "MailingId"=> null,
            "MessageId"=> null,
            "Charset"=> null,
            "ReplyTo"=>null,
            "Cc"=> array(),
            "Bcc"=>array(),
            "CustomHeaders"=> array(),
            "Attatchments"=> array()
        );

         /**
         * @since    1.0.0
         * @access   private
         * @param    string|array         $to            A single or colletion of recipients.
         * @param    string               $subject       The subject of the email.
         * @param    string               $message       The email message (or body).
         * @param    string|array         $headers       A single header or a collection of headers.
         * @param    array                $attachments   A collection of attachments.
         */
        function __construct ($to, $subject, $message, $headers, $attachments){
            
            $this->api_url = defined("SOCKETLABS_INJECTION_URL") ? SOCKETLABS_INJECTION_URL : "https://inject.socketlabs.com/api/v1/email";
            
            /**
             * Filters the wp_mail() arguments.
             *
             * @since 1.0.0
             *
             * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
             *                    subject, message, headers, and attachments values.
             */
            $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
    
            if ( isset( $atts['to'] ) ) {
                $this->to = $atts['to'];
            }
        
            if ( isset( $atts['subject'] ) ) {
                $this->subject = $atts['subject'];
            }
        
            if ( isset( $atts['message'] ) ) {
                $this->message = $atts['message'];
            }
        
            if ( isset( $atts['headers'] ) ) {
                $this->headers = $atts['headers'];
            }
        
            if ( isset( $atts['attachments'] ) ) {
                $this->attachments = $atts['attachments'];
            }

            $this->create_message();
        }


        /**
         * A helper function that applies from information to the $api_message 
         *
         * @since    1.0.0
         * @access   private
         * @param    string      $from_name        From address friendly name
         * @param    string      $from_email       From email address
         * @return   void
         */
        private function apply_from($from_name, $from_email){

            /**
             * Filters the name to associate with the "from" email address.
             *
             * @since 1.0.0
             *
             * @param string $from_name Name associated with the "from" email address.
             */
            $from_name_filtered = apply_filters( 'wp_mail_from_name', $from_name );

            /**
             * Filters the email address to send from.
             *
             * @since 1.0.0
             *
             * @param string $from_email Email address to send from.
             */
            $from_email_filtered = apply_filters( 'wp_mail_from', $from_email );

            $from = array();
            if($from_name_filtered || $from_name){
                $from["FriendlyName"] = $from_name_filtered ? $from_name_filtered : $from_name;
            }
            $from["EmailAddress"] = $from_email_filtered ? $from_email_filtered : $from_email;

            $this->api_message["From"] = (object)$from;
                
        }
        
        /**
         * A helper function that is parses a single header and applies header to the $api_message
         *
         * @since    1.0.0
         * @access   private
         * @param    string      $header        Any single header item
         * @return   void
         */
        private function apply_header($header){

            $header_matches;
            preg_match(self::header_regex, $header, $header_matches);
            
            if(isset($header_matches[1]) && isset($header_matches[2])){
                
                $name = trim($header_matches[1][0]);
                $value = trim($header_matches[2][0]);
                
                switch(strtolower($name)){
                    case "content-type":
                        //Determine content type and attach content accordingly
                        //Determine charset and set  $api_message["Charset"] to the value

                        if ( strpos( $value, ';' ) !== false ) {
							list( $type, $charset_content ) = explode( ';', $value );
							$content_type = trim( $type );
							if ( false !== stripos( $charset_content, 'charset=' ) ) {
								$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
							} elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
								$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
								$charset = '';
							}

						// Avoid setting an empty $content_type.
						} elseif ( '' !== trim( $value ) ) {
							$content_type = trim( $value );
						}

                        /**
                         * Filters the wp_mail() content type.
                         *
                         * @since 1.0.0
                         *
                         * @param string $content_type Default wp_mail() content type.
                         */
                        $content_type = apply_filters( 'wp_mail_content_type', $content_type );

                        // Set whether it's plaintext, depending on $content_type
                        if ( 'text/html' == $content_type ){
                            $this->api_message["TextBody"] = null;
                            $this->api_message["HtmlBody"] = $this->content;
                        }
                        else{
                            $this->api_message["TextBody"] = $this->content;
                            $this->api_message["HtmlBody"] = null;
                        }

                        /**
                         * Filters the default wp_mail() charset.
                         *
                         * @since 1.0.0
                         *
                         * @param string $charset Default email charset.
                         */
                        $this->api_message["Charset"] = apply_filters( 'wp_mail_charset', $charset );

                        break;
                    case "cc":
                        $contact_match;
                        preg_match(self::contact_regex, $value, $contact_match);
                        if(isset($contact_match[1][0]) && isset($contact_match[2][0])){
                            $this->api_message["Cc"][] = (object)array(
                            "FriendlyName" => $contact_match[1][0],
                            "EmailAddress" => $contact_match[2][0],
                            );
                        }
                        break;
                    case "bcc":
                        $contact_match;
                        preg_match(self::contact_regex, $value, $contact_match);
                        if(isset($contact_match[1][0]) && isset($contact_match[2][0])){
                            $this->api_message["Bcc"][] = (object)array(
                            "FriendlyName" => $contact_match[1][0],
                            "EmailAddress" => $contact_match[2][0],
                            );
                        }
                        break;
                    case "from":
                        $contact_match;
                        preg_match(self::contact_regex, $value, $contact_match);
                        if(isset($contact_match[1][0]) && isset($contact_match[2][0])){
                            $this->apply_from($contact_match[1][0], $contact_match[2][0]);
                        }
                        break;
                    default :
                        $this->api_message["CustomHeaders"][] = (object)array(
                            "Name" => $name,
                            "Value" => $value,
                        );
                }
            }
        }

         /**
         * A helper function that is assembles the api message 
         *
         * @since    1.0.0
         * @return   void
         */
        private function create_message(){
            $this->api_message["Subject"] = $this->subject;

            //Assemble "To" property
            if(is_string($this->to)){
                $this->api_message["To"][] = (object)array("EmailAddress"=> $this->to);
            }
            else if(is_array($this->to)){
                foreach ($this->to as $recipient){
                    $this->api_message["To"][] = (object)array("EmailAddress"=> $recipient);
                }
            }

            //Assemble "CustomHeaders" property
            if(is_string($this->headers)){
                $this->apply_header($this->headers);
            }
            else if(is_array($this->headers)){
                foreach($this->headers as $header){
                    $this->apply_header($header);
                }
            }

            //Set the TextBody if no content was set
            if($this->api_message["TextBody"] == null && $this->api_message["HtmlBody"] == null){
                $this->api_message["TextBody"] = $this->message;
            }

            /* If we don't have an email from the input headers default to wordpress@$sitename
            * Some hosts will block outgoing mail from this address if it doesn't exist but
            * there's no easy alternative. Defaulting to admin_email might appear to be another
            * option but some hosts may refuse to relay mail from an unknown domain. See
            * https://core.trac.wordpress.org/ticket/5007.
            */

            if ( $this->api_message["From"] == null ) {
                $from_name = 'WordPress';
                // Get the site domain and get rid of www.
                $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                    $sitename = substr( $sitename, 4 );
                }

                $from_email = 'wordpress@' . $sitename;

                $this->apply_from($from_name, $from_email);
            }
        }

         /**
         * Send the assembled email message 
         * collection.
         *
         * @since    1.0.0
         * @return   object
         */
        public function send(){
            
            $payload = (object) array(
                "ServerId" => Socketlabs::get_server_id(),
                "ApiKey"=> Socketlabs::get_api_key(),
                "Messages"=> array($this->api_message)
            );

            return wp_remote_post( $this->api_url, array(
                'method' => 'POST',
                'body' => json_encode($payload),
                'headers' => 'Content-Type: application/json'
            ));
        }
    }
?>