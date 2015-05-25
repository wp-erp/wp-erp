<?php
namespace WeDevs\ERP\Admin;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * sdlfkj
 */
class Admin_Page {

    use Hooker;

    function __construct() {
        $this->init_actions();
        $this->init_classes();
    }

    /**
     * Initialize action hooks
     *
     * @return void
     */
    public function init_actions() {
        $this->action( 'admin_footer', 'erp_modal_markup' );

        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
    }

    /**
     * Initialize required classes
     *
     * @return void
     */
    public function init_classes() {
        new Form_Handler();
        new Ajax();
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

        wp_enqueue_script( 'jquery-chosen', WPERP_ASSETS . "/vendor/chosen/chosen.jquery$suffix.js", array( 'jquery' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'wp-erp-popup', WPERP_ASSETS . "/js/jquery-popup$suffix.js", array( 'jquery' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'wp-erp-script', WPERP_ASSETS . "/js/erp$suffix.js", array( 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-datepicker' ), date( 'Ymd' ), true );

        wp_localize_script( 'wp-erp-script', 'wpErp', array(
            'nonce'           => wp_create_nonce( 'erp-nonce' ),
            'ajaxurl'         => admin_url( 'admin-ajax.php' ),
            'set_logo'        => __( 'Set company logo', 'wp-erp' ),
            'upload_logo'     => __( 'Upload company logo', 'wp-erp' ),
            'remove_logo'     => __( 'Remove company logo', 'wp-erp' ),
            'update_location' => __( 'Update Location', 'wp-erp' ),
            'create'          => __( 'Create', 'wp-erp' ),
            'update'          => __( 'Update', 'wp-erp' )
        ) );

        // load country/state JSON on new company page
        if ( 'toplevel_page_erp-company' == $hook && isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'new', 'edit' ) ) ) {
            wp_enqueue_script( 'post' );
            wp_enqueue_media();

            $country = \WeDevs\ERP\Countries::instance();
            wp_localize_script( 'wp-erp-script', 'wpErpCountries', $country->load_country_states() );
        }

        wp_enqueue_style( 'jquery-ui', WPERP_ASSETS . '/vendor/jquery-ui/jquery-ui-1.9.1.custom.css' );
        wp_enqueue_style( 'jquery-chosen', WPERP_ASSETS . "/vendor/chosen/chosen$suffix.css" );

        wp_enqueue_style( 'wp-erp-styles', WPERP_ASSETS . '/css/admin/admin.css', false, date( 'Ymd' ) );
    }

    /**
     * Prints the ERP modal window markup
     *
     * @return void
     */
    public function erp_modal_markup() {
        include WPERP_INCLUDES . '/admin/views/erp-modal.php';

        erp_get_js_template( WPERP_INCLUDES . '/admin/views/address.php', 'erp-address' );
    }
}

new Admin_Page();