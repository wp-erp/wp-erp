<?php
namespace WeDevs\ERP\API;

use WP_Error;

/**
 * WP Rest API Basic Authentication class
 */
class Authentication {

    public function __construct() {
        add_filter( 'rest_authentication_errors', [ $this, 'check_rest_api_authentication' ] );
        add_filter( 'determine_current_user', [ $this, 'json_basic_auth_handler' ], 20 );
        add_filter( 'json_authentication_errors', [ $this, 'json_basic_auth_error' ] );
    }

    public function check_rest_api_authentication( $result ) {
        if ( ! empty( $result ) ) {
            return $result;
        }

        if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) && ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
            return new WP_Error( 'restx_logged_out', 'Sorry, you must be logged in to make a request.', array( 'status' => 401 ) );
        }

        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
        $user     = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) ) {
            return $user;
        }

        return $result;
    }

    public function json_basic_auth_handler( $user ) {
        global $wp_json_basic_auth_error;

        $wp_json_basic_auth_error = null;

        // Don't authenticate twice
        if ( ! empty( $user ) ) {
            return $user;
        }

        // Check that we're trying to authenticate
        if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            return $user;
        }

        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        remove_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );

        $user = wp_authenticate( $username, $password );

        add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );

        if ( is_wp_error( $user ) ) {
            $wp_json_basic_auth_error = $user;
            return null;
        }

        $wp_json_basic_auth_error = true;

        return $user->ID;
    }

    public function json_basic_auth_error( $error ) {
        // Passthrough other errors
        if ( ! empty( $error ) ) {
            return $error;
        }

        global $wp_json_basic_auth_error;

        return $wp_json_basic_auth_error;
    }
}