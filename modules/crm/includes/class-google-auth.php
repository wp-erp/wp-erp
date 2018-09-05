<?php
namespace WeDevs\ERP\CRM;

class Google_Auth {

    /**
     * @var \Google_Client
     */
    private $client;
    private $options;

    public function __construct() {
        //check if options are saved
        //get options and set
        //init client with options
        $this->init_client();
        add_action( 'admin_init', [$this, 'handle_google_auth'] );
    }

    /**
     * Initializes the WeDevs_ERP() class
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    private function init_client(){
        $client = new \Google_Client( array(
//            'client_id'     => $this->options['client_id'],
            'client_id'     => '585473699682-sdlfqsnovjvgk5lgl73jbprfot463ogh.apps.googleusercontent.com',
//            'client_secret' => $this->options['client_secret'],
            'client_secret' =>'08x6TdwuajMc095yJ1eSWIfa',
            'redirect_uris' => array(
                $this->get_redirect_url(),
            ),
        ) );

        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(\Google_Service_Gmail::GMAIL_SEND);
        $client->addScope(\Google_Service_Gmail::GMAIL_MODIFY);
        $client->addScope(\Google_Service_Gmail::GMAIL_SETTINGS_BASIC);
        $client->addScope(\Google_Service_Gmail::GMAIL_READONLY);
        $client->setRedirectUri($this->get_redirect_url());

        $token = get_option( 'erp_google_access_token' );

        if ( !empty( $token ) ) {
            $client->setAccessToken($token);
        }

        $this->client = $client;

    }

    public function get_client(){
        if ( ! $this->client instanceof \Google_Client ){
            $this->init_client();
        }
        return $this->client;
    }

    public function set_access_token( $code ){
        $this->client->authenticate( $code );
        $access_token = $this->client->getAccessToken();
        update_option( 'erp_google_access_token', $access_token );
    }

    public function get_redirect_url(){
        return add_query_arg( 'erp-auth', 'google', admin_url( 'options-general.php' ) );
    }

    public function is_active(){

        //check if client id secret are all available and set as acitve
        return true;
    }

    public function handle_google_auth(){
        if ( !isset( $_GET['erp-auth'] ) || ! isset($_GET['code']) ) {
            return;
        }
        $this->set_access_token( $_GET['code'] );

        $settings_url = add_query_arg( [ 'page' => 'erp-settings', 'tab' => 'erp-email' ], admin_url('admin.php') );
        wp_redirect( $settings_url );
    }

}
