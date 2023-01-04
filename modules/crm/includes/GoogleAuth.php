<?php

namespace WeDevs\ERP\CRM;

use Google_Client;
use Google_Service_Gmail;

class GoogleAuth {

    /**
     * @var Google_Client
     */
    private $client;

    public function __construct() {
        //init client with options
        $this->init_client();
        add_action( 'admin_init', [ $this, 'handle_google_auth' ] );
        add_action( 'admin_init', [ $this, 'disconnect_account' ] );
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

    private function init_client() {
        $creds = $this->has_credentials();

        if ( empty( $creds['client_id'] ) || empty( $creds['client_secret'] ) ) {
            return false;
        }

        $client = new Google_Client( [
            'client_id'     => $creds['client_id'],
            'client_secret' => $creds['client_secret'],
            'redirect_uris' => [
                $this->get_redirect_url(),
            ],
        ] );

        $client->setAccessType( 'offline' );        // offline access
        $client->setIncludeGrantedScopes( true );   // incremental auth
        $client->addScope( Google_Service_Gmail::GMAIL_SEND );
        $client->addScope( Google_Service_Gmail::GMAIL_MODIFY );
        $client->addScope( Google_Service_Gmail::GMAIL_SETTINGS_BASIC );
        $client->addScope( Google_Service_Gmail::GMAIL_READONLY );
        $client->setRedirectUri( $this->get_redirect_url() );
        $client->setApprovalPrompt( 'force' );

        $token = get_option( 'erp_google_access_token' );

        if ( array_key_exists( 'error', (array) $token ) ) {
            $token = [];
            update_option( 'erp_google_access_token', $token );
        }

        if ( ! empty( $token ) ) {
            $client->setAccessToken( $token );
        }

        $this->client = $client;
    }

    public function get_client() {
        if ( !$this->client instanceof Google_Client ) {
            $this->init_client();
        }

        return $this->client;
    }

    public function set_access_token( $code ) {
        $access_token = $this->client->fetchAccessTokenWithAuthCode( $code );
//        $access_token = $this->client->getAccessToken();

        if ( is_string( $access_token ) ) {
            $access_token = json_decode( $access_token, true );
        }

        if ( array_key_exists( 'error', (array) $access_token ) ) {
            $access_token = [];
        }

        update_option( 'erp_google_access_token', $access_token );
    }

    public function get_redirect_url() {
        return add_query_arg( 'erp-auth', 'google', admin_url( 'options-general.php' ) );
    }

    public function get_disconnect_url() {
        return add_query_arg( 'erp-auth-dc', 'google', admin_url( 'options-general.php' ) );
    }

    public function is_active() {
        if ( !$this->has_credentials() || !$this->is_connected() ) {
            return false;
        }

        $token = get_option( 'erp_google_access_token', [] );

        if ( empty( $token ) ) {
            return false;
        }

        return true;
    }

    public function has_credentials() {
        $options = get_option( 'erp_settings_erp-email_gmail', [] );

        if ( !isset( $options['client_id'] ) || empty( $options['client_id'] ) ) {
            return false;
        }

        if ( !isset( $options['client_secret'] ) || empty( $options['client_secret'] ) ) {
            return false;
        }

        return $options;
    }

    public function is_connected() {
        $email = get_option( 'erp_gmail_authenticated_email', '' );

        if ( !empty( $email ) ) {
            return $email;
        }

        return false;
    }

    public function handle_google_auth() {
        if ( !isset( $_GET['erp-auth'] ) || !isset( $_GET['code'] ) ) {
            return;
        }
        $this->set_access_token( sanitize_text_field( wp_unslash( $_GET['code'] ) ) );

        wperp()->google_sync->update_profile();

        wp_redirect( $this->get_settings_url() );
    }

    public function disconnect_account() {
        if ( !isset( $_GET['erp-auth-dc'] ) ) {
            return;
        }
        $this->clear_account_data();

        wp_redirect( $this->get_settings_url() );
    }

    public function clear_account_data() {
        //reset access token
        update_option( 'erp_google_access_token', [] );
        //reset email
        update_option( 'erp_gmail_authenticated_email', '' );
        //reset history id
        update_option( 'erp_gsync_historyid', '' );
    }

    public function get_settings_url() {
        $settings_url = admin_url( 'admin.php?page=erp-settings#/erp-email/email_connect' );

        return $settings_url;
    }
}
