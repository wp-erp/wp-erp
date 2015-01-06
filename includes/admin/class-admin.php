<?php
namespace WeDevs\ERP;

/**
 * sdlfkj
 */
class Admin_Page {

    function __construct() {
        add_action( 'admin_footer', array($this, 'erp_modal_markup' ) );

        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts' ) );
    }

    /**
     * Load admin scripts and styles
     *
     * @param  string
     *
     * @return void
     */
    public function admin_scripts( $hook ) {
        // var_dump( $hook );

        $suffix = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_script( 'wp-erp-popup', WPERP_ASSETS . "/js/jquery-popup$suffix.js", array( 'jquery' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'wp-erp-script', WPERP_ASSETS . "/js/erp$suffix.js", array( 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-datepicker' ), date( 'Ymd' ), true );

        wp_localize_script( 'wp-erp-script', 'wpErp', array(
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'set_logo'    => __( 'Set company logo', 'wp-erp' ),
            'upload_logo' => __( 'Upload company logo', 'wp-erp' ),
            'remove_logo' => __( 'Remove company logo', 'wp-erp' )
        ) );

        // load country/state JSON on new company page
        if ( 'toplevel_page_erp-company' == $hook && isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'new', 'edit' ) ) ) {
            wp_enqueue_script( 'post' );
            wp_enqueue_media();

            $country = \WeDevs\ERP\Countries::instance();
            wp_localize_script( 'wp-erp-script', 'wpErpCountries', $country->load_country_states() );
        }

        wp_enqueue_style( 'jquery-ui', WPERP_ASSETS . '/css/jquery-ui-1.9.1.custom.css' );
        wp_enqueue_style( 'wp-erp-styles', WPERP_ASSETS . '/css/admin/admin.css', false, date( 'Ymd' ) );
    }

    /**
     * Prints the ERP modal window markup
     *
     * @return void
     */
    public function erp_modal_markup() {
        include WPERP_INCLUDES . '/admin/views/erp-modal.php';
    }
}

new Admin_Page();