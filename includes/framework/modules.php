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
                'callback'    => '\WeDevs\ERP\HRM\Human_Resource',
                'modules'     => apply_filters( 'erp_hr_modules', [] ),
            ],
            'crm' => [
                'title'       => __( 'CR Management', 'erp' ),
                'slug'        => 'erp-crm',
                'description' => __( 'Customer Relationship Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\CRM\Customer_Relationship',
                'modules'     => apply_filters( 'erp_crm_modules', [] ),
            ],
            'accounting' => [
                'title'       => __( 'Accounting', 'erp' ),
                'slug'        => 'erp-accounting',
                'description' => __( 'Accounting Management', 'erp' ),
                'callback'    => '\WeDevs\ERP\Accounting\Accounting',
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

        foreach ( $active_modules as $module_name => $active_module ) {
            if ( in_array( $module_name, $modules ) ) {
                unset( $active_modules[ $module_name ] );
            }
        }

        update_option( 'erp_modules', $active_modules );

        return true;
    }

    /**
     * Get Modules Extensions
     *
     * @since 1.8.2
     *
     * @return array Module extensions with detail information
     */
    public function get_modules_extensions() {
        if ( ! $this->modules_extensions ) {
            $thumbnail_dir = WPERP_ASSETS . '/images/modules';

            $this->modules_extensions = apply_filters( 'wp_erp_pro_modules', [
                'advanced_leave' => [
                    'id'            => 'advanced_leave',
                    'version'       => '1.0.0',
                    'path'          => 'pro/advanced-leave',
                    'old_path'      => '',
                    'name'          => __('Advanced Leave Management', 'erp-pro'),
                    'description'   => __('Advanced Leave Management for WP ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/advance-leave-management.svg',
                    'module_file'   => WPERP_MODULES . '/pro/advanced-leave/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module',
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
                    'id'            => 'awesome_support',
                    'version'       => '1.0.1',
                    'path'          => 'pro/awesome-support',
                    'old_path'      => 'erp-awesome-support/erp-awesome-support.php',
                    'name'          => __('Awesome Support', 'erp-pro'),
                    'description'   => __('WP ERP and Awesome Support integration.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/awesome-support.svg',
                    'module_file'   => WPERP_MODULES . '/pro/awesome-support/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\AwesomeSupport\Module',
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
                    'id'            => 'gravity_forms',
                    'version'       => '1.1.1',
                    'path'          => 'pro/gravity_forms',
                    'old_path'      => 'erp-gravityforms/erp-gravityforms.php',
                    'name'          => __('Gravity Forms Integration', 'erp-pro'),
                    'description'   => __('Gravity Forms integration for WP ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/gravity-forms.svg',
                    'module_file'   => WPERP_MODULES . '/pro/gravity_forms/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\GravityForms\Module',
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
                    'id'            => 'help_scout',
                    'version'       => '1.1.2',
                    'path'          => 'pro/help-scout',
                    'old_path'      => 'erp-helpscout/erp-helpscout.php',
                    'name'          => __('HelpScout Integration', 'erp-pro'),
                    'description'   => __('HelpScout integration for WP ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/help-scout.svg',
                    'module_file'   => WPERP_MODULES . '/pro/help-scout/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\HelpScout\Module',
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
                    'id'            => 'hr_frontend',
                    'version'       => '2.1.2',
                    'path'          => 'pro/hr-frontend',
                    'old_path'      => 'erp-hr-frontend/erp-hr-frontend.php',
                    'name'          => __('HR Frontend', 'erp-pro'),
                    'description'   => __('Provides a brand new dashboard experience for WordPress ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/hr-frontend.svg',
                    'module_file'   => WPERP_MODULES . '/pro/hr-frontend/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\HR_Frontend\Module',
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
                    'id'            => 'hubspot',
                    'version'       => '1.1.1',
                    'path'          => 'pro/hubspot',
                    'old_path'      => 'erp-hubspot/erp-hubspot.php',
                    'name'          => __('HubSpot Contacts Sync', 'erp-pro'),
                    'description'   => __('Sync your CRM contacts with HubSpot.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/hubspot.svg',
                    'module_file'   => WPERP_MODULES . '/pro/hubspot/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Hubspot\Module',
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
                    'id'            => 'mailchimp',
                    'version'       => '1.1.1',
                    'path'          => 'pro/mailchimp',
                    'old_path'      => 'erp-mailchimp/erp-mailchimp.php',
                    'name'          => __('Mailchimp Contacts Sync', 'erp-pro'),
                    'description'   => __('Sync your CRM contacts with mailchimp.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/mailchimp-contacts-sync.svg',
                    'module_file'   => WPERP_MODULES . '/pro/mailchimp/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Mailchimp\Module',
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
                    'id'            => 'salesforce',
                    'version'       => '1.1.2',
                    'path'          => 'pro/salesforce',
                    'old_path'      => 'erp-salesforce/erp-salesforce.php',
                    'name'          => __('Salesforce Contacts Sync', 'erp-pro'),
                    'description'   => __('Sync your CRM contacts with salesforce.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/salesforce-contacts-sync.svg',
                    'module_file'   => WPERP_MODULES . '/pro/salesforce/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Salesforce\Module',
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
                    'id'            => 'zendesk',
                    'version'       => '1.1.3',
                    'path'          => 'pro/zendesk',
                    'old_path'      => 'erp-zendesk/erp-zendesk.php',
                    'name'          => __('Zendesk', 'erp-pro'),
                    'description'   => __('Zendesk integration for WP ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/zendesk.svg',
                    'module_file'   => WPERP_MODULES . '/pro/zendesk/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Zendesk\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/zendesk-integration/',
                    'module_link'   => 'https://wperp.com/downloads/zendesk-integration/',
                ],
                'asset_management' => [
                    'id'            => 'asset_management',
                    'version'       => '1.1.4',
                    'path'          => 'hrm/asset-management',
                    'old_path'      => 'erp-asset-management/erp-asset-management.php',
                    'name'          => __('Asset Manager', 'erp-pro'),
                    'description'   => __('Manage assets, allocate to employees and keep track.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/asset-management.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/asset-management/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\AssetManagement\Module',
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
                    'id'            => 'attendance',
                    'version'       => '2.0.7',
                    'path'          => 'hrm/attendance',
                    'old_path'      => 'erp-attendance/erp-attendance.php',
                    'name'          => __('Attendance', 'erp-pro'),
                    'description'   => __('Employee Attendance Add-On for WP ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/attendance.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/attendance/Module.php',
                    'module_class'  => 'WeDevs\ERP_PRO\HRM\Attendance\Module',
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
                    'id'            => 'custom_field_builder',
                    'version'       => '1.3.3',
                    'path'          => 'hrm/custom-field-builder',
                    'old_path'      => 'erp-field-builder/erp-field-builder.php',
                    'name'          => __('Custom Field Builder', 'erp-pro'),
                    'description'   => __('Adds extra custom fields to employee, contacts, companies and other people types.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/custom-field-builder.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/custom-field-builder/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\CustomFieldBuilder\Module',
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
                    'id'            => 'document_manager',
                    'version'       => '1.3.4',
                    'path'          => 'hrm/document-manager',
                    'old_path'      => 'erp-document/erp-document.php',
                    'name'          => __('Document Manager', 'erp-pro'),
                    'description'   => __('Manage your employee and company documents.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/document-manager.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/document-manager/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\DocumentManager\Module',
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
                    'id'            => 'hr_training',
                    'version'       => '1.1.3',
                    'path'          => 'hrm/hr-training',
                    'old_path'      => 'erp-hr-training/erp-hr-training.php',
                    'name'          => __('HR Training', 'erp-pro'),
                    'description'   => __('Employee Training Add-On for WP-ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/hr-training.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/hr-training/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\HrTraining\Module',
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
                    'id'            => 'payroll',
                    'version'       => '1.4.2',
                    'path'          => 'hrm/payroll',
                    'old_path'      => 'erp-payroll/erp-payroll.php',
                    'name'          => __('Payroll', 'erp-pro'),
                    'description'   => __('Manage your employee payroll.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/payroll.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/payroll/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Payroll\Module',
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
                    'id'            => 'recruitment',
                    'version'       => '1.3.3',
                    'path'          => 'hrm/recruitment',
                    'old_path'      => 'erp-recruitment/wp-erp-recruitment.php',
                    'name'          => __('Recruitment', 'erp-pro'),
                    'description'   => __('Recruitment solution for WP-ERP. Create job posting and hire employee for your company.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/recruitment.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/recruitment/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Recruitment\Module',
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
                    'id'            => 'reimbursement',
                    'version'       => '1.2.5',
                    'path'          => 'hrm/reimbursement',
                    'old_path'      => 'erp-reimbursement/reimbursement.php',
                    'name'          => __('Reimbursement', 'erp-pro'),
                    'description'   => __('Reimbursement addon for WP ERP - Accounting module.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/reimbursement.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/reimbursement/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Reimbursement\Module',
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
                    'id'            => 'sms_notification',
                    'version'       => '1.1.2',
                    'path'          => 'hrm/sms-notification',
                    'old_path'      => 'erp-sms-notification/erp-sms-notification.php',
                    'name'          => __('SMS Notification', 'erp-pro'),
                    'description'   => __('Send SMS notifications to employees and CRM contacts.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/sms-notification.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/sms-notification/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\SmsNotification\Module',
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
                    'id'            => 'workflow',
                    'version'       => '1.2.2',
                    'path'          => 'hrm/workflow',
                    'old_path'      => 'erp-workflow/erp-workflow.php',
                    'name'          => __('Workflow', 'erp-pro'),
                    'description'   => __('Workflow Automation System.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/workflow.svg',
                    'module_file'   => WPERP_MODULES . '/hrm/workflow/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Workflow\Module',
                    'is_pro'        => false,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/workflow/',
                    'module_link'   => 'https://wperp.com/downloads/workflow/',
                ],
                'deals' => [
                    'id'            => 'deals',
                    'version'       => '1.1.4',
                    'path'          => 'crm/deals',
                    'old_path'      => 'erp-deals/wp-erp-deals.php',
                    'name'          => __('Deals', 'erp-pro'),
                    'description'   => __('Deal Management add-on for WP ERP - CRM Module.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/deals.svg',
                    'module_file'   => WPERP_MODULES . '/crm/deals/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\CRM\Deals\Module',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/erp-deals/',
                    'module_link'   => 'https://wperp.com/downloads/deals/',
                ],
                'inventory' => [
                    'id'            => 'inventory',
                    'version'       => '1.3.3',
                    'path'          => 'accounting/inventory',
                    'old_path'      => 'erp-inventory/wp-erp-inventory.php',
                    'name'          => __('Inventory', 'erp-pro'),
                    'description'   => __('Manage and display your products purchase, order and stock.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/inventory.svg',
                    'module_file'   => WPERP_MODULES . '/accounting/inventory/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\Accounting\Inventory\Module',
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
                    'id'            => 'payment_gateway',
                    'version'       => '1.1.1',
                    'path'          => 'accounting/payment-gateway',
                    'old_path'      => 'erp-payment-gateway/erp-payment-gateway.php',
                    'name'          => __('Payment Gateway', 'erp-pro'),
                    'description'   => __('Manage all payment gateways for ERP Accounting module.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/payment-gateway.svg',
                    'module_file'   => WPERP_MODULES . '/accounting/payment-gateway/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\Accounting\PaymentGateway\Module',
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
                    'id'            => 'woocommerce',
                    'version'       => '1.3.3',
                    'path'          => 'accounting/woocommerce',
                    'old_path'      => 'erp-woocommerce/erp-woocommerce.php',
                    'name'          => __('WooCommerce', 'erp-pro'),
                    'description'   => __('WooCommerce integration with CRM and Accounting modules in ERP.', 'erp-pro'),
                    'thumbnail'     => $thumbnail_dir . '/woocommerce.svg',
                    'module_file'   => WPERP_MODULES . '/accounting/woocommerce/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\Accounting\woocommerce\Module',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/woocommerce-integration/',
                    'module_link'   => 'https://wperp.com/downloads/woocommerce-crm/',
                ],

            ]);
        }

        return $this->modules_extensions;
    }
}
