<?php

namespace WeDevs\ERP\CRM;

class Gmail_Sync {

    /**
     * @var \Google_Service_Gmail
     */
    private $gmail;

    /**
     * @var \Google_Auth
     */
    private $client;

    private $userid = 'me';

    public function __construct() {
        $this->client = wperp()->google_auth;
        if( !$this->client->get_client() ){
            return;
        }
        $this->gmail = new \Google_Service_Gmail( $this->client->get_client() );
    }

    /**
     * Initializes the WeDevs_ERP() class
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new self();
        }

        return $instance;
    }

    public function get_historyid() {
        $history = get_option( 'erp_gsync_historyid' );
        return $history;
    }

    private function update_historyid( $id ) {
        if ( $id < $this->get_historyid() ) {
            return false;
        }
        return update_option( 'erp_gsync_historyid', $id );
    }

    private function set_inbound_email( $email ) {
        return update_option( 'erp_gmail_authenticated_email', $email );
    }

    private function get_inbound_email() {
        $email = get_option( 'erp_gmail_authenticated_email', '' );
        if ( !empty( $email ) ) {
            return $email;
        }

        $profile = $this->update_profile();
        return $profile->getEmailAddress();
    }

    public function update_profile() {
        $profile = $this->gmail->users->getProfile( $this->userid );
        $this->update_historyid( $profile->getHistoryId() );
        $this->set_inbound_email( $profile->getEmailAddress() );

        return $profile;
    }

    public function sync() {
        //get history id
        $history_id = $this->get_historyid();

        if ( empty( $history_id ) ) {
            //get full sync
            $this->full_sync();
        } else {
            //do partial sync
            $this->partial_sync( $history_id );
        }
    }

    public function full_sync() {
        $this->update_profile();
        $this->sync();
    }

    public function partial_sync( $historyid ) {
        //get all messages after history id
        try {
            $data = $this->gmail->users_history->listUsersHistory( $this->userid, [ 'startHistoryId' => $historyid ] );
        } catch ( \Google_Service_Exception $e ) {
            error_log( 'Gmail API SYNC error : ' );
            error_log( $e->getMessage());
            $this->full_sync();
            return;
        }

        $histories = $data->getHistory();

        if ( empty( $histories ) ) {
            //update historyid as no new history is found
            $this->update_historyid( $data->getHistoryId() );
            return;
        }

        $added_messages = [];

        foreach ( $histories as $history ) {
            $item = $history->getMessagesAdded();
            if ( !isset( $item[0] ) ) {
                continue;
            }
            /**
             * @var \Google_Service_Gmail_Message
             */
            $message = $item[0]->getMessage();
            $labels = $message->getLabelIds();

