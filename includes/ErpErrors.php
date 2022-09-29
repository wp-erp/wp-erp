<?php

namespace WeDevs\ERP;

// don't call the file directly
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ErpErrors {

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $key = '';

    /**
     * Errors array
     *
     * @var array
     */
    private $errors = [];

    private $form_data = [];

    public function __construct( $key ) {
        $this->key = sanitize_key( $key );

        // get errors
        $transient_name = 'wp_erp_error_' . $this->key;
        $errors         = get_transient( $transient_name );
        $this->errors   = false !== $errors ? $errors : [];
        delete_transient( $transient_name );

        // get form data
        $transient_name  = 'wp_erp_form_data_' . $this->key;
        $form_data       = get_transient( $transient_name );
        $this->form_data = false !== $form_data ? $form_data : [];
        delete_transient( $transient_name );
    }

    /**
     * Get Error Key
     *
     * @return string
     */
    public function get_key() {
        return $this->key;
    }

    /**
     * Check if error exist
     *
     * @return bool
     */
    public function has_error() {
        return empty( $this->errors ) ? false : true;
    }

    /**
     * Add a new error
     *
     * @param WP_Error|string $error
     */
    public function add( $error ) {
        $this->errors[] = $error;
    }

    /**
     * Use this method to save extra data, eg: submitted form data
     *
     * @param array $form_data
     */
    public function add_form_data( $form_data ) {
        $this->form_data = $form_data;
    }

    /**
     * Save datas as transient
     */
    public function save() {
        //save form errors
        if ( ! empty( $this->errors ) ) {
            $transient_name = 'wp_erp_error_' . $this->key;
            set_transient( $transient_name, $this->errors, MINUTE_IN_SECONDS * 10 );
        }

        // save form data
        if ( ! empty( $this->form_data ) ) {
            $transient_name = 'wp_erp_form_data_' . $this->key;
            set_transient( $transient_name, $this->form_data, MINUTE_IN_SECONDS * 10 );
        }
    }

    /**
     * Display error(s) as formatted string
     *
     * @return bool|string
     */
    public function display() {
        $error_string = '';

        if ( ! empty( $this->errors ) ) {
            foreach ( $this->errors as $error ) {
                if ( $error instanceof WP_Error ) {
                    $error_text = $error->get_error_message();
                } else {
                    $error_text = $error;
                }
                $error_string .= "<p>$error_text</p>";
            }
        }

        return $error_string != '' ? '<div class="notice notice-error is-dismissible">' . $error_string . '</div>' : false;
    }

    /**
     * Get available from data(s) as array
     *
     * @return array|bool
     */
    public function get_errors() {
        $return = [];

        if ( ! empty( $this->errors ) ) {
            foreach ( $this->errors as $error ) {
                if ( $error instanceof WP_Error ) {
                    $return[ $error->get_error_code() ] = $error->get_error_message();
                } else {
                    $return[] = $error;
                }
            }
        }

        return empty( $return ) ? false : $return;
    }

    /**
     * Get Form Data
     *
     * @return array
     */
    public function get_form_data() {
        return $this->form_data;
    }

    public function print_generic_error_message() {
        $error_text    = esc_attr__( 'Something went wrong! Please check your input.', 'erp' );
        $error_message = <<<EOD
<div class="notice notice-error is-dismissible">
        <p>$error_text</p>
</div>
EOD;

        return $error_message;
    }

    /**
     * Delete errors from cache
     */
    public function remove() {
        // delete error data
        $transient_name = 'wp_erp_error_' . $this->key;
        delete_transient( $transient_name );

        //delete error data
        $transient_name = 'wp_erp_form_data_' . $this->key;
        delete_transient( $transient_name );
    }
}
