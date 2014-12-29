<?php
/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP
 */

// namespace WeDevs\ERP;

// don't call the file directly
// if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Installer Class
 *
 * @package ERP
 */
class WeDevs_ERP_Installer {

    function __construct() {
        register_activation_hook( WPERP_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( WPERP_FILE, array( $this, 'deactivate' ) );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {

        // bail out if the php version is lower than 5.3.0
        if ( version_compare( PHP_VERSION, '5.3.0', '<=' ) ) {
            deactivate_plugins( basename( WPERP_FILE ) );
            wp_die( __( '<p>The <strong>WP ERP</strong> plugin requires PHP version 5.3 or greater', 'wp-erp' ), __( 'Plugin Activation Error', 'wp-erp' ), array( 'response' => 200, 'back_link' => true ) );
        }

        $this->create_tables();
        $this->create_roles();

        update_option( 'wp_erp_version', WPERP_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    public function create_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    /**
     * Create user roles and capabilities
     *
     * @return void
     */
    public function create_roles() {

        // Employee role
        add_role( 'employee', __( 'Employee', 'wp-erp' ), array(
            'read'                      => true,
            'edit_posts'                => false,
            'delete_posts'              => false
        ) );
    }
}

new WeDevs_ERP_Installer();