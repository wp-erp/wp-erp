<?php
namespace WeDevs\ERP\API;

use WP_Error;
use WeDevs\ERP\Framework\Models\APIKey;

/**
 * WP Rest API Basic Authentication class
 */
class Authentication {

    public function __construct() {
        add_filter( 'determine_current_user', [ $this, 'authenticate' ], 100 );
        add_filter( 'rest_authentication_errors', [ $this, 'check_rest_api_authentication' ] );
    }

    public function check_rest_api_authentication( $error ) {
        global $erp_rest_authentication_error;

        if ( ! empty( $error ) ) {
            return $error;
        }

        return $erp_rest_authentication_error;
    }

    public function authenticate( $user_id ) {
        global $erp_rest_authentication_error;

        // Don't authenticate twice
        if ( ! empty( $user_id ) ) {
            return $user_id;
        }

        $consumer_key    = '';
        $consumer_secret = '';

        if ( ! empty( $_GET['consumer_key'] ) && ! empty( $_GET['consumer_secret'] ) ) {
            $consumer_key    = $_GET['consumer_key'];
            $consumer_secret = $_GET['consumer_secret'];
        }

        if ( ! $consumer_key && ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
            $consumer_key    = $_SERVER['PHP_AUTH_USER'];
            $consumer_secret = $_SERVER['PHP_AUTH_PW'];
        }

        if ( empty( $consumer_key ) || empty( $consumer_secret ) ) {
            $erp_rest_authentication_error = new WP_Error( 'erp_rest_authentication_error', __( 'Required consumer key & consumer secret.', 'erp' ), array( 'status' => 401 ) );
            return false;
        }

        $api = APIKey::where( 'api_key', $consumer_key )->first();

        if ( ! $api || ! hash_equals( $api->api_secret, $consumer_secret ) ) {
            $erp_rest_authentication_error = new WP_Error( 'erp_rest_authentication_error', __( 'Consumer secret is invalid.', 'erp' ), array( 'status' => 401 ) );
            return false;
        }

        $api->update( ['last_accessed_at' => current_time( 'mysql' )] );

        return $api->user_id;
    }
}