<?php

namespace WeDevs\ERP\Admin;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Status;

/**
 * Administration Menu Class
 */
class AdminMenu {
    use Hooker;

    /**
     * Kick-in the class
     */
    public function __construct() {
        $this->action( 'admin_menu', 'admin_menu' );
        $this->action( 'admin_menu', 'admin_settings', 99 );
        $this->action( 'admin_menu', 'hide_admin_menus', 100 );
        $this->action( 'wp_before_admin_bar_render', 'hide_admin_bar_links', 100 );
        $this->action( 'admin_bar_menu', 'add_admin_bar_menu' );
    }

    /**
     * Render Admin bar menu
     */
    public function add_admin_bar_menu() {
        global $wp_admin_bar, $wpdb;

        /* Check that the admin bar is showing and user has permission... */
        if ( !is_admin_bar_showing() ) {
            return;
        }

        $menu = erp_menu();

        $menu   = erp_menu();
        $hide   = [];
        $header = erp_get_menu_headers();

        if ( !current_user_can( 'administrator' ) ) {
            if ( !current_user_can( 'erp_hr_manager' ) && !current_user_can( 'erp_recruiter' ) && !current_user_can( 'erp_list_employee' ) ) {
                unset( $header['hr'] );
                $hide[] = true;
            }

            if ( !current_user_can( 'erp_crm_manager' ) && !current_user_can( 'erp_crm_agent' ) ) {
                unset( $header['crm'] );
                $hide[] = true;
            }

            if ( !current_user_can( 'erp_ac_manager' ) ) {
                unset( $header['accounting'] );
                $hide[] = true;
            }
        }

        if ( count( $hide ) == 3 ) {
            return;
        }

        /* Add the main siteadmin menu item */
        $wp_admin_bar->add_menu(
            [
                'parent'    => 'top-secondary',
                'id'        => 'wp-erp',
                'title'     => '<span class="ab-icon"><?xml version="1.0" encoding="UTF-8"?><svg width="17px" height="17px" viewBox="0 0 37 37" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="erp-logo" fill="#82878C" fill-rule="nonzero"><path d="M30.3450389,6.04059132 C25.6652413,7.67566309 21.1099947,9.64748663 16.7150389,11.9405913 C15.7850389,12.4605913 14.8150389,13.0405913 13.7150389,13.7205913 C13.0443119,14.2840176 12.1907283,14.5827719 11.3150389,14.5605913 C10.3150389,14.4305913 8.90503889,13.5605913 9.00503889,12.4205913 C9.14503889,10.8305913 11.7350389,9.50059132 12.9150389,8.73059132 C14.7401826,7.56812598 16.67637,6.58998289 18.6950389,5.81059132 C21.1378916,4.73528564 23.7587368,4.12160777 26.4250389,4.00059132 C26.9947028,3.99290686 27.5629047,4.06014969 28.1150389,4.20059132 C28.9137259,4.74357385 29.6602644,5.35955176 30.3450389,6.04059132 Z" id="Shape-Copy-2"></path><path d="M30.7933348,15.9696304 C29.4433348,16.4596304 17.5233348,20.8296304 15.7933348,24.3896304 C15.3633836,25.3434159 15.3633836,26.4358449 15.7933348,27.3896304 C16.3919857,28.7511327 17.7917314,29.5797177 19.2733348,29.4496304 C19.6790059,29.4462835 20.0837104,29.4094922 20.4833348,29.3396304 C21.6712939,29.1478656 22.8417823,28.8602599 23.9833348,28.4796304 C24.9833348,28.1296304 25.9833348,27.6996304 26.9833348,27.2896304 L27.8133348,26.9496304 C30.7383636,25.6650404 33.5343329,24.1046551 36.1633348,22.2896304 C34.4182609,29.8341877 28.08799,35.4468207 20.3887538,36.2759094 C12.6895175,37.104998 5.30944369,32.968757 1.99813915,25.9686936 C-1.31316539,18.9686301 0.170333077,10.6395718 5.69491091,5.21327782 C11.2194887,-0.213016153 19.5738319,-1.54678143 26.5133348,1.88963044 C16.9833348,3.59963044 12.0633348,7.18963044 9.18333477,9.75963044 L8.86333477,10.0896304 C7.93333477,11.0396304 6.66333477,12.3296304 7.47333477,14.4196304 C7.9783997,15.5890182 9.06827343,16.4007074 10.3333348,16.5496304 L10.7033348,16.5496304 C11.6270831,16.4741709 12.5076261,16.1261302 13.2333348,15.5496304 C14.2333348,14.8796304 15.2333348,14.3096304 16.1533348,13.7996304 C21.2778133,11.1185988 26.6326398,8.90303934 32.1533348,7.17963044 C33.6733348,7.84963044 36.5733348,11.2496304 36.3833348,14.1796304 C34.6633348,14.7396304 32.2733348,15.4396304 30.7933348,15.9696304 Z" id="Shape-Copy-3"></path><path d="M34.3722313,17.49 C34.3726197,18.4970161 34.2754999,19.501704 34.0822313,20.49 C31.869491,22.1051295 29.5283527,23.5365684 27.0822313,24.77 C24.8622313,25.93 22.7822313,27.32 20.2422313,27.89 C19.0122313,28.16 16.9222313,27.42 17.0022313,25.89 C17.0622313,24.8 19.0022313,23.54 19.7922313,22.98 C21.2689784,21.9825786 22.8358063,21.1255103 24.4722313,20.42 C27.682491,19.0175052 30.9997216,17.8738632 34.3922313,17 C34.3622313,17.16 34.3722313,17.32 34.3722313,17.49 Z" id="Shape-Copy-4"></path></g></g></svg></span>
                                WP ERP ',
            ]
        );

        $html = '<div class="wp-erp-admin-bar-menu">';

        foreach ( $menu as $component => $items ) {
            if ( empty( $header[$component] ) ) {
                continue;
            }
            $html .= '<div style="min-width: 200px;" >';
            $html .= sprintf( "<h3><a href='%s'>%s</a></h3>", admin_url( 'admin.php?page=erp-' . $component ), $header[$component]['title'] );
            $html .= erp_build_mega_menu( $items, '', $component );
            $html .= '</div>';
        }
        $html .= '</div>';
        $wp_admin_bar->add_menu(
            [
                'parent'    => 'wp-erp',
                'id'        => 'wp-erp-child',
                'title'     => ' ',
                'meta'      => [
                    'html'    => $html,
                ],
            ]
        );
    }

