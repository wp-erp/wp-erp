<?php

namespace WeDevs\ERP\Admin;

class AddProMenu {

    public function __construct() {
        add_filter( 'erp_hr_people_menu_items', [ $this, 'add_org_chart_section' ] );
    }

    /**
     * Registers Org chart section in people submenu
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_org_chart_section( $sections ) {
        $index = array_search( 'announcement', array_keys( $sections ), true );

        if ( false === $index ) {
            $index = count( $sections );
        }

        $sections = array_slice( $sections, 0, $index ) + [
			'org-chart' => [
				'title'     => esc_html__( 'Org Chart', 'erp' ),
				'cap'       => 'erp_list_employee',
				'pro_popup' => true,
			],
		] + array_slice( $sections, $index );

        return $sections;
    }

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
            'callback'   => '',
            'position'   => 35,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Assets', 'erp' ),
            'slug'       => 'asset',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 1,
            'pro_popup'  => true,
        ] );
        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Allotments', 'erp' ),
            'slug'       => 'asset-allottment',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Requests', 'erp' ),
            'slug'       => 'asset-request',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
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
            'callback'   => '',
            'position'   => 11,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Dashboard', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'dashboard',
            'callback'   => '',
            'position'   => 1,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Pay Calendar', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'calendar',
            'callback'   => '',
            'position'   => 5,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Pay Run List', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'payrun',
            'callback'   => '',
            'position'   => 10,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Bulk pay item edit', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'bulk-pay-item-edit',
            'callback'   => '',
            'position'   => 11,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Reports', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'reports',
            'callback'   => '',
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
            'callback'   => '',
            'position'   => 35,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Job Opening', 'erp' ),
            'slug'       => 'job-opening',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 1,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add Opening', 'erp' ),
            'slug'       => 'add-opening',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
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
            'callback'   => '',
            'position'   => 15,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Calendar', 'erp' ),
            'slug'       => 'todo-calendar',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 20,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Reports', 'erp' ),
            'slug'       => 'reports',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 25,
            'pro_popup'  => true,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add candidate', 'erp' ),
            'slug'       => 'add_candidate',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
            'position'   => 16,
            'pro_popup'  => true,
        ] );

        // Document module
        erp_add_menu( 'hr', [
            'title'      => __( 'Documents', 'erp' ),
            'slug'       => 'documents',
            'capability' => 'erp_hr_manager',
            'callback'   => '',
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
