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
        add_filter( 'erp_settings_hr_sections', [ $this, 'add_payroll_settings_section' ] );
        add_filter( 'erp_settings_hr_section_fields', [ $this, 'add_payroll_settings_fields' ], 10, 2 );
        add_action( 'erp_settings_loaded', [ $this, 'add_payroll_settings_route' ] );
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
     * Inject Vue routes and Pro badge for the Payroll settings section.
     *
     * The settings SPA has hardcoded routes; dynamically added sections
     * via PHP filters need a matching Vue route to render.
     *
     * @return void
     */
    public function add_payroll_settings_route() {
        $js = <<<'JS'
(function() {
    var BaseLayout = window.settings_get_lib('BaseLayout');

    /**
     * Shared payroll layout: renders BaseLayout with sub-sub-menu tabs.
     * Mirrors the Pro's HrPayroll.vue component structure.
     */
    var PayrollLayout = {
        components: { 'base-layout': BaseLayout },
        props: { activeTab: String },
        data: function() {
            return { subSections: {}, sectionTitle: '' };
        },
        created: function() {
            var menus = erp_settings_var.erp_settings_menus;
            var parentMenu = menus.find(function(m) { return m.id === 'erp-hr'; });
            if (parentMenu) {
                this.sectionTitle = parentMenu.sections['payroll'] || 'Payroll';
                var payrollData = parentMenu.fields['payroll'] || {};
                this.subSections = payrollData.sub_sections || {};
            }
        },
        render: function(h) {
            var self = this;
            var tabs = [];
            Object.keys(this.subSections).forEach(function(key) {
                tabs.push(h('li', { key: key }, [
                    h('router-link', {
                        props: { to: '/erp-hr/payroll/' + key },
                        class: { 'router-link-active': self.activeTab === key }
                    }, [h('span', { class: 'menu-name' }, self.subSections[key])])
                ]));
            });

            return h('base-layout', {
                props: { section_id: 'erp-hr', sub_section_id: 'payroll', enable_content: false }
            }, [
                h('h3', { class: 'sub-section-title' }, this.sectionTitle),
                h('div', [
                    h('ul', { class: 'sub-sub-menu' }, tabs),
                    this.$slots.default
                ])
            ]);
        }
    };

    /**
     * Payment Settings tab — disabled preview.
     */
    var PaymentPreview = {
        components: { 'payroll-layout': PayrollLayout },
        render: function(h) {
            return h('payroll-layout', { props: { activeTab: 'payment' } }, [
                h('form', { class: 'wperp-form', on: { submit: function(e) { e.preventDefault(); } } }, [
                    h('h3', { class: 'sub-sub-title' }, 'Payment Method Selection'),
                    h('div', { class: 'wperp-form-group' }, [
                        h('label', 'Select a method'),
                        h('select', { class: 'wperp-form-field', attrs: { disabled: true } }, [
                            h('option', { attrs: { value: 'cash' } }, 'Cash'),
                            h('option', { attrs: { value: 'cheque' } }, 'Cheque'),
                            h('option', { attrs: { value: 'bank' } }, 'Bank')
                        ])
                    ]),
                    h('div', { class: 'wperp-form-group' }, [
                        h('label', 'Select a bank'),
                        h('select', { class: 'wperp-form-field', attrs: { disabled: true } }, [
                            h('option', '— Select Bank —')
                        ])
                    ]),
                    h('div', { class: 'wperp-form-group' }, [
                        h('button', {
                            class: 'wperp-btn wperp-btn-primary',
                            attrs: { type: 'submit', disabled: true }
                        }, 'Save Changes'),
                        h('div', { class: 'clearfix' })
                    ])
                ])
            ]);
        }
    };

    /**
     * Pay Item Settings tab — disabled preview with sample data table.
     */
    var PayItemPreview = {
        components: { 'payroll-layout': PayrollLayout },
        render: function(h) {
            var sampleItems = [
                { type: 'Allowance', item: 'Travel Allowance', amount: 'Addition' },
                { type: 'Allowance', item: 'Accommodation Allowance', amount: 'Addition' },
                { type: 'Allowance', item: 'City Compensatory Allowance', amount: 'Addition' }
            ];

            var rows = sampleItems.map(function(row, i) {
                return h('tr', { key: i, attrs: { valign: 'top' } }, [
                    h('td', row.type),
                    h('td', row.item),
                    h('td', row.amount),
                    h('td', [
                        h('span', { class: 'action', style: { cursor: 'not-allowed', opacity: '0.5', marginRight: '8px' } }, [
                            h('span', { class: 'dashicons dashicons-edit' })
                        ]),
                        h('span', { class: 'action', style: { cursor: 'not-allowed', opacity: '0.5' } }, [
                            h('span', { class: 'dashicons dashicons-trash' })
                        ])
                    ])
                ]);
            });

            return h('payroll-layout', { props: { activeTab: 'payitem' } }, [
                h('form', { class: 'wperp-form', on: { submit: function(e) { e.preventDefault(); } } }, [
                    h('h3', { class: 'sub-sub-title' }, 'Pay Item Settings'),
                    h('div', { class: 'wperp-form-group' }, [
                        h('label', 'Pay Type'),
                        h('select', { class: 'wperp-form-field', attrs: { disabled: true } }, [
                            h('option', { attrs: { value: 'earning' } }, 'Earning'),
                            h('option', { attrs: { value: 'deduction' } }, 'Deduction')
                        ])
                    ]),
                    h('div', { class: 'wperp-form-group' }, [
                        h('label', 'Pay Item'),
                        h('input', { class: 'wperp-form-field', attrs: { type: 'text', disabled: true } })
                    ]),
                    h('div', { class: 'wperp-form-group' }, [
                        h('button', {
                            class: 'wperp-btn wperp-btn-primary',
                            attrs: { type: 'submit', disabled: true }
                        }, 'Add Pay Item'),
                        h('div', { class: 'clearfix' })
                    ])
                ]),
                h('table', { class: 'erp-settings-table widefat' }, [
                    h('thead', [h('tr', [
                        h('th', 'Pay Type'), h('th', 'Pay Item'),
                        h('th', 'Amount Type'), h('th', 'Action')
                    ])]),
                    h('tbody', rows)
                ])
            ]);
        }
    };

    /* Register routes */
    window.settings.hooks.addFilter('erp_settings_admin_routes', 'erpProPreview', function(routes) {
        var hrRoute = routes.find(function(r) { return r.path === '/erp-hr'; });

        if (hrRoute && hrRoute.children) {
            hrRoute.children.push({
                path: 'payroll',
                component: { render: function(c) { return c('router-view'); } },
                children: [
                    { path: 'payment', name: 'HrPayment', component: PaymentPreview, alias: '/' },
                    { path: 'payitem', name: 'HrPayItem', component: PayItemPreview }
                ]
            });
        }

        return routes;
    });

    /* Inject PRO badge next to the "Payroll" sub-menu tab.
     * Observes document.body because #erp-settings may not be in
     * the DOM yet when this inline script executes. The observer
     * stays active so the badge survives Vue re-renders. */
    function injectPayrollBadge() {
        var items = document.querySelectorAll('.settings-sub-menu .menu-name');
        for (var i = 0; i < items.length; i++) {
            if (items[i].textContent.trim() === 'Payroll' && !items[i].parentNode.querySelector('.pro-label')) {
                var badge = document.createElement('span');
                badge.className = 'pro-label';
                badge.style.marginLeft = '10px';
                badge.textContent = 'PRO';
                items[i].parentNode.insertBefore(badge, items[i].nextSibling);
            }
        }
    }

    var observer = new MutationObserver(injectPayrollBadge);
    observer.observe(document.body, { childList: true, subtree: true });
})();
JS;

        wp_add_inline_script( 'erp-settings-bootstrap', $js, 'after' );
    }

    /**
     * Add "Payroll" section to the HR settings tab.
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_payroll_settings_section( $sections ) {
        $sections['payroll'] = __( 'Payroll', 'erp' );

        return $sections;
    }

    /**
     * Provide payroll settings field data (sub-sections structure) for the HR settings tab.
     * Mirrors the Pro payroll Settings class structure so the SPA renders sub-tabs.
     *
     * @param array $fields
     * @param array $sections
     *
     * @return array
     */
    public function add_payroll_settings_fields( $fields, $sections ) {
        $fields['payroll'] = [
            'sub_sections' => [
                'payment' => __( 'Payment Settings', 'erp' ),
                'payitem' => __( 'Pay Item Settings', 'erp' ),
            ],
            'payment' => [
                [
                    'title' => __( 'Payment Method Selection', 'erp' ),
                    'type'  => 'title',
                ],
                [
                    'title'   => __( 'Select a method', 'erp' ),
                    'id'      => 'erp_payroll_payment_method_settings',
                    'type'    => 'select',
                    'options' => [
                        'cash'   => __( 'Cash', 'erp' ),
                        'cheque' => __( 'Cheque', 'erp' ),
                        'bank'   => __( 'Bank', 'erp' ),
                    ],
                ],
                [
                    'title'   => __( 'Select a bank', 'erp' ),
                    'id'      => 'erp_payroll_payment_bank_settings',
                    'type'    => 'select',
                    'options' => [
                        '' => __( '— Select Bank —', 'erp' ),
                    ],
                ],
                [
                    'type' => 'sectionend',
                    'id'   => 'script_styling_options',
                ],
            ],
            'payitem' => [
                [
                    'title' => __( 'Pay Item Settings', 'erp' ),
                    'type'  => 'title',
                ],
                [
                    'title'   => __( 'Pay Type', 'erp' ),
                    'id'      => 'paytype',
                    'type'    => 'select',
                    'options' => [
                        'earning'   => __( 'Earning', 'erp' ),
                        'deduction' => __( 'Deduction', 'erp' ),
                    ],
                ],
                [
                    'title' => __( 'Pay Item', 'erp' ),
                    'id'    => 'payitem',
                    'type'  => 'text',
                ],
            ],
        ];

        return $fields;
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
            'title'       => __( 'Settings', 'erp' ),
            'capability'  => 'erp_hr_manager',
            'slug'        => 'settings',
            'position'    => 20,
            'direct_link' => admin_url( 'admin.php?page=erp-settings#/erp-hr/payroll' ),
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
