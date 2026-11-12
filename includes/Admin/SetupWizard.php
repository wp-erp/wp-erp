<?php

namespace WeDevs\ERP\Admin;

/**
 * Setup wizard (`index.php?page=erp-setup`).
 *
 * Renders the React onboarding app from the HR React build on a bare page.
 * The steps talk to `erp/v2/onboarding`.
 */
class SetupWizard {

    /**
     * Class constructor
     */
    public function __construct() {
        if ( apply_filters( 'erp_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
            add_action( 'admin_menu', [ $this, 'admin_menus' ] );
            add_action( 'admin_init', [ $this, 'setup_wizard' ] );
        }
    }

    /**
     * Add admin menus/screens.
     *
     * @return void
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'erp-setup', '' );
    }

    /**
     * Show the setup wizard.
     *
     * @return void
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'erp-setup' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        // Opening the wizard counts as running it, so activation no longer redirects here.
        update_option( 'erp_setup_wizard_ran', '1' );

        $enqueue = '\WeDevs\ERP\HRM\Admin\Enqueue';
        $handle  = class_exists( $enqueue ) ? $enqueue::register_entry( 'onboarding' ) : '';

        // The wizard is built on the HR module and its React build; without
        // them there is nothing to set up here.
        if ( '' === $handle ) {
            wp_safe_redirect( admin_url( 'admin.php?page=erp' ) );
            exit;
        }

        wp_add_inline_script(
            $handle,
            'window.__ERP_HR_BOOT__ = ' . wp_json_encode( $enqueue::boot_payload( 'erp-hr', false ) ) . ';'
            . 'window.__ERP_ONBOARDING__ = ' . wp_json_encode(
                [
                    'logoUrl' => WPERP_ASSETS . '/images/wperp-logo.png',
                    'exitUrl' => admin_url( 'admin.php?page=erp-hr' ),
                ]
            ) . ';',
            'before'
        );

        wp_enqueue_script( $handle );

        if ( wp_style_is( $handle, 'registered' ) ) {
            wp_enqueue_style( $handle );
        }

        $this->render_page( $handle );
        exit;
    }

    /**
     * Print the bare wizard page.
     *
     * @param string $handle Script and style handle.
     *
     * @return void
     */
    private function render_page( $handle ) {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php esc_html_e( 'WP ERP &rsaquo; Setup Wizard', 'erp' ); ?></title>
            <?php wp_print_styles( $handle ); ?>
        </head>
        <body class="erp-onboarding">
            <div id="erp-hr-app"></div>
            <?php wp_print_scripts( $handle ); ?>
        </body>
        </html>
        <?php
    }
}

return new SetupWizard();
