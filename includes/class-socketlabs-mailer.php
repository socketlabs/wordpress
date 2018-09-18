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
        
        const contact_regex = '/([^"]*)["]?\s*<(.*)>.*$/';
        
        private $api_url;
        private $content_type = "text/plain";

        private $api_message = array(
            "To"=> array(),
            "From"=> null,
            "Subject"=> null,
            "TextBody"=> null,
            "HtmlBody"=> null,
            "ApiTemplate"=> null,
            "MailingId"=> "wordpress",
            "MessageId"=> null,
            "Charset"=> null,
            "ReplyTo"=>null,
            "Cc"=> array(),
            "Bcc"=>array(),
            "CustomHeaders"=> array(),
            "Attachments"=> array()
        );

         /**
         * @since    1.0.0
         * @access   private
         * @param    string|array         $to            A single or collection of recipients.
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
                $to = $atts['to'];
                if(is_string($to)){
                    $to = explode( ',', $to );
                }
                if(is_array($to)){
                    foreach ($to as $recipient){
                        $this->api_message["To"][] = (object)array("EmailAddress"=> $recipient);
                    }
                }
            }
        
            if ( isset( $atts['subject'] ) ) {
                $this->api_message["Subject"] = $atts['subject'];
            }
        
            if ( isset( $atts['message'] ) ) {
                $this->message = $atts['message'];
            }
        
            if ( isset( $atts['attachments'] ) ) {
                $attachments = $atts['attachments'];
            }
            if ( ! is_array( $attachments ) ) {
                $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
            }
            if ( ! empty( $attachments ) ) {
                foreach ( $attachments as $attachment ) {
                    try {
                        $this->addAttachment( $attachment );
                    } catch ( Exception $e ) {
                        continue;
                    }
                }
            }
            
            $headers = $atts['headers'];
            if ( empty( $headers ) ) {
                $headers = array();
            } else {
                if ( ! is_array( $headers ) ) {
                    // Explode the headers out, so this function can take both
                    // string headers and an array of headers.
                    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
                } else {
                    $tempheaders = $headers;
                }
                $headers = array();
                // If it's actually got contents
                if ( ! empty( $tempheaders ) ) {
                    // Iterate through the raw headers
                    foreach ( (array) $tempheaders as $header ) {
                        if ( strpos( $header, ':' ) === false ) {
                            if ( false !== stripos( $header, 'boundary=' ) ) {
                                $parts    = preg_split( '/boundary=/i', trim( $header ) );
                                $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                            }
                            continue;
                        }
                        // Explode them out
                        list( $name, $content ) = explode( ':', trim( $header ), 2 );
    
                        // Cleanup crew
                        $name    = trim( $name );
                        $content = trim( $content );
    
                        switch ( strtolower( $name ) ) {
                            // Mainly for legacy -- process a From: header if it's there
                            case 'from':
            
                                $bracket_pos = strpos( $content, '<' );
                                if ( $bracket_pos !== false ) {
                                    // Text before the bracketed email is the "From" name.
                                    if ( $bracket_pos > 0 ) {
                                        $from_name = substr( $content, 0, $bracket_pos - 1 );
                                        $from_name = str_replace( '"', '', $from_name );
                                        $from_name = trim( $from_name );
                                    }
    
                                    $from_email = substr( $content, $bracket_pos + 1 );
                                    $from_email = str_replace( '>', '', $from_email );

                        
                                    $contact_match;
                                    preg_match(self::contact_regex, $header, $contact_match);

                                    if(isset($contact_match[2])){
                                        $this->apply_from(isset($contact_match[1]) ? $contact_match[1] : "", $contact_match[2]);
                                    }
                                    // Avoid setting an empty $from_email.
                                } elseif ( '' !== trim( $content ) ) {
                                    $this->apply_from("", trim( $content ));
                                }
    
                                break;
                            case 'content-type':
                                if ( strpos( $content, ';' ) !== false ) {
                                    list( $type, $charset_content ) = explode( ';', $content );
                                    $this->content_type                   = trim( $type );
                                    if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                        $this->api_message["Charset"] = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                                    } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                        $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                        $this->api_message["Charset"] = apply_filters( 'wp_mail_charset', "" );
                                    }
    
                                    // Avoid setting an empty $content_type.
                                } elseif ( '' !== trim( $content ) ) {
                                    $this->api_message["Charset"] = apply_filters( 'wp_mail_charset', trim( $content ) );
                                }
                                break;
                            case 'cc':
                                $cc = explode( ',', $content );
                                foreach ($cc as $recipient){
                                    $this->api_message["Cc"][] = $this->create_contact($recipient);
                                }
                                break;
                            case 'bcc':
                                $bcc = explode( ',', $content );
                                foreach ($bcc as $recipient){
                                    $this->api_message["Bcc"][] = $this->create_contact($recipient);
                                }
                                break;
                            case 'reply-to':
                                $this->api_message["ReplyTo"] = $this->create_contact($content);
                                break;
                            default:
                                // Add it to our grand headers array
                                $this->api_message["CustomHeaders"][] = (object)array(
                                    "Name" => $name,
                                    "Value" => trim( $content ),
                                );                                
                                break;
                        }
                    }
                }
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
         * A helper function that is creates a contact suitable for the api message
         *
         * @since    1.0.0
         * @access   private
         * @param    string      $header        Any single header item
         * @return   void
         */
        private function create_contact($value){
            $contact_match;
            preg_match(self::contact_regex, $value, $contact_match);
            if( preg_match(self::contact_regex, $value, $contact_match) && isset($contact_match[2])){
                return (object)array(
                    "FriendlyName" => isset($contact_match[1]) ? $contact_match[1] : null,
                    "EmailAddress" => $contact_match[2]
                );
            }
            return (object)array(
                "FriendlyName" => null,
                "EmailAddress" => $value
            );
        }

         /**
         * A helper function that is assembles the api message 
         *
         * @since    1.0.0
         * @return   void
         */
        private function create_message(){

            /**
             * Filters the wp_mail() content type.
             *
             * @since 1.0.0
             *
             * @param string $content_type Default wp_mail() content type.
             */
            $this->content_type = apply_filters( 'wp_mail_content_type', $this->content_type );

            // Set whether it's plain text, depending on $content_type
            if ( 'text/plain' == $this->content_type ){
                $this->api_message["TextBody"] = $this->message;
                $this->api_message["HtmlBody"] = null;
            }
            else{
                $this->api_message["TextBody"] = null;
                $this->api_message["HtmlBody"] = $this->message;
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
     * Add an attachment from a path on the filesystem.
     * Never use a user-supplied path to a file!
     * Returns false if the file could not be found or read.
     * @since      1.0.7
     * @param string $path Path to the attachment.
     * @param string $name Overrides the attachment name.
     * @param string $type File extension (MIME) type.
     * @return boolean
     */
    private function addAttachment($path, $name = '', $type = '')
    {
        try {
            if (!@is_file($path)) {
                throw new Exception("Cannot open file: " . $path);
            }
            // If a MIME type is not specified, try to work it out from the file name
            if ($type == '') {
                $type = Socketlabs_IO::filenameToType($path);
            }
            $filename = basename($path);
            if ($name == '') {
                $name = $filename;
            }
            $this->api_message["Attachments"][] = (object)array(
                "Name" => $name,
                "Content" => Socketlabs_IO::encodeFile($path),
                "ContentType" => $type
            );
        } catch (Exception $exc) {
            return false;
        }
        return true;
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