<?php

namespace WeDevs\ERP\Admin;

class AddProMenu {

    /**
     * @var ProFeaturePreview
     */
    private $preview;

    public function __construct() {
        if ( class_exists( 'WP_ERP_Pro' ) ) {
            return;
        }

        $this->preview = new ProFeaturePreview();

        add_filter( 'erp_hr_people_menu_items', [ $this, 'add_org_chart_section' ] );
        add_action( 'erp_hr_org-chart_page', [ $this->preview, 'org_chart_page' ] );
        add_action( 'admin_footer', [ $this, 'pro_popup_js_templates' ] );
        add_filter( 'erp_hr_reports', [ $this, 'add_reports' ] );
        add_filter( 'erp_hr_reporting_pages', [ $this->preview, 'filter_report_template' ], 10, 2 );
        wp_enqueue_style( 'add-pro-popup' );
    }

    /**
     * Append an inline Pro badge to a menu title.
     * Uses a custom class so the JS popup handler on .pro-popup-main doesn't fire.
     *
     * @param string $title
     *
     * @return string
     */
    private function pro_title( $title ) {
        return $title . ' <span class="erp-pro-badge-nav">Pro</span>';
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
                    'title' => esc_html__( 'Org Chart', 'erp' ) . ' <span class="erp-pro-badge-nav">Pro</span>',
                    'cap'   => 'erp_list_employee',
                ],
            ] + array_slice( $sections, $index );

        return $sections;
    }

    /**
     * Add reports tab.
     *
     * @param $reports
     *
     * @return mixed
     */
    public function add_reports( $reports ) {
        $reports['asset-report'] = [
            'title'       => __( 'Assets', 'erp' ),
            'description' => __( 'Detailed report on Assets', 'erp' ),
            'pro_preview' => true,
        ];
        $reports['attendance-report'] = [
            'title'       => __( 'Attendance (Date Based)', 'erp' ),
            'description' => __( 'Reporting on employee attendance', 'erp' ),
            'pro_preview' => true,
        ];

        $reports['att-report-employee'] = [
            'title'       => __( 'Attendance (Employee Based)', 'erp' ),
            'description' => __( 'Reporting on employee attendance', 'erp' ),
            'pro_preview' => true,
        ];

        return $reports;
    }


    /**
     * Add pro menu items with preview pages in core plugin.
     *
     * @return void
     */
    public function add_pro_menu() {
        // Asset module.
        erp_add_menu( 'hr', [
            'title'      => $this->pro_title( __( 'Assets', 'erp' ) ),
            'slug'       => 'asset',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'assets_page' ],
            'position'   => 35,
        ] );

        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Assets', 'erp' ),
            'slug'       => 'asset',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'assets_page' ],
            'position'   => 1,
        ] );
        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Allotments', 'erp' ),
            'slug'       => 'asset-allottment',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'assets_page' ],
            'position'   => 5,
        ] );

        erp_add_submenu( 'hr', 'asset', [
            'title'      => __( 'Requests', 'erp' ),
            'slug'       => 'asset-request',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'assets_page' ],
            'position'   => 10,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'      => $this->pro_title( __( 'Assets', 'erp' ) ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'report&type=asset-report',
            'callback'   => [ $this->preview, 'assets_page' ],
            'position'   => 5,
        ] );

        // Attendance module.
        erp_add_menu( 'hr', [
            'title'      => $this->pro_title( __( 'Attendance', 'erp' ) ),
            'slug'       => 'attendance',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 34,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'      => __( 'Attendance', 'erp' ),
            'slug'       => 'attendance',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 36,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'      => __( 'Shifts', 'erp' ),
            'slug'       => 'shifts',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 37,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'      => __( 'Tools', 'erp' ),
            'slug'       => 'exim',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 38,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'      => __( 'Assign Bulk Shift', 'erp' ),
            'slug'       => 'assign-shift-bulk',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 39,
        ] );

        erp_add_submenu( 'hr', 'attendance', [
            'title'      => __( 'Settings', 'erp' ),
            'slug'       => 'settings',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 40,
        ] );

        // Report submenus for attendance
        erp_add_submenu( 'hr', 'report', [
            'title'      => $this->pro_title( __( 'Attendance (Date Based)', 'erp' ) ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'report&type=attendance-report',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 5,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'      => $this->pro_title( __( 'Attendance (Employee Based)', 'erp' ) ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'report&type=att-report-employee',
            'callback'   => [ $this->preview, 'attendance_page' ],
            'position'   => 5,
        ] );

        // Payroll module
        erp_add_menu( 'hr', [
            'title'      => $this->pro_title( __( 'Payroll', 'erp' ) ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'payroll',
            'callback'   => [ $this->preview, 'payroll_page' ],
            'position'   => 11,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Dashboard', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'dashboard',
            'callback'   => [ $this->preview, 'payroll_page' ],
            'position'   => 1,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Pay Calendar', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'calendar',
            'callback'   => [ $this->preview, 'payroll_calendar_page' ],
            'position'   => 5,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Pay Run List', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'payrun',
            'callback'   => [ $this->preview, 'payroll_payrun_page' ],
            'position'   => 10,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Bulk pay item edit', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'bulk-pay-item-edit',
            'callback'   => [ $this->preview, 'payroll_bulk_edit_page' ],
            'position'   => 11,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Reports', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'reports',
            'callback'   => [ $this->preview, 'payroll_reports_page' ],
            'position'   => 15,
        ] );

        erp_add_submenu( 'hr', 'payroll', [
            'title'      => __( 'Settings', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'settings',
            'callback'   => [ $this->preview, 'payroll_settings_page' ],
            'position'   => 20,
        ] );

        // Recruitment module
        erp_add_menu( 'hr', [
            'title'      => $this->pro_title( __( 'Recruitment', 'erp' ) ),
            'slug'       => 'recruitment',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 35,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Job Opening', 'erp' ),
            'slug'       => 'job-opening',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 1,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add Opening', 'erp' ),
            'slug'       => 'add-opening',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 5,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Question Sets', 'erp' ),
            'slug'       => 'question-sets',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 10,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Candidates', 'erp' ),
            'slug'       => 'jobseeker_list',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 15,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Calendar', 'erp' ),
            'slug'       => 'todo-calendar',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 20,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Reports', 'erp' ),
            'slug'       => 'reports',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 25,
        ] );

        erp_add_submenu( 'hr', 'recruitment', [
            'title'      => __( 'Add candidate', 'erp' ),
            'slug'       => 'add_candidate',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'recruitment_page' ],
            'position'   => 16,
        ] );

        // Document module
        erp_add_menu( 'hr', [
            'title'      => $this->pro_title( __( 'Documents', 'erp' ) ),
            'slug'       => 'documents',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'documents_page' ],
            'position'   => 35,
        ] );

        // Training module
        erp_add_menu( 'hr', [
            'title'      => $this->pro_title( __( 'Training', 'erp' ) ),
            'slug'       => 'training',
            'capability' => 'erp_hr_manager',
            'callback'   => [ $this->preview, 'training_page' ],
            'position'   => 35,
        ] );

        // Deals module (CRM - keep existing popup behavior)
        erp_add_menu( 'crm', [
            'title'       => __( 'Deals', 'erp' ),
            'capability'  => 'manage_options',
            'slug'        => 'deals',
            'callback'    => [ $this, 'admin_view_deals' ],
            'position'    => 11,
            'pro_popup'   => true,
            'direct_link' => '#',
        ] );

        erp_add_submenu( 'crm', 'deals', [
            'title'       => __( 'Dashboard', 'erp' ),
            'capability'  => 'manage_options',
            'slug'        => 'dashboard',
            'callback'    => [ $this, 'admin_view_deals' ],
            'position'    => 1,
            'pro_popup'   => true,
            'direct_link' => '#',
        ] );

        erp_add_submenu( 'crm', 'deals', [
            'title'       => __( 'All Deals', 'erp' ),
            'capability'  => 'manage_options',
            'slug'        => 'all-deals',
            'callback'    => [ $this, 'admin_view_deals' ],
            'position'    => 5,
            'pro_popup'   => true,
            'direct_link' => '#',
        ] );

        erp_add_submenu( 'crm', 'deals', [
            'title'       => __( 'Activities', 'erp' ),
            'capability'  => 'manage_options',
            'slug'        => 'activities',
            'callback'    => [ $this, 'admin_view_deals' ],
            'position'    => 10,
            'pro_popup'   => true,
            'direct_link' => '#',
        ] );

        erp_add_submenu( 'crm', 'deals', [
            'title'       => __( 'Settings', 'erp' ),
            'capability'  => 'manage_options',
            'slug'        => 'settings',
            'callback'    => '',
            'position'    => 15,
            'pro_popup'   => true,
            'direct_link' => '#',
        ] );

        // Integrations
        erp_add_menu( 'crm', [
            'title'       => __( 'Integrations', 'erp' ),
            'slug'        => 'integration',
            'capability'  => 'manage_options',
            'callback'    => [ $this, 'integration_page' ],
            'position'    => 40,
            'pro_popup'   => true,
            'direct_link' => '#',
        ] );

        // Reimbursement
        erp_add_submenu( 'accounting', 'transactions', [
            'title'       => __( 'Reimbursement', 'erp' ),
            'capability'  => 'manage_options',
            'slug'        => 'transactions',
            'callback'    => [ $this, 'reimbursement_page' ],
            'position'    => 190,
            'pro_popup'   => true,
            'direct_link' => 'transactions',
        ] );

        // Products > Inventory
        erp_add_submenu(
            'accounting', 'products', [
                'title'       => __( 'Inventory', 'erp' ),
                'capability'  => 'manage_options',
                'slug'        => 'products',
                'position'    => 15,
                'pro_popup'   => true,
                'direct_link' => 'products',
            ]
        );
    }


    /**
     * Print JS templates in footer
     *
     * @return void
     */
    public function pro_popup_js_templates() {
        erp_get_js_template( WPERP_INCLUDES . '/Admin/views/erp-pro-popup-modal.php', 'erp-pro-popup-modal' );
        ?>
        <script>
            jQuery(function($) {
                $('body').on('click', '.erp-pro-preview-action', function(e) {
                    e.preventDefault();
                    var formId = $(this).data('form');
                    if (formId && $('#' + formId).length) {
                        $.erpPopup({
                            title: $(this).data('form-title') || '',
                            button: '',
                            id: 'erp-pro-form-modal',
                            content: $('#' + formId).html(),
                            extraClass: 'larger',
                            footer: false
                        });
                    } else {
                        $.erpPopup({
                            title: '',
                            button: '',
                            id: 'erp-pro-popup-modal',
                            content: wperp.template('erp-pro-popup-modal'),
                            extraClass: 'larger',
                            footer: false
                        });
                    }
                });
            });
        </script>
        <?php
    }

}