    /**
     * Get the admin menu position
     *
     * @return int the position of the menu
     */
    public function get_menu_position() {
        return apply_filters( 'payroll_menu_position', 9999 );
    }

    /**
     * Add menu items
     *
     * @return void
     */
    public function admin_menu() {
        add_menu_page( __( 'WP ERP', 'erp' ), apply_filters( 'erp_title', 'WP ERP'), 'manage_options', 'erp', [ $this, 'overview_page' ], 'data:image/svg+xml;base64,' . base64_encode( '<?xml version="1.0" encoding="UTF-8"?><svg width="37px" height="37px" viewBox="0 0 37 37" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g fill="#82878C" fill-rule="nonzero"><path d="M30.3450389,6.04059132 C25.6652413,7.67566309 21.1099947,9.64748663 16.7150389,11.9405913 C15.7850389,12.4605913 14.8150389,13.0405913 13.7150389,13.7205913 C13.0443119,14.2840176 12.1907283,14.5827719 11.3150389,14.5605913 C10.3150389,14.4305913 8.90503889,13.5605913 9.00503889,12.4205913 C9.14503889,10.8305913 11.7350389,9.50059132 12.9150389,8.73059132 C14.7401826,7.56812598 16.67637,6.58998289 18.6950389,5.81059132 C21.1378916,4.73528564 23.7587368,4.12160777 26.4250389,4.00059132 C26.9947028,3.99290686 27.5629047,4.06014969 28.1150389,4.20059132 C28.9137259,4.74357385 29.6602644,5.35955176 30.3450389,6.04059132 Z"></path><path d="M30.7933348,15.9696304 C29.4433348,16.4596304 17.5233348,20.8296304 15.7933348,24.3896304 C15.3633836,25.3434159 15.3633836,26.4358449 15.7933348,27.3896304 C16.3919857,28.7511327 17.7917314,29.5797177 19.2733348,29.4496304 C19.6790059,29.4462835 20.0837104,29.4094922 20.4833348,29.3396304 C21.6712939,29.1478656 22.8417823,28.8602599 23.9833348,28.4796304 C24.9833348,28.1296304 25.9833348,27.6996304 26.9833348,27.2896304 L27.8133348,26.9496304 C30.7383636,25.6650404 33.5343329,24.1046551 36.1633348,22.2896304 C34.4182609,29.8341877 28.08799,35.4468207 20.3887538,36.2759094 C12.6895175,37.104998 5.30944369,32.968757 1.99813915,25.9686936 C-1.31316539,18.9686301 0.170333077,10.6395718 5.69491091,5.21327782 C11.2194887,-0.213016153 19.5738319,-1.54678143 26.5133348,1.88963044 C16.9833348,3.59963044 12.0633348,7.18963044 9.18333477,9.75963044 L8.86333477,10.0896304 C7.93333477,11.0396304 6.66333477,12.3296304 7.47333477,14.4196304 C7.9783997,15.5890182 9.06827343,16.4007074 10.3333348,16.5496304 L10.7033348,16.5496304 C11.6270831,16.4741709 12.5076261,16.1261302 13.2333348,15.5496304 C14.2333348,14.8796304 15.2333348,14.3096304 16.1533348,13.7996304 C21.2778133,11.1185988 26.6326398,8.90303934 32.1533348,7.17963044 C33.6733348,7.84963044 36.5733348,11.2496304 36.3833348,14.1796304 C34.6633348,14.7396304 32.2733348,15.4396304 30.7933348,15.9696304 Z"></path><path d="M34.3722313,17.49 C34.3726197,18.4970161 34.2754999,19.501704 34.0822313,20.49 C31.869491,22.1051295 29.5283527,23.5365684 27.0822313,24.77 C24.8622313,25.93 22.7822313,27.32 20.2422313,27.89 C19.0122313,28.16 16.9222313,27.42 17.0022313,25.89 C17.0622313,24.8 19.0022313,23.54 19.7922313,22.98 C21.2689784,21.9825786 22.8358063,21.1255103 24.4722313,20.42 C27.682491,19.0175052 30.9997216,17.8738632 34.3922313,17 C34.3622313,17.16 34.3722313,17.32 34.3722313,17.49 Z"></path></g></g></svg>' ), 2 );
        add_submenu_page( 'erp', __( 'Dashboard', 'erp' ), __( 'Dashboard', 'erp' ), 'manage_options', 'erp', [ $this, 'overview_page' ] );

        // add_menu_page( __( 'ERP', 'erp' ), 'ERP Settings', 'manage_options', 'erp-company', array( $this, 'company_page' ), 'dashicons-admin-settings', $this->get_menu_position() );

        // add_submenu_page( 'erp-company', __( 'Company', 'erp' ), __( 'Company', 'erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ) );
        // add_submenu_page( 'erp-company', __( 'Tools', 'erp' ), __( 'Tools', 'erp' ), 'manage_options', 'erp-tools', array( $this, 'tools_page' ) );
        // add_submenu_page( 'erp-company', __( 'Audit Log', 'erp' ), __( 'Audit Log', 'erp' ), 'manage_options', 'erp-audit-log', array( $this, 'log_page' ) );
        // add_submenu_page( 'erp-company', __( 'Settings', 'erp' ), __( 'Settings', 'erp' ), 'manage_options', 'erp-settings', array( $this, 'settings_page' ) );
        // add_submenu_page( 'erp-company', __( 'Status', 'erp' ), __( 'Status', 'erp' ), 'manage_options', 'erp-status', array( $this, 'status_page' ) );
        // add_submenu_page( 'erp-company', __( 'Modules', 'erp' ), __( 'Modules', 'erp' ), 'manage_options', 'erp-modules', array( $this, 'module' ) );
        // add_submenu_page( 'erp-company', __( 'Add-Ons', 'erp' ), __( 'Add-Ons', 'erp' ), 'manage_options', 'erp-addons', array( $this, 'addon_page' ) );
    }