            //skip DRAFT and SENT messages
            if ( in_array( 'DRAFT', $labels ) || in_array( 'SENT', $labels ) ) {
                continue;
            }
            $added_messages[] = $message->getId();
        }

        $emails = [];
        if ( empty( $added_messages ) ) {
            $this->update_historyid( $data->getHistoryId() );
            return true;
        }
        $emails = $this->get_messages( $added_messages );
        $this->process_emails( $emails );

        return true;
    }

    public function format_email( $message ) {

        if ( !$message instanceof \Google_Service_Gmail_Message ) {
            return false;
        }

        $headers = $message->getPayload()->getHeaders();
        $parts   = $message->getPayload()->getParts();
        $attachments = [];

        $headers = array_reduce( $headers, [ $this, 'format_header' ] );
        $body = $this->get_message_body( $message );

        foreach ( $parts as $key => $value ) {
            if ( !isset( $value['body']['attachmentId'] ) ) {
                continue;
            }

            try {
                $att = $this->gmail->users_messages_attachments->get( $this->userid, $message->getId(), $value['body']['attachmentId'] );
                $data = $att->getData();
            }
            catch ( \Exception $e ) {
                error_log( 'Failed to fetch attachment : ', $e->getMessage() );
                continue;
            };
            $data = [ 'id'   => $value['body']['attachmentId'],
                      'type' => $value->getMimeType(),
                      'name' => $value->getfilename(),
                      'data' => $this->base64url_decode( $data )
            ];

            array_push( $attachments, $data );
        }

        return [
            'id'          => $message->getId(),
            'history_id'  => $message->getHistoryId(),
            'headers'     => $headers,
            'body'        => $this->base64url_decode( $body ),
            'subject'     => $headers['Subject'],
            'attachments' => $attachments
        ];
    }

    public function save_attachments( $attachments ) {
        if ( empty( $attachments ) ) {
            return $attachments;
        }

        $subdir      = apply_filters( 'crm_attachmet_directory', 'crm-attachments' );
        $upload_dir  = wp_upload_dir();
        $dir         = $upload_dir['basedir'].'/'.$subdir.'/';

        //Create CRM attachments directory
        if ( !file_exists( $dir ) ) {
            wp_mkdir_p($dir);
        }

        foreach ( $attachments as $key => $item ) {
            $name = $item['name'];
            $file = wp_check_filetype( $item['name'] );

            if ( file_exists( $dir.$name ) ) {
                $name = uniqid().'.'.$file['ext'];
            }

            $saved = file_put_contents( $dir.$name, $item['data'] );

            if ( $saved ) {
                $attachments[$key]['slug'] = $name;
                $attachments[$key]['path'] = $dir.$name;
                //remove image data
                unset( $attachments[$key]['data'] );
                unset( $attachments[$key]['id'] );
            } else {
                unset( $attachments[$key] );
            }
        }

        return $attachments;

    }

    public function format_header( $headers, $item ) {
        $headers[$item->name] = $item->value;
        return $headers;
    }

    public function get_messages( $ids ) {

        $batch = $this->gmail->createBatch();
        $this->gmail->getClient()->setUseBatch( true );
        foreach ( $ids as $id ) {
            $batch->add( $this->gmail->users_messages->get( 'me', $id ), $id );
        }

        //fetch messages
        $messages = $batch->execute();
        $this->gmail->getClient()->setUseBatch( false );
        $emails = [];
        if ( !empty( $messages ) ) {
            foreach ( $messages as $message ) {
                $emails[] = $this->format_email( $message );
            }
        }

        return $emails;

    }

    public function process_emails( $emails ) {

        do_action( 'erp_crm_new_inbound_emails', $emails );

        $http_host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST']  : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $email_regexp = '([a-z0-9]+[.][0-9]+[.][0-9]+[.][r][1|2])@' . $http_host;
        foreach ( $emails as $email ) {

            if ( !isset( $email['headers']['References'] ) ) {
                $this->update_historyid( $email['history_id'] );
                continue;
            }

            if ( isset( $email['headers']['References'] ) && preg_match( '/<' . $email_regexp . '>/', $email['headers']['References'], $matches ) ) {

                $filtered_emails[] = $email;

                $message_id = $matches[1];
                $message_id_parts = explode( '.', $message_id );

                $email['hash'] = $message_id_parts[0];
                $email['cid'] = $message_id_parts[1];
                $email['sid'] = $message_id_parts[2];

                $email['attachments'] = $this->save_attachments( $email['attachments'] );
                // Save & sent the email
                switch ( $message_id_parts[3] ) {
                    case 'r1':
                        $customer_feed_data = erp_crm_save_email_activity( $email, $this->get_inbound_email() );
                        break;
                    case 'r2':
                        $customer_feed_data = erp_crm_save_contact_owner_email_activity( $email, $this->get_inbound_email() );
                        break;
                }

                $type = ( $message_id_parts[3] == 'r2' ) ? 'owner_to_contact' : 'contact_to_owner';
                $email['type'] = $type;
                //update history id
                $this->update_historyid( $email['history_id'] );
                do_action( 'erp_crm_contact_inbound_email', $email, $customer_feed_data );
            }
        }
    }

    private function base64url_decode( $data ) {
        return base64_decode( str_replace( array( '-', '_' ), array( '+', '/' ), $data ) );
    }

    private function base64url_encode( $data ) {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }

    /**
     * @param \Google_Service_Gmail_Message $message
     *
     * @return string body
     */
    private function get_message_body( \Google_Service_Gmail_Message $message ) {

        if ( $message->getPayload()->getBody()->getData() ) {
            return $message->getPayload()->getBody()->getData();
        }

        $parts = $message->getPayload()->getParts();

        if ( !empty( $parts ) ) {
            foreach ( $parts as $part ) {

                if ( !empty( $part['parts'] ) ) {
                    return array_last( $part['parts'] )->getBody()->getData();
                }

                if ( $part['mimeType'] != 'text/html' )
                    continue;

                return $part->getBody()->getData();
            }
        }
    }

}
