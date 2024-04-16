<?php

namespace WeDevs\ERP;

use Exception;

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
     * @param string $host
     * @param string $port           (993|995)
     * @param string $protocol
     * @param string $username
     * @param string $password
     * @param string $authentication (ssl|tls|notls)
     * @param bool   $cert
     *
     * @return void
     */
    public function __construct( $host, $port, $protocol, $username, $password, $authentication = 'ssl', $cert = false ) {
        set_time_limit( 3000 );

        $this->host = $host;

        $option = '';

        if ( $protocol ) {
            $option .= '/' . $protocol;
        }

        $option .= '/' . $authentication;

        if ( $cert ) {
            $option .= '/validate-cert';
        } else {
            $option .= '/novalidate-cert';
        }

        if ( preg_match( '/google|gmail/i', $host ) ) {
            $this->mailbox = '{' . $host . ':' . $port . $option . '}INBOX';
        } else {
            $this->mailbox = '{' . $host . ':' . $port . $option . '}';
        }

        if ( ! $this->is_extension_loaded() ) {
            throw new Exception( 'Your server isn\'t connected with imap.' );
        }

        $this->connection = imap_open( $this->mailbox, $username, $password );

        if ( ! $this->connection ) {
            // phpcs:ignore 	WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new Exception( 'Cannot connect to Email: ' . imap_last_error() );
        }
    }

    /**
     * Determine the imap entension is loaded or try to load it dynamically
     *
     * @return bool
     */
    public function is_extension_loaded() {
        if ( ! extension_loaded( 'imap' ) || ! function_exists( 'imap_open' ) ) {
            // $prefix = ( PHP_SHLIB_SUFFIX == 'dll' ) ? 'php_' : '';
            // $extension = $prefix . 'imap.' . PHP_SHLIB_SUFFIX;

            // if ( function_exists( 'dl' ) ) {
            //     return dl( $extension );
            // }

            return false;
        }

        return true;
    }

    /**
     * Determine if the imap stream is connected
     *
     * @return bool
     */
    public function is_connected() {
        if ( imap_ping( $this->connection ) ) {
            return true;
        }

        return false;
    }

    /**
     * Open the inbox
     *
     * @param string $mailbox (optional)
     * @param string $query   (optional)
     *
     * @return array
     */
    public function open( $mailbox = 'inbox', $query = 'UNSEEN' ) {
        if ( strtolower( $mailbox ) != 'inbox' ) {
            $mailboxes = imap_list( $this->connection, $this->mailbox, $mailbox );

            imap_reopen( $this->connection, $mailboxes[0] );
        }

        $emails = imap_search( $this->connection, $query );

        if ( $emails ) {
            rsort( $emails );

            return $emails;
        }

        return [];
    }

    /**
     * Get all emails with body & attachments
     *
     * @param string $mailbox (optional)
     * @param string $query   (optional)
     *
     * @return array
     */
    public function get_emails( $mailbox = 'inbox', $query = 'UNSEEN' ) {
        $email_ids = $this->open( $mailbox, $query );

        $emails = [];

        foreach ( $email_ids as $email_id ) {
            $emails[] = [
                'id'          => $email_id,
                'subject'     => $this->get_subject( $email_id ),
                'body'        => $this->get_body( $email_id ),
                'attachments' => $this->get_attachments( $email_id ),
                'headers'     => $this->get_headers( $email_id ),
            ];
        }

        return $emails;
    }

    /**
     * Get email overview
     *
     * @param int $email_id
     *
     * @return array
     */
    public function get_overview( $email_id ) {
        $overview = imap_fetch_overview( $this->connection, $email_id, 0 );

        return $overview;
    }

    /**
     * Get email headers
     *
     * @param int $email_id
     *
     * @return array
     */
    public function get_headers( $email_id ) {
        $header_string = imap_fetchheader( $this->connection, $email_id );

        preg_match_all( '/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)\r\n/m', $header_string, $matches );
        $headers = array_combine( $matches[1], $matches[2] );

        return $headers;
    }

    /**
     * Get email subject
     *
     * @param int $email_id
     *
     * @return string
     */
    public function get_subject( $email_id ) {
        $headers = $this->get_headers( $email_id );

        return $headers['Subject'];
    }

    /**
     * Get email body
     *
     * @param int $email_id
     *
     * @return text
     */
    public function get_body( $email_id ) {
        $body = $this->get_part( $email_id, 'TEXT/HTML' );

        // if HTML body is empty, try getting text body
        if ( $body == '' ) {
            $body = $this->get_part( $email_id, 'TEXT/PLAIN' );
        }

        return $body;
    }

    /**
     * Get email part for decoding email body
     *
     * @param int $email_id
     * @param int $mimetype
     * @param int $structure
     * @param int $part_number
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

                $text = imap_fetchbody( $this->connection, $email_id, $part_number, FT_PEEK );

                switch ( $structure->encoding ) {
                    case 3: return imap_base64( $text );

                    case 4: return imap_qprint( $text );

                    default: return $text;
               }
            }

            // multipart
            if ( $structure->type == 1 ) {
                foreach ( $structure->parts as $index => $sub_struct ) {
                    $prefix = '';

                    if ( $part_number ) {
                        $prefix = $part_number . '.';
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
     * @param array $structure
     *
     * @return string
     */
    protected function get_mime_type( $structure ) {
        $primary_mimetypes = [ 'TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'OTHER' ];

        if ( $structure->subtype ) {
            return $primary_mimetypes[(int) $structure->type] . '/' . $structure->subtype;
        }

        return 'TEXT/PLAIN';
    }

    /**
     * Get email attachments
     *
     * @param int $email_id
     *
     * @return array
     */
    public function get_attachments( $email_id ) {
        $structure = imap_fetchstructure( $this->connection, $email_id );

        $attachments = [];

        /* if any attachment found... */
        if ( isset( $structure->parts ) && count( $structure->parts ) ) {
            for ( $i = 0; $i < count( $structure->parts ); $i++ ) {
                $attachments[$i] = [
                    'is_attachment' => false,
                    'filename'      => '',
                    'attachment'    => '',
                ];

                if ( $structure->parts[$i]->ifdparameters ) {
                    foreach ( $structure->parts[$i]->dparameters as $object ) {
                        if ( strtolower( $object->attribute ) == 'filename' ) {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename']      = $object->value;
                        }
                    }
                }

                if ( $structure->parts[$i]->ifparameters ) {
                    foreach ( $structure->parts[$i]->parameters as $object ) {
                        if ( strtolower( $object->attribute ) == 'name' ) {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename']      = $object->value;
                        }
                    }
                }

                if ( $attachments[$i]['is_attachment'] ) {
                    $attachments[$i]['attachment'] = imap_fetchbody( $this->connection, $email_id, $i + 1 );

                    if ( $structure->parts[$i]->encoding == 3 ) {
                        $attachments[$i]['attachment'] = base64_decode( $attachments[$i]['attachment'] );
                    } elseif ( $structure->parts[$i]->encoding == 4 ) {
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
     * Mark emails as seen
     *
     * @param array $email_ids
     *
     * @return bool
     */
    public function mark_seen_emails( $email_ids ) {
        $comma_separated_ids = implode( ',', $email_ids );

        if ( empty( $comma_separated_ids ) ) {
            return false;
        }

        $status = imap_setflag_full( $this->connection, $comma_separated_ids, '\\Seen' );

        return $status;
    }

    /**
     * Download email attachments as zip
     *
     * @param array $attachments
     *
     * @return void
     */
    public function download_attachments( $attachments ) {
        $zip_file = 'email-attachment.zip';

        $zip = new ZipArchive();
        $zip->open( $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE );

        foreach ( $attachments as $file ) {
            $zip->addFromString( $file['filename'], $file['attachment'] );
        }

        $zip->close();

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename=email-attachment.zip' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );

        readfile( $zip_file ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
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
