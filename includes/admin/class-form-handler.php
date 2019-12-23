<?php
namespace WeDevs\ERP\Admin;
use WeDevs\ERP\Company;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Admin form handler
 *
 * Handles all the form submission
 */
class Form_Handler {

    use Hooker;

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->action( 'erp_action_create_new_company', 'create_new_company' );

        $this->action( 'admin_init', 'save_settings' );
        $this->action( 'admin_init', 'tools_general' );
        $this->action( 'admin_init', 'tools_test_mail' );

        $erp_settings = sanitize_title( esc_html__( 'ERP Settings', 'erp' ) );
        add_action( "load-{$erp_settings}_page_erp-audit-log", array( $this, 'audit_log_bulk_action' ) );
    }

    /**
     * Save all settings
     *
     * @since 0.1
     *
     * @return void
     */
    public function save_settings() {
        if ( ! isset( $_POST['erp_module_status'] ) ) {
            return;
        }

        if ( ! isset( $_POST['erp_settings'] ) || ! wp_verify_nonce( sanitize_key( $_POST['erp_settings'] ), 'erp_nonce' ) ) {
            return;
        }

        $inactive    = ( isset( $_GET['tab'] ) && $_GET['tab'] == 'inactive' ) ? true : false;
        $modules     = isset( $_POST['modules'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['modules'] ) ) : array();
        $all_modules = wperp()->modules->get_modules();

        foreach ( $all_modules as $key => $module ) {
            if ( ! in_array( $key, $modules ) ) {
                unset( $all_modules[$key] );
            }
        }

        if ( $inactive ) {
            $active_modules = wperp()->modules->get_active_modules();
            $all_modules    = array_merge( $all_modules, $active_modules );
        }
        update_option( 'erp_modules', $all_modules );
        wp_redirect( isset( $_POST['_wp_http_referer'] ) ? sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) : '' );
        exit();
    }

    /**
     * Check is valid input or not
     *
     * @since 0.1
     *
     * @param  array  $array
     * @param  string  $key
     *
     * @return boolean
     */
    public function is_valid_input( $array, $key ) {
        if ( ! isset( $array[$key]) || empty( $array[$key] ) || $array[$key] == '-1' ) {
            return false;
        }

        return true;
    }

    /**
     * Create a new company
     *
     * @return void
     */
    public function create_new_company() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-new-company' ) ) {
            wp_die( esc_html__( 'Cheating?', 'erp' ) );
        }

        $posted   = array_map( 'strip_tags_deep', $_POST );
        $posted   = array_map( 'trim_deep', $posted );

        $errors   = [];
        $required = [
            'name'    => esc_html__( 'Company name', 'erp' ),
            'address' => [
                'country' => esc_html__( 'Country', 'erp' )
            ]
        ];

        if ( ! $this->is_valid_input( $posted, 'name' ) ) {
            $errors[] = 'error-company=1';
        }

        if ( ! $this->is_valid_input( $posted['address'], 'country' ) ) {
            $errors[] = 'error-country=1';
        }

        if ( $errors ) {
            $args = implode( '&' , $errors );
            $redirect_to = admin_url( 'admin.php?page=erp-company&action=edit&msg=error&' . $args );
            wp_redirect( $redirect_to );
            exit;
        }

        $args = [
            'logo'    => isset( $posted['company_logo_id'] ) ? absint( $posted['company_logo_id'] ) : 0,
            'name'    => $posted['name'],
            'address' => [
                'address_1' => $posted['address']['address_1'],
                'address_2' => $posted['address']['address_2'],
                'city'      => $posted['address']['city'],
                'state'     => $posted['address']['state'],
                'zip'       => $posted['address']['zip'],
                'country'   => $posted['address']['country'],
            ],
            'phone'   => $posted['phone'],
            'fax'     => $posted['fax'],
            'mobile'  => $posted['mobile'],
            'website' => $posted['website'],
            'business_type' => $posted['business_type']
        ];

        $company = new Company();
        $company->update( $args );

        $redirect_to = esc_url( admin_url( 'admin.php?page=erp-company&action=edit&msg=updated' ) );
        wp_redirect( $redirect_to );
        exit;
    }

    /**
     * Handle audit log bulk action
     *
     * @since 0.1
     *
     * @return void
     */
    public function audit_log_bulk_action() {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] != 'erp-audit-log' ) {
            return;
        }

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-audit_logs' ) ) {
            return;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'filter_audit_log' ), $request_uri );
        wp_redirect( $redirect );
        exit();
    }

    /**
     * Handle all the forms in the tools page
     *
     * @return void
     */
    public function tools_general() {

        // admin menu form
        if ( isset( $_POST['erp_admin_menu'], $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-remove-menu-nonce' ) ) {

            $menu     = isset( $_POST['menu'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['menu'] ) ) : [];
            $bar_menu = isset( $_POST['admin_menu'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['admin_menu'] ) ) : [];

            update_option( '_erp_admin_menu', $menu );
            update_option( '_erp_adminbar_menu', $bar_menu );
        }
    }

    /**
     * Send test email
     *
     * @since 1.1.2
     *
     * @return void
     */
    public function tools_test_mail() {
        if ( isset( $_POST['erp_send_test_email'], $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-test-email-nonce' ) ) {

            $to      = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';
            $subject = sprintf( __( 'Test email from %s', 'erp' ), get_bloginfo( 'name' ) );
            $body    = isset( $_POST['body'] ) ? sanitize_text_field( wp_unslash( $_POST['body'] ) ) : '';

            if ( empty( $body ) ) {
                $body = sprintf( __( 'This test email proves that your WordPress installation at %1$s can send emails.\n\nSent: %2$s', 'erp' ), get_bloginfo( 'url' ), date( 'r' ) );
            }

            erp_mail( $to, $subject, $body );

            $redirect_to = esc_url( admin_url( 'admin.php?page=erp-tools&tab=misc&sent=true' ) );
            wp_redirect( $redirect_to );
            exit;
        }
    }

}

new Form_Handler();
