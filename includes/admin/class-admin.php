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

        $this->action( 'init', 'includes' );
        $this->action( 'admin_init', 'admin_redirects' );
        $this->action( 'admin_footer', 'erp_modal_markup' );
    }

    /**
     * Include required files
     *
     * @return void
     */
    public function includes() {
        // Setup/welcome
        if ( ! empty( $_GET['page'] ) ) {

            if ( 'erp-setup' == $_GET['page'] ) {
                include_once dirname( __FILE__ ) . '/class-setup-wizard.php';
            }
        }
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
     * Handle redirects to setup/welcome page after install and updates.
     *
     * @return void
     */
    public function admin_redirects() {
        if ( ! get_transient( '_erp_activation_redirect' ) ) {
            return;
        }

        delete_transient( '_erp_activation_redirect' );

        if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'erp-setup', 'erp-welcome' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // If it's the first time
        if ( get_option( 'erp_setup_wizard_ran' ) != '1' ) {
            wp_safe_redirect( admin_url( 'index.php?page=erp-setup' ) );
            exit;

            // Otherwise, the welcome page
        } else {
            wp_safe_redirect( admin_url( 'index.php?page=erp-welcome' ) );
            exit;
        }
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