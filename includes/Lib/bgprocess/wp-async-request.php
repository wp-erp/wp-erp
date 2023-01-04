<?php
/**
 * WP Async Request
 */
if ( ! class_exists( 'WP_Async_Request' ) ) {

    /**
     * Abstract WP_Async_Request class.
     *
     * @abstract
     */
    abstract class WP_Async_Request {

        /**
         * Prefix
         *
         * (default value: 'wp')
         *
         * @var string
         */
        protected $prefix = 'wp';

        /**
         * Action
         *
         * (default value: 'async_request')
         *
         * @var string
         */
        protected $action = 'async_request';

        /**
         * Identifier
         *
         * @var mixed
         */
        protected $identifier;

        /**
         * Data
         *
         * (default value: array())
         *
         * @var array
         */
        protected $data = [];

        /**
         * Initiate new async request
         */
        public function __construct() {
            $this->identifier = $this->prefix . '_' . $this->action;

            add_action( 'wp_ajax_' . $this->identifier, [ $this, 'maybe_handle' ] );
            add_action( 'wp_ajax_nopriv_' . $this->identifier, [ $this, 'maybe_handle' ] );
        }

        /**
         * Set data used during the request
         *
         * @param array $data data
         *
         * @return $this
         */
        public function data( $data ) {
            $this->data = $data;

            return $this;
        }

        /**
         * Dispatch the async request
         *
         * @return array|WP_Error
         */
        public function dispatch() {
            $url  = add_query_arg( $this->get_query_args(), $this->get_query_url() );
            $args = $this->get_post_args();

            return wp_remote_post( esc_url_raw( $url ), $args );
        }

        /**
         * Get query args
         *
         * @return array
         */
        protected function get_query_args() {
            if ( property_exists( $this, 'query_args' ) ) {
                return $this->query_args;
            }

            return [
                'action' => $this->identifier,
                'nonce'  => wp_create_nonce( $this->identifier ),
            ];
        }

        /**
         * Get query URL
         *
         * @return string
         */
        protected function get_query_url() {
            if ( property_exists( $this, 'query_url' ) ) {
                return $this->query_url;
            }

            return admin_url( 'admin-ajax.php' );
        }

        /**
         * Get post args
         *
         * @return array
         */
        protected function get_post_args() {
            if ( property_exists( $this, 'post_args' ) ) {
                return $this->post_args;
            }

            return [
                'timeout'   => 0.01,
                'blocking'  => false,
                'body'      => $this->data,
                'cookies'   => map_deep( wp_unslash( $_COOKIE ), 'sanitize_text_field' ),
                'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
            ];
        }

        /**
         * Maybe handle
         *
         * Check for correct nonce and pass to handler.
         */
        public function maybe_handle() {
            // Don't lock up other requests while processing
            session_write_close();

            check_ajax_referer( $this->identifier, 'nonce' );

            $this->handle();

            wp_die();
        }

        /**
         * Handle
         *
         * Override this method to perform any actions required
         * during the async request.
         */
        abstract protected function handle();
    }
}
