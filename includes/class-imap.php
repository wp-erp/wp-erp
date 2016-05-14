<?php
namespace WeDevs\ERP;

/**
 * Incoming email reader class
 *
 * Instructions
 * =========================================================
 * sudo apt-get install php5-imap
 * sudo php5enmod imap
 * sudo service apache2 restart
 *
 * Host: {imap.gmail.com:993/imap/ssl}INBOX
 * Host: {imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX
 * Host: {imap.mail.yahoo.com:993/imap/ssl}INBOX
 * Host: {imap-mail.outlook.com:993/imap/ssl}INBOX
 * https://www.google.com/settings/security/lesssecureapps
 * =========================================================
 */
class Imap {
    /**
     * Mailbox
     */
    protected $mailbox;

    /**
     * Imap connection
     */
    protected $connection;

    /**
     * Class contsructor
     *
     * @param  string  $host
     * @param  string  $port (993|995)
     * @param  string  $username
     * @param  string  $password
     * @param  string  $encryption (ssl|tls|notls)
     * @param  boolean $cert
     *
     * @return void
     */
    public function __construct( $host, $port, $protocol, $username, $password, $encryption = 'ssl', $cert = false ) {
        set_time_limit( 3000 );

        $option = '';

        if ( $protocol ) {
            $option .= '/' . $protocol;
        }

        $option .= '/' . $encryption;

        if ( $cert ) {
            $option .= '/validate-cert';
        } else {
            $option .= '/novalidate-cert';
        }

        if ( preg_match( "/google|gmail/i", $host ) ) {
            $this->mailbox = "{" . $host . ":" . $port . $option . "}INBOX";
        } else {
            $this->mailbox = "{" . $host . ":" . $port . $option . "}";
        }

        if ( ! $this->is_extension_loaded() ) {
            die( 'IMAP extension could not loaded!' );
        }

        $this->connection = imap_open( $this->mailbox, $username, $password ) or die( 'Cannot connect to Email: ' . imap_last_error() );
    }

    /**
     * Determine the imap entension is loaded or try to load it dynamically
     *
     * @return boolean
     */
    public function is_extension_loaded() {
        if ( ! extension_loaded( 'imap' ) || ! function_exists( 'imap_open' ) ) {
            $prefix = ( PHP_SHLIB_SUFFIX == 'dll' ) ? 'php_' : '';
            $extension = $prefix . 'imap.' . PHP_SHLIB_SUFFIX;

            if ( function_exists( 'dl' ) ) {
                return dl( $extension );
            }

            return false;
        }

        return true;
    }

    /**
     * Open the inbox
     *
     * @param  string $mail_type (optional)
     *
     * @return array
     */
    public function open( $mail_type = 'UNSEEN' ) {
        $emails = imap_search( $this->connection, $mail_type );

        if ( $emails ) {
            rsort( $emails );

            return $emails;
        }

        return [];
    }

    /**
     * Get all emails with body & attachments
     *
     * @param  string $mail_type (optional)
     *
     * @return array
     */
    public function get_emails( $mail_type = 'UNSEEN' ) {
        $email_ids = $this->open( $mail_type );

        $emails = [];

        foreach ( $email_ids as $email_id ) {
            $emails[] = [
                'subject'     => $this->get_subject( $email_id ),
                'body'        => $this->get_body( $email_id ),
                'attachments' => $this->get_attachments( $email_id ),
                'headers'     => $this->get_header( $email_id ),
            ];
        }

        return $emails;
    }

    /**
     * Get email overview
     *
     * @param  int $email_id
     *
     * @return array
     */
    public function get_overview( $email_id ) {
        $overview = imap_fetch_overview( $this->connection, $email_id, 0 );

        return $overview;
    }

    /**
     * Get email header
     *
     * @param  int $email_id
     *
     * @return array
     */
    public function get_header( $email_id ) {
        $header = imap_header( $this->connection, $email_id );

        return $header;
    }

    /**
     * Get email subject
     *
     * @param  int $email_id
     *
     * @return string
     */
    public function get_subject( $email_id ) {
        $header = $this->get_header( $email_id );

        return $header->subject;
    }

    /**
     * Get email body
     *
     * @param  int $email_id
     *
     * @return text
     */
    public function get_body( $email_id ) {
        $body = $this->get_part( $email_id, "TEXT/HTML" );

        // if HTML body is empty, try getting text body
        if ( $body == "" ) {
            $body = $this->get_part( $email_id, "TEXT/PLAIN" );
        }

        return $body;
    }

