<?php

/**
 * Bootstrap for Weather unit tests.
 *
 * Provides minimal stubs for WordPress functions so the Weather classes
 * can be tested without a full WordPress installation.
 */

// Prevent direct file access guard from blocking.
define( 'ABSPATH', true );

// Load composer autoloader for class resolution.
require_once __DIR__ . '/../../../vendor/autoload.php';

// Stub WordPress functions used by the Weather classes.
if ( ! function_exists( 'add_query_arg' ) ) {
    function add_query_arg( $args, $url ) {
        return $url . '?' . http_build_query( $args );
    }
}

if ( ! function_exists( 'wp_remote_get' ) ) {
    function wp_remote_get( $url, $args = [] ) {
        // Return a WP_Error-like response for testing.
        return new \WP_Error( 'http_request_not_available', 'wp_remote_get is not available in tests.' );
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return $thing instanceof \WP_Error;
    }
}

if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
    function wp_remote_retrieve_response_code( $response ) {
        if ( is_array( $response ) && isset( $response['response']['code'] ) ) {
            return $response['response']['code'];
        }
        return '';
    }
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
    function wp_remote_retrieve_body( $response ) {
        if ( is_array( $response ) && isset( $response['body'] ) ) {
            return $response['body'];
        }
        return '';
    }
}

if ( ! function_exists( 'erp_get_option' ) ) {
    function erp_get_option( $option_name, $section = false, $default = '' ) {
        return $default;
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'do_action' ) ) {
    function do_action( $tag, ...$args ) {
        // No-op in tests.
    }
}

if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value, ...$args ) {
        return $value;
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'get_transient' ) ) {
    function get_transient( $transient ) {
        return false;
    }
}

if ( ! function_exists( 'set_transient' ) ) {
    function set_transient( $transient, $value, $expiration = 0 ) {
        return true;
    }
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) {
        return true;
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

if ( ! function_exists( 'rest_validate_request_arg' ) ) {
    function rest_validate_request_arg( $value, $request, $param ) {
        return true;
    }
}

if ( ! function_exists( 'register_rest_route' ) ) {
    function register_rest_route( $namespace, $route, $args = [], $override = false ) {
        return true;
    }
}

if ( ! function_exists( 'get_current_screen' ) ) {
    function get_current_screen() {
        return null;
    }
}

// Stub WP_Error if not already available.
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        protected $errors = [];
        protected $error_data = [];

        public function __construct( $code = '', $message = '', $data = '' ) {
            if ( ! empty( $code ) ) {
                $this->errors[ $code ][] = $message;
                if ( ! empty( $data ) ) {
                    $this->error_data[ $code ] = $data;
                }
            }
        }

        public function get_error_message( $code = '' ) {
            if ( empty( $code ) ) {
                $code = $this->get_error_code();
            }
            return isset( $this->errors[ $code ] ) ? $this->errors[ $code ][0] : '';
        }

        public function get_error_code() {
            $codes = array_keys( $this->errors );
            return ! empty( $codes ) ? $codes[0] : '';
        }
    }
}

// Time constants.
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
    define( 'MINUTE_IN_SECONDS', 60 );
}
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
    define( 'HOUR_IN_SECONDS', 3600 );
}
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 86400 );
}
