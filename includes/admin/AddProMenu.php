<?php

namespace WeDevs\ERP\Admin;

class AddProMenu {


    /**
     * Add pro menu popup in core plugins.
     *
     * @return void
     */
    public function add_pro_menu() {
        if ( class_exists( 'WP_ERP_Pro' ) ) {
            return;
        }

        // License menu page
        add_submenu_page( 'erp', __( 'License', 'erp' ), sprintf( '<span class="pro-popup-main">%s<span class="pro-popup-nav">%s</span></span>', __( 'License', 'erp' ), __( 'Pro', 'erp' ) ), 'manage_options', 'erp-license', [ $this, 'license_menu' ] );

        // Asset module.
        erp_add_menu( 'hr', [
            'title'      => __( 'Assets', 'erp' ),
            'slug'       => 'asset',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'assets_page' ],
            'position'   => 35,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Assets', 'erp' ),
            'slug'       => 'asset',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'assets_page' ],
            'position'   => 1,
            'pro_popup'  => true,
        ] );
        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Allotments', 'erp' ),
            'slug'       => 'asset-allottment',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'allotment_page' ],
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Requests', 'erp' ),
            'slug'       => 'asset-request',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'request_page' ],
            'position'   => 10,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'      => __( 'Assets', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'report&type=asset-report',
            'callback'   => '',
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        // Attendance module.
        erp_add_menu( 'hr', [
            'title'      => __( 'Attendance', 'erp' ),
            'slug'       => 'attendance',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 34,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'       => __( 'Attendance', 'erp' ),
            'slug'        => 'attendance',
            'direct_link' => admin_url( 'admin.php?page=erp-hr&section=attendance#/' ),
            'capability'  => 'erp_hr_manager',
            'callback'    => '',
            'position'    => 36,
            'pro_popup'   => true,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'       => __( 'Shifts', 'erp' ),
            'slug'        => 'shifts',
            'direct_link' => admin_url( 'admin.php?page=erp-hr&section=attendance#/shifts' ),
            'capability'  => 'erp_hr_manager',
            'callback'    => '',
            'position'    => 37,
            'pro_popup'   => true,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'       => __( 'Tools', 'erp' ),
            'slug'        => 'exim',
            'direct_link' => admin_url( 'admin.php?page=erp-hr&section=attendance#/exim' ),
            'capability'  => 'erp_hr_manager',
            'callback'    => '',
            'position'    => 38,
            'pro_popup'   => true,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'       => __( 'Assign Bulk Shift', 'erp' ),
            'slug'        => 'assign-shift-bulk',
            'direct_link' => admin_url( 'admin.php?page=erp-hr&section=attendance#/assign-shift-bulk' ),
            'capability'  => 'erp_hr_manager',
            'callback'    => '',
            'position'    => 39,
            'pro_popup'   => true,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'       => __( 'Settings', 'erp' ),
            'slug'        => 'settings',
            'direct_link' => admin_url( 'admin.php?page=erp-settings#/erp-hr/attendance' ),
            'capability'  => 'erp_hr_manager',
            'callback'    => '',
            'position'    => 40,
            'pro_popup'   => true,
        ] );

        // Report module
        erp_add_submenu( 'hr', 'report', [
            'title'      => __( 'Attendance (Date Based)', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'report&type=attendance-report',
            'callback'   => '',
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'      => __( 'Attendance (Employee Based)', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'report&type=att-report-employee',
            'callback'   => '',
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        // Payroll module
        erp_add_menu( 'hr', [
            'title'      => __( 'Payroll', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'payroll',
            'callback'   => [ $this, 'dashboard_page' ],
            'position'   => 11,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Dashboard', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'dashboard',
            'callback'   => [ $this, 'dashboard_page' ],
            'position'   => 1,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Pay Calendar', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'calendar',
            'callback'   => [ $this, 'pay_calendar_page' ],
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Pay Run List', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'payrun',
            'callback'   => [ $this, 'pay_run_page' ],
            'position'   => 10,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Bulk pay item edit', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'bulk-pay-item-edit',
            'callback'   => [ $this, 'bulk_edit_item' ],
            'position'   => 11,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Reports', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'reports',
            'callback'   => [ $this, 'reports_page' ],
            'position'   => 15,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'       => __( 'Settings', 'erp' ),
            'capability'  => 'erp_hr_manager',
            'direct_link' => admin_url( 'admin.php?page=erp-settings#/erp-hr/payroll' ),
            'slug'        => 'settings',
            'callback'    => '',
            'position'    => 20,
            'pro_popup'   => true,
        ] );

        // Recruitment module
        erp_add_menu( 'hr', [
            'title'      => __( 'Recruitment', 'erp' ),
            'slug'       => 'recruitment',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'job_opening_list_page' ],
            'position'   => 35,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Job Opening', 'erp' ),
            'slug'       => 'job-opening',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'job_opening_list_page' ],
            'position'   => 1,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add Opening', 'erp' ),
            'slug'       => 'add-opening',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'job_description_step' ],
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'       => __( 'Question Sets', 'erp' ),
            'slug'        => '',
            'capability'  => '',
            'callback'    => '',
            'direct_link' => admin_url( 'edit.php?post_type=erp_hr_questionnaire' ),
            'position'    => 10,
            'pro_popup'   => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Candidates', 'erp' ),
            'slug'       => 'jobseeker_list',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'candidate_page' ],
            'position'   => 15,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Calendar', 'erp' ),
            'slug'       => 'todo-calendar',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'todo_calendar_page' ],
            'position'   => 20,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Reports', 'erp' ),
            'slug'       => 'reports',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'opening_reports_page' ],
            'position'   => 25,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add candidate', 'erp' ),
            'slug'       => 'add_candidate',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'add_candidate' ],
            'position'   => 16,
            'pro_popup'  => true,
        ] );

        // Document module
        erp_add_menu( 'hr', [
            'title'      => __( 'Documents', 'erp' ),
            'slug'       => 'documents',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this, 'erp_hr_company_document_tab' ],
            'position'   => 35,
            'pro_popup'  => true,
        ] );

        // Training module
        erp_add_menu( 'hr', [
            'title'       => __( 'Training', 'erp' ),
            'slug'        => '[fd[gp[rg',
            'callback'    => [],
            'capability'  => 'erp_hr_manager',
            'direct_link' => admin_url( 'edit.php?post_type=erp_hr_training' ),
            'position'    => 35,
            'pro_popup'   => true,
        ] );
    }

}