    /**
     * Get email part for decoding email body
     *
     * @param  int $email_id
     * @param  int $mimetype
     * @param  int $structure
     * @param  int $part_number
     *
     * @return string
     */
    protected function get_part( $email_id, $mimetype, $structure = false, $part_number = false ) {
        if ( ! $structure ) {
            $structure = imap_fetchstructure( $this->connection, $email_id );
        }

        if ( $structure ) {
            if ( $mimetype == $this->get_mime_type( $structure ) ) {
                if ( ! $part_number ) {
                    $part_number = 1;
                }

                $text = imap_fetchbody( $this->connection, $email_id, $part_number );

                switch ( $structure->encoding ) {
                    case 3: return imap_base64( $text );
                    case 4: return imap_qprint( $text );

                    default: return $text;
               }
           }

            // multipart
            if ( $structure->type == 1 ) {
                foreach ( $structure->parts as $index => $sub_struct ) {
                    $prefix = "";
                    if ( $part_number ) {
                        $prefix = $part_number . ".";
                    }

                    $data = $this->get_part( $email_id, $mimetype, $sub_struct, $prefix . ( $index + 1 ) );

                    if ( $data ) {
                        return $data;
                    }
                }
            }
        }

        return '';
    }

    /**
     * Get mimetype by given email structure
     *
     * @param  array $structure
     *
     * @return string
     */
    protected function get_mime_type( $structure ) {
        $primary_mimetypes = [ "TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER" ];

        if ( $structure->subtype ) {
           return $primary_mimetypes[(int)$structure->type] . "/" . $structure->subtype;
        }

        return "TEXT/PLAIN";
    }

    /**
     * Get email attachments
     *
     * @param  int $email_id
     *
     * @return array
     */
    public function get_attachments( $email_id ) {
        $structure = imap_fetchstructure( $this->connection, $email_id );

        $attachments = [];

        /* if any attachment found... */
        if ( isset( $structure->parts ) && count( $structure->parts ) ) {
            for ( $i = 0; $i < count( $structure->parts ); $i++ ) {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename'      => '',
                    'attachment'    => '',
                );

                if ( $structure->parts[$i]->ifdparameters ) {
                    foreach ( $structure->parts[$i]->dparameters as $object ) {
                        if ( strtolower($object->attribute ) == 'filename' ) {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if ( $structure->parts[$i]->ifparameters ) {
                    foreach ( $structure->parts[$i]->parameters as $object ) {
                        if ( strtolower( $object->attribute ) == 'name' ) {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if ( $attachments[$i]['is_attachment'] ) {
                    $attachments[$i]['attachment'] = imap_fetchbody( $this->connection, $email_id, $i + 1 );

                    if ( $structure->parts[$i]->encoding == 3 ) {
                        $attachments[$i]['attachment'] = base64_decode( $attachments[$i]['attachment'] );
                    } else if ( $structure->parts[$i]->encoding == 4 ) {
                        $attachments[$i]['attachment'] = quoted_printable_decode( $attachments[$i]['attachment'] );
                    }
                }
            }
        }

        $filtered_attachments = [];
        foreach ( $attachments as $attachment ) {
            if ( $attachment['is_attachment'] ) {
                $filtered_attachments[] = $attachment;
            }
        }

        return $filtered_attachments;
    }

    /**
     * Download email attachments as zip
     *
     * @param  array $attachments
     *
     * @return void
     */
    public function download_attachments( $attachments ) {
        $zip_file = 'email-attachment.zip';

        $zip = new ZipArchive;
        $zip->open( $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE );

        foreach ( $attachments as $file ) {
            $zip->addFromString( $file['filename'], $file['attachment'] ) ;
        }

        $zip->close();

        header( "Content-Description: File Transfer" );
        header( "Content-Type: application/octet-stream" );
        header( "Content-Disposition: attachment; filename=email-attachment.zip" );
        header( "Content-Transfer-Encoding: binary" );
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate" );
        header( "Pragma: public" );

        readfile( $zip_file );
        exit;
    }

    /**
     * Close the connection
     *
     * @return void
     */
    public function close() {
        imap_close( $this->connection );
    }
}
