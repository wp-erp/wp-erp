<?php

namespace WeDevs\ERP\Framework;

use WeDevs\ERP\Framework\Traits\Hooker;
use WP_Error;

/**
 * Modules Class
 */
class Modules {

    /*
     * Hooks
     */
    use Hooker;

    /**
     * Hold the modules
     *
     * @var
     */
    protected $modules;

    /**
     * Hold the modules extensions
     *
     * @var
     */
    protected $modules_extensions;

    /**
     * initialize
     */
    public function __construct() {
        $this->init_modules();

        $this->get_modules_extensions();

        $this->action( 'erp_switch_redirect_to', 'module_switch_redirect', 10, 2 );
    }

    /**
     * Initialize the modules
     *
     * @return void
     */
    public function init_modules() {
        $this->modules = [
            'hrm' => [
                'title'       => __( 'HR Management', 'erp' ),
                'slug'        => 'erp-hrm',
                'description' => __( 'Human Resource Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\HRM\Main\HRM',
                'modules'     => apply_filters( 'erp_hr_modules', [] ),
            ],
            'crm' => [
                'title'       => __( 'CR Management', 'erp' ),
                'slug'        => 'erp-crm',
                'description' => __( 'Customer Relationship Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\CRM\Main\CRM',
                'modules'     => apply_filters( 'erp_crm_modules', [] ),
            ],
            'accounting' => [
                'title'       => __( 'Accounting', 'erp' ),
                'slug'        => 'erp-accounting',
                'description' => __( 'Accounting Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\Accounting\Main\Accounting',
                'modules'     => apply_filters( 'erp_ac_modules', [] ),
            ],
        ];
    }

    /**
     * Get all the registered modules
     *
     * @return mixed|void
     */
    public function get_modules() {
        return apply_filters( 'erp_get_modules', $this->modules );
    }

    /**
     * Get a module
     *
     * @param $module
     *
     * @return bool
     */
    public function get_module( $module ) {
        if ( array_key_exists( $module, $this->modules ) ) {
            return $this->modules[ $module ];
        }

        return false;
    }

    /**
     * Get the first module as default module
     *
     * @return mixed
     */
    public function get_default_module() {
        return reset( $this->modules );
    }

    /**
     * Get the current mode of the admin UI
     *
     * @return bool|mixed
     */
    public function get_current_module() {
        global $current_user;

        $mode = get_user_meta( $current_user->ID, '_erp_mode', true );

        if ( !empty( $mode ) && $this->get_module( $mode ) ) {
            return $this->get_module( $mode );
        }

        return $this->get_default_module();
    }

    /**
     * Redirect to the CRM/HRM dashboard page when switching from admin menu bar
     *
     * @param $url
     * @param $new_mode
     *
     * @return string|void
     */
    public function module_switch_redirect( $url, $new_mode ) {
        if ( 'crm' == $new_mode ) {
            return admin_url( 'admin.php?page=erp-crm' );
        } elseif ( 'hrm' == $new_mode ) {
            return admin_url( 'admin.php?page=erp-hrm' );
        }

        return $url;
    }

    /**
     * Get module status
     *
     * @param string $module_key
     *
     * @since 0.1
     *
     * @return bool
     */
    public function is_module_active( $module_key ) {
        $modules = $this->get_active_modules();

        if ( array_key_exists( $module_key, $modules ) ) {
            return true;
        }

        return false;
    }

    /**
     * Get active modules
     *
     * @since 0.1
     *
     * @return array
     */
    public function get_active_modules() {
        return get_option( 'erp_modules', [] );
    }

    /**
     * Get inactive modules
     *
     * @since 0.1
     *
     * @return array
     */
    public function get_inactive_modules() {
        $all_modules    = $this->get_modules();
        $active_modules = $this->get_active_modules();

        foreach ( $active_modules as $key => $module ) {
            unset( $all_modules[$key] );
        }

        return $all_modules;
    }

    /**
     * Get modules by query pram
     *
     * @param string $tab
     *
     * @since 0.1
     *
     * @return array
     */
    public function get_query_modules( $tab = false ) {
        switch ( $tab ) {
            case 'active':
                return $this->get_active_modules();
                break;

            case 'inactive':
                return $this->get_inactive_modules();
                break;

            default:
                return $this->get_modules();
                break;
        }
    }

    /**
     * Check for valid ERP modules
     *
     * @since 1.2.2
     *
     * @param array $modules Name of the module or modules
     *
     * @return mixed WP_Error if an invalid name is given or true on success
     */
    public function is_valid_module( $modules ) {
        if ( ! is_array( $modules ) ) {
            $modules = [ $modules ];
        }

        $erp_module_names = array_keys( $this->modules );

        foreach ( $modules as $module_name ) {
            if ( ! in_array( $module_name, $erp_module_names ) ) {
                return new WP_Error( 'invalid-module-name', sprintf( __( 'Invalid module name %s', 'erp' ), $module_name ) );
            }
        }

        return true;
    }

    /**
     * Activate single or multiple modules
     *
     * @since 1.2.2
     *
     * @param array $modules Name of the module or modules
     *
     * @return mixed WP_Error if an invalid name is given or true on success
     */
    public function activate_modules( $modules ) {
        if ( ! is_array( $modules ) ) {
            $modules = [ $modules ];
        }

        $is_valid_module = $this->is_valid_module( $modules );

        if ( is_wp_error( $is_valid_module ) ) {
            return $is_valid_module;
        }

        $active_modules = $this->get_active_modules();

        foreach ( $modules as $module_name ) {
            if ( ! in_array( $module_name, $active_modules ) ) {
                $active_modules[ $module_name ] = $this->modules[ $module_name ];
            }
        }

        update_option( 'erp_modules', $active_modules );

        return true;
    }

    /**
     * Deactivate single or multiple modules
     *
     * @since 1.2.2
     *
     * @param array $modules Name of the module or modules
     *
     * @return mixed WP_Error if an invalid name is given or true on success
     */
    public function deactivate_modules( $modules ) {
        if ( ! is_array( $modules ) ) {
            $modules = [ $modules ];
        }

        $is_valid_module = $this->is_valid_module( $modules );

        if ( is_wp_error( $is_valid_module ) ) {
            return $is_valid_module;
        }

        $active_modules = $this->get_active_modules();
        $deactivated    = [];

        foreach ( $active_modules as $module_name => $active_module ) {
            if ( in_array( $module_name, $modules, true ) ) {
                unset( $active_modules[ $module_name ] );
                $deactivated[] = $module_name;
            }
        }

        update_option( 'erp_modules', $active_modules );

        foreach ( $deactivated as $module_name ) {
            // action to do additional tasks when module deactivated
            do_action( 'erp_module_after_deactivated', $module_name );
        }

        return true;
    }

    /**
     * Get Modules Extensions
     *
     * @since 1.8.3
     *
     * @return array Module extensions with detail information
     */
    public function get_modules_extensions() {
        if ( ! $this->modules_extensions ) {
            $thumbnail_dir  = WPERP_ASSETS . '/images/modules';

            $this->modules_extensions = [
                'inventory' => [
                    'name'          => __('Inventory', 'erp'),
                    'description'   => __('Manage and display your products purchase, order and stock.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/inventory.png',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/inventory/',
                    'module_link'   => 'https://wperp.com/downloads/inventory/',
                ],
                'payment_gateway' => [
                    'name'          => __('Payment Gateway', 'erp'),
                    'description'   => __('Manage all payment gateways for ERP Accounting module.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/payment-gateway.png',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/payment-gateway/',
                    'module_link'   => 'https://wperp.com/downloads/payment-gateway/',
                ],
                'woocommerce' => [
                    'name'          => __('WooCommerce', 'erp'),
                    'description'   => __('WooCommerce integration with CRM and Accounting modules in ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/woocommerce.png',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'crm', 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/woocommerce-integration/',
                    'module_link'   => 'https://wperp.com/downloads/woocommerce-crm/',
                ],
                'deals' => [
                    'name'          => __('Deals', 'erp'),
                    'description'   => __('Deal Management add-on for WP ERP - CRM Module.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/deals.png',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/erp-deals/',
                    'module_link'   => 'https://wperp.com/downloads/deals/',
                ],
                'asset_management' => [
                    'name'          => __('Asset Manager', 'erp'),
                    'description'   => __('Manage assets, allocate to employees and keep track.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/asset-management.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/asset-manager/',
                    'module_link'   => 'https://wperp.com/downloads/asset-manager/',
                ],
                'attendance' => [
                    'name'          => __('Attendance', 'erp'),
                    'description'   => __('Employee Attendance Add-On for WP ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/attendance.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/attendance-management/',
                    'module_link'   => 'https://wperp.com/downloads/attendance/',
                ],
                'custom_field_builder' => [
                    'name'          => __('Custom Field Builder', 'erp'),
                    'description'   => __('Adds extra custom fields to employee, contacts, companies and other people types.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/custom-field-builder.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/custom-field-builder/',
                    'module_link'   => 'https://wperp.com/downloads/custom-field-builder/',
                ],
                'document_manager' => [
                    'name'          => __('Document Manager', 'erp'),
                    'description'   => __('Manage your employee and company documents.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/document-manager.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/document-manager/',
                    'module_link'   => 'https://wperp.com/downloads/document-manager/',
                ],
                'hr_training' => [
                    'name'          => __('HR Training', 'erp'),
                    'description'   => __('Employee Training Add-On for WP-ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/hr-training.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/training/',
                    'module_link'   => 'https://wperp.com/downloads/training/',
                ],
                'payroll' => [
                    'name'          => __('Payroll', 'erp'),
                    'description'   => __('Manage your employee payroll.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/payroll.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/payroll/',
                    'module_link'   => 'https://wperp.com/downloads/payroll/',
                ],
                'recruitment' => [
                    'name'          => __('Recruitment', 'erp'),
                    'description'   => __('Recruitment solution for WP-ERP. Create job posting and hire employee for your company.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/recruitment.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/recruitment/',
                    'module_link'   => 'https://wperp.com/downloads/recruitment/',
                ],
                'reimbursement' => [
                    'name'          => __('Reimbursement', 'erp'),
                    'description'   => __('Reimbursement addon for WP ERP - Accounting module.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/reimbursement.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'hrm', 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/reimbursement/',
                    'module_link'   => 'https://wperp.com/downloads/reimbursement/',
                ],
                'sms_notification' => [
                    'name'          => __('SMS Notification', 'erp'),
                    'description'   => __('Send SMS notifications to employees and CRM contacts.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/sms-notification.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/sms-notification/',
                    'module_link'   => 'https://wperp.com/downloads/sms-notification/',
                ],
                'workflow' => [
                    'name'          => __('Workflow', 'erp'),
                    'description'   => __('Workflow Automation System.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/workflow.png',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/workflow/',
                    'module_link'   => 'https://wperp.com/downloads/workflow/',
                ],
                'advanced_leave' => [
                    'name'          => __('Advanced Leave Management', 'erp'),
                    'description'   => __('Advanced Leave Management for WP ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/advance-leave-management.png',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hr/advanced-leave-management/',
                    'module_link'   => 'https://wperp.com/downloads/advanced-leave-management/',
                ],
                'awesome_support' => [
                    'name'          => __('Awesome Support', 'erp'),
                    'description'   => __('WP ERP and Awesome Support integration.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/awesome-support.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/awesome-support-sync/',
                    'module_link'   => 'https://wperp.com/downloads/awesome-support-sync/',
                ],
                'gravity_forms' => [
                    'name'          => __('Gravity Forms Integration', 'erp'),
                    'description'   => __('Gravity Forms integration for WP ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/gravity-forms.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/gravity-forms/',
                    'module_link'   => 'https://wperp.com/downloads/crm-gravity-forms/',
                ],
                'help_scout' => [
                    'name'          => __('HelpScout Integration', 'erp'),
                    'description'   => __('HelpScout integration for WP ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/help-scout.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/help-scout/',
                    'module_link'   => 'https://wperp.com/downloads/help-scout-integration/',
                ],
                'hr_frontend' => [
                    'name'          => __('HR Frontend', 'erp'),
                    'description'   => __('Provides a brand new dashboard experience for WordPress ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/hr-frontend.png',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/hr-frontend/',
                    'module_link'   => 'https://wperp.com/downloads/hr-frontend/',
                ],
                'hubspot' => [
                    'name'          => __('HubSpot Contacts Sync', 'erp'),
                    'description'   => __('Sync your CRM contacts with HubSpot.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/hubspot.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/hubspot-contacts-sync/',
                    'module_link'   => 'https://wperp.com/downloads/hubspot-contacts-sync/',
                ],
                'mailchimp' => [
                    'name'          => __('Mailchimp Contacts Sync', 'erp'),
                    'description'   => __('Sync your CRM contacts with mailchimp.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/mailchimp-contacts-sync.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/mailchimp-contacts-sync/',
                    'module_link'   => 'https://wperp.com/downloads/mailchimp-contacts-sync/',
                ],
                'salesforce' => [
                    'name'          => __('Salesforce Contacts Sync', 'erp'),
                    'description'   => __('Sync your CRM contacts with salesforce.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/salesforce-contacts-sync.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/salesforce-contacts-sync/',
                    'module_link'   => 'https://wperp.com/downloads/salesforce-contact-sync/',
                ],
                'zendesk' => [
                    'name'          => __('Zendesk', 'erp'),
                    'description'   => __('Zendesk integration for WP ERP.', 'erp'),
                    'thumbnail'     => $thumbnail_dir . '/zendesk.png',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/zendesk-integration/',
                    'module_link'   => 'https://wperp.com/downloads/zendesk-integration/',
                ],
            ];
        }

        return apply_filters( 'erp_pro_modules_details', $this->modules_extensions );
    }
}