    public function admin_settings() {
        do_action( 'erp_submenu_page' );
        add_submenu_page( 'erp', __( 'Company', 'erp' ), __( 'Company', 'erp' ), 'manage_options', 'erp-company', [ $this, 'company_page' ] );
        add_submenu_page( 'erp', __( 'Tools', 'erp' ), __( 'Tools', 'erp' ), 'manage_options', 'erp-tools', [ $this, 'tools_page' ] );
        add_submenu_page( 'erp', __( 'Modules', 'erp' ), __( 'Modules', 'erp' ), 'manage_options', 'erp-modules', [ $this, 'module' ] );
        add_submenu_page( 'erp', __( 'WP ERP Pro', 'erp' ), __( 'WP ERP Pro', 'erp' ), 'manage_options', 'erp-addons', [ $this, 'addon_page' ] );
        add_submenu_page( 'erp', __( 'Settings', 'erp' ), __( 'Settings', 'erp' ), 'manage_options', 'erp-settings', [ $this, 'router' ] );
        // If accounting module is active, then add Create the Invoice menu.
        if( wperp()->modules->is_module_active('accounting') ) {
            add_submenu_page( 'erp', __( 'Create Invoice', 'erp' ), __( 'Create Invoice', 'erp' ), 'manage_options', 'erp-accounting#/invoices/new', [ $this, 'router' ], 4 );
        }
        erp_add_menu( 'settings', [
            'title'         => __( 'Settings', 'erp' ),
            'slug'          => 'settings',
            'capability'    => 'manage_options',
            'callback'      => [ $this, 'settings_page' ],
            'position'      => 5,
        ] );

        if ( ! class_exists( 'WP_ERP_Pro' ) ) {
            $pro_menu = new AddProMenu();
            $pro_menu->add_pro_menu();
        }
    }

