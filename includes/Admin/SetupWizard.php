<?php
/**
 * Setup wizard class
 *
 * Renders the React-based onboarding app.
 */

namespace WeDevs\ERP\Admin;

class SetupWizard {

    public function __construct() {
        if ( apply_filters( 'erp_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
            add_action( 'admin_menu', [ $this, 'admin_menus' ] );
            add_action( 'admin_init', [ $this, 'setup_wizard' ] );
        }
    }

    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'erp-setup', '' );
    }

    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'erp-setup' !== $_GET['page'] ) {
            return;
        }

        $onboarding_dist_url = WPERP_URL . '/includes/Admin/Onboarding/assets/dist';

        wp_enqueue_style( 'wperp-onboarding', $onboarding_dist_url . '/onboarding.css', [], WPERP_VERSION );
        wp_enqueue_script( 'wperp-onboarding', $onboarding_dist_url . '/onboarding.js', [], WPERP_VERSION, true );

        $page           = '?page=erp-hr&section=people&sub-section=employee&action=download_sample&type=employee';
        $csv_nonce      = 'erp-import-export-nonce';
        $csv_sample_url = wp_nonce_url( $page, $csv_nonce );

        wp_localize_script( 'wperp-onboarding', 'wpErpOnboarding', [
            'nonce'        => wp_create_nonce( 'wp_rest' ),
            'importNonce'  => wp_create_nonce( 'erp-import-export-nonce' ),
            'apiUrl'       => rest_url( 'erp/v1' ),
            'adminUrl'     => admin_url(),
            'distUrl'      => $onboarding_dist_url,
            'logoUrl'      => file_exists( WPERP_PATH . '/assets/images/wperp-logo.png' )
                              ? WPERP_ASSETS . '/images/wperp-logo.png'
                              : '',
            'sampleCsvUrl' => admin_url( 'admin.php' . $csv_sample_url ),
        ] );

        ob_start();
        $this->render_page();
        exit;
    }

    private function render_page() {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e( 'WP ERP &rsaquo; Setup Wizard', 'erp' ); ?></title>
            <?php
                wp_print_styles( 'wperp-onboarding' );
                wp_print_scripts( 'wperp-onboarding' );
            ?>
        </head>
        <body class="wperp-setup-root">
            <div id="wperp-onboarding-root"></div>
        </body>
        </html>
        <?php
    }
}

return new SetupWizard();
