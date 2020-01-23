<?php
namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * ERP License handler class
 *
 * @author WP ERP
 */
class License {

    use Hooker;

    private $api_url = 'https://wperp.com/';

    function __construct( $file, $addon_name, $version, $author, $api_url = null ) {

        // bail out if it's a local server
        if ( $this->is_local_server() ) {
            return;
        }

        $this->file       = $file;
        $this->item_name  = $addon_name;
        $this->version    = $version;
        $this->author     = $author;
        $this->api_url    = is_null( $api_url ) ? $this->api_url : $api_url;
        $this->short_name = preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );

        // include and hooks
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include the EDD updater class
     *
     * @return void
     */
    private function includes() {
        if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) )  {
            require_once __DIR__ . '/lib/EDD_SL_Plugin_Updater.php';
        }
    }

    /**
     * Check if the current server is localhost
     *
     * @return boolean
     */
    private function is_local_server() {
        $addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        $host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

        $is_local = ( in_array( $addr, array( '127.0.0.1', '::1' ) ) || substr( $host, -4 ) == '.dev' );

        return apply_filters( 'erp_lc_is_local_server', $is_local );
    }

    /**
     * Init required hooks
     *
     * @return void
     */
    private function init_hooks() {
        $this->action( 'admin_init', 'init_updater' );
        $this->action( 'admin_notices', 'license_nag' );

        // scheduled events and checking
        $this->action( 'erp_weekly_scheduled_events', 'license_status_check' );
        // $this->action( 'admin_init', 'license_status_check' ); // for testing
        $this->action( 'admin_init', 'save_and_activate_license' );

        $this->action( 'in_plugin_update_message-' . plugin_basename( $this->file ), 'plugin_row_license_missing', 10, 2 );

        // register for settings page
        $this->filter( 'erp_settings_licenses', 'register_addon' );
    }

    /**
     * The option id for the license key
     *
     * @return string
     */
    public function get_license_option_key() {
        return 'erp_license_' . $this->short_name;
    }

    /**
     * The option id for the license key status
     *
     * @return string
     */
    public function get_license_status_option_key() {
        return $this->get_license_option_key() . '_status';
    }

    /**
     * Get the addon license key
     *
     * @return void
     */
    public function get_license_key() {
        return get_option( $this->get_license_option_key(), '' );
    }

    /**
     * Get license status
     *
     * @return array
     */
    public function get_license_status() {
        return get_option( $this->get_license_status_option_key(), [] );
    }

    /**
     * Register the add-on for the license settings page
     *
     * @param  array  $settings
     *
     * @return array
     */
    public function register_addon( $settings ) {
        $settings[] = [
            'name'    => $this->item_name,
            'id'      => $this->get_license_option_key(),
            'license' => $this->get_license_key(),
            'version' => $this->version,
            'status'  => $this->get_license_status()
        ];

        return $settings;
    }

    /**
     * Initialize the plugin updater
     *
     * @return void
     */
    public function init_updater() {
        $args = [
            'version'   => $this->version,
            'license'   => $this->get_license_key(),
            'author'    => $this->author,
            'item_name' => $this->item_name,
            'url'       => home_url()
        ];

        // Setup the updater
        $edd_updater = new \EDD_SL_Plugin_Updater(
            $this->api_url,
            $this->file,
            $args
        );
    }

    public function api_request( $action = 'check_license' ) {
        // data to send in our API request
        $api_params = array(
            'edd_action'=> $action,
            'license'   => $this->get_license_key(),
            'item_name' => urlencode( $this->item_name ),
            'url'       => home_url()
        );

        // Call the API
        $response = wp_remote_post( $this->api_url, array(
            'timeout'   => 15,
            'sslverify' => false,
            'body'      => $api_params
        ) );

        // make sure the response came back okay
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        return $license_data;
    }

    /**
     * Check license status
     *
     * @return void
     */
    public function license_status_check() {
        $license_key = $this->get_license_key();

        if ( empty( $license_key ) ) {
            return;
        }

        $license_data = $this->api_request();

        if ( $license_data ) {
            update_option( $this->get_license_status_option_key(), $license_data );
        }
    }

    /**
     * Try activating the license once saved
     *
     * @return void
     */
    public function save_and_activate_license() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            // die();
        }

        if ( isset( $_POST[ $this->get_license_option_key() ] ) ) {
            $old_key = $this->get_license_key();
            $new_key = sanitize_text_field( wp_unslash( $_POST[ $this->get_license_option_key() ] ) );

            // delete license status if differs
            if ( $old_key != $new_key ) {
                delete_option( $this->get_license_status_option_key() );
                update_option( $this->get_license_option_key(), $new_key );
            }

            // don't do anything if we have a valid license
            $license_status = $this->get_license_status();
            if ( is_object( $license_status ) && 'valid' === $license_status->license ) {
                return;
            }

            // if we have a license key, try to activate now
            $license_key = $this->get_license_key();

            if ( ! empty( $license_key ) ) {
                $license_data = $this->api_request( 'activate_license' );

                if ( $license_data ) {
                    update_option( $this->get_license_status_option_key(), $license_data );
                }
            }
        }
    }

    /**
     * Show license key notice
     *
     * @return void
     */
    public function license_nag() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $license_key = $this->get_license_key();
        $url         = esc_url( admin_url( 'admin.php?page=erp-settings&tab=erp-license' ) );

        if ( empty( $license_key ) ) {
            $this->show_error( sprintf( __( 'Please <a href="%s">enter</a> <strong>%s</strong> license key to get automatic updates and support', 'erp' ), $url, $this->item_name ) );
            return;
        }

        $license_status = $this->get_license_status();

        if ( is_object( $license_status ) && 'valid' !== $license_status->license ) {
            $this->show_error( sprintf(
                __( 'You have invalid or expired license keys for %s. Please go to the <a href="%s" title="Go to Licenses page">Licenses page</a> to correct this issue.', 'erp' ),
                $this->item_name,
                admin_url( 'admin.php?page=erp-settings&tab=erp-license' )
            ) );
        }
    }

    /**
     * Show a error message
     *
     * @param  string  $message
     *
     * @return void
     */
    public function show_error( $message ) {
        echo '<div class="error">';
            echo '<p>' . wp_kses_post( $message ) . '</p>';
        echo '</div>';
    }

    /**
     * Displays message inline on plugin row that the license key is missing
     *
     * @return  void
     */
    public function plugin_row_license_missing( $plugin_data, $version_info ) {
        static $showed_imissing_key_message;

        $license = $this->get_license_status();

        if ( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->get_license_option_key() ] ) ) {

            echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=erp-settings&tab=erp-license' ) ) . '">' . esc_html__( 'Enter valid license key for automatic updates.', 'erp' ) . '</a></strong>';
            $showed_imissing_key_message[ $this->get_license_option_key() ] = true;
        }

    }
}