    /**
     * Route to approprite template according to current menu
     *
     * @since 1.3.14
     *
     * @return void
     */
    public function router() {
        $component   = 'settings';
        $menu        = erp_menu();
        $menu        = $menu[ $component ];
        $section     = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : false;
        $section     = $section && isset( $menu[ $section ] ) ? $section : 'settings';
        $sub_section = isset( $_GET['sub_section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_section'] ) ) : false;
        $sub_section = $sub_section && ! empty( $menu[ $section ]['submenu'][ $sub_section ] ) ? $sub_section : false;
        $callback    = $menu[ $section ]['callback'];

        if ( $sub_section ) {
            $callback = $menu[ $section ]['submenu'][ $sub_section ]['callback'];
        }

        call_user_func( $callback );
    }

    /**
     * Erp Settings page
     *
     * @return void
     */
    public function settings_page() {
        new \WeDevs\ERP\Settings\Settings();
    }

    /**
     * Erp Status page
     *
     * @return void
     */
    public function status_page() {
        new Status();
    }

    /**
     * Erp module
     *
     * @return void
     */
    public function module() {
        new AdminModule();
    }

    /**
     * Hide default WordPress menu's
     *
     * @return void
     */
    public function hide_admin_menus() {
        global $menu;

        $menus = get_option( '_erp_admin_menu', [] );

        if ( ! $menus ) {
            return;
        }

        foreach ( $menus as $item ) {
            $item = erp_serialize_string_to_array( $item );

            if ( ! empty( $item[2] ) ) {
                remove_menu_page( $item[2] );
            }
        }

        remove_menu_page( 'edit-tags.php?taxonomy=link_category' );
        remove_menu_page( 'separator1' );
        remove_menu_page( 'separator2' );
        remove_menu_page( 'separator-last' );

        $position        = 9998;
        $menu[$position] = [
            0   => '',
            1   => 'read',
            2   => 'separator' . $position,
            3   => '',
            4   => 'wp-menu-separator',
        ];
    }

    /**
     * Hide default admin bar links
     *
     * @return void
     */
    public function hide_admin_bar_links() {
        global $wp_admin_bar;

        $adminbar_menus = get_option( '_erp_adminbar_menu', [] );

        if ( ! $adminbar_menus ) {
            return;
        }

        foreach ( $adminbar_menus as $item ) {
            $wp_admin_bar->remove_menu( $item );
        }
    }

    /**
     * Render Overview page
     */
    public function overview_page() {
        include_once __DIR__ . '/views/erp-overview.php';
    }

    /**
     * Handles the company page
     *
     * @return void
     */
    public function company_page() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';

        switch ( $action ) {
            case 'edit':
                $company    = new \WeDevs\ERP\Company();
                $template   = WPERP_VIEWS . '/company-editor.php';
                break;

            default:
                $template = WPERP_VIEWS . '/company.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handles the company locations page
     *
     * @return void
     */
    public function locations_page() {
        include_once __DIR__ . '/views/locations.php';
    }

    /**
     * Handles the tools page
     *
     * @return void
     */
    public function tools_page() {
        include_once __DIR__ . '/views/tools.php';
    }

    /**
     * Handles the log page
     *
     * @return void
     */
    public function log_page() {
        include_once __DIR__ . '/views/log.php';
    }

    /**
     * Handles the log page
     *
     * @return void
     */
    public function addon_page() {
        wp_enqueue_style( 'erp-addons-fonts', 'https://fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap', [], WPERP_VERSION );
        include_once __DIR__ . '/views/add-ons.php';
    }
}
