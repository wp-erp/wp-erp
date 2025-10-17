<?php

namespace WeDevs\ERP\Admin\Onboarding;

use WeDevs\ERP\API\REST_Controller;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Onboarding REST API Controller
 */
class OnboardingController extends REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'onboarding';

    /**
     * Register the routes for onboarding.
     */
    public function register_routes() {
        // Basic info step
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/basic', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_basic_info' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => [
                    'companyName' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'companyStartDate' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'financialYearStarts' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ] );

        // Organization info step
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/organization', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_organization_info' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => [
                    'address' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                    'city' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'state' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'zipCode' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'country' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'currency' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'fiscalYear' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'dateFormat' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ] );

        // Import data step
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/import', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_import_info' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => [
                    'importType' => [
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'hasExistingData' => [
                        'type'              => 'boolean',
                    ],
                ],
            ],
        ] );

        // Module selection step
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/modules', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_module_selection' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => [
                    'modules' => [
                        'required'          => true,
                        'type'              => 'array',
                        'items'             => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ] );

        // Complete onboarding
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/complete', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'complete_onboarding' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );

        // Get onboarding status
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/status', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_status' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );
    }

    /**
     * Check if user has permission to access onboarding
     *
     * @return bool
     */
    public function check_permission() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Save basic information
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function save_basic_info( $request ) {
        $company_name         = $request->get_param( 'companyName' );
        $company_start_date   = $request->get_param( 'companyStartDate' );
        $financial_year_starts = $request->get_param( 'financialYearStarts' );

        // Convert month name to number (1-12)
        $month_map = [
            'january' => '1', 'february' => '2', 'march' => '3', 'april' => '4',
            'may' => '5', 'june' => '6', 'july' => '7', 'august' => '8',
            'september' => '9', 'october' => '10', 'november' => '11', 'december' => '12',
        ];
        $financial_month = isset( $month_map[ strtolower( $financial_year_starts ) ] ) 
            ? $month_map[ strtolower( $financial_year_starts ) ] 
            : '1';

        // Update Company object (same as old setup wizard)
        $company = new \WeDevs\ERP\Company();
        $company->update( [
            'name' => $company_name,
        ] );

        // Update general settings (same as old setup wizard)
        $general_settings = get_option( 'erp_settings_general', [] );
        $general_settings['gen_financial_month'] = $financial_month;
        $general_settings['gen_com_start']       = $company_start_date;
        update_option( 'erp_settings_general', $general_settings );

        // Store onboarding data
        update_option( 'erp_onboarding_basic', [
            'company_name'          => $company_name,
            'company_start_date'    => $company_start_date,
            'financial_year_starts' => $financial_year_starts,
        ] );

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Basic information saved successfully', 'erp' ),
            'data'    => [
                'company_name'          => $company_name,
                'company_start_date'    => $company_start_date,
                'financial_year_starts' => $financial_year_starts,
            ],
        ] );
    }

    /**
     * Save organization information
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function save_organization_info( $request ) {
        $data = [
            'address'     => $request->get_param( 'address' ),
            'city'        => $request->get_param( 'city' ),
            'state'       => $request->get_param( 'state' ),
            'zip_code'    => $request->get_param( 'zipCode' ),
            'country'     => $request->get_param( 'country' ),
            'currency'    => $request->get_param( 'currency' ),
            'fiscal_year' => $request->get_param( 'fiscalYear' ),
            'date_format' => $request->get_param( 'dateFormat' ),
        ];

        update_option( 'erp_onboarding_organization', $data );

        // Update ERP settings
        $company_settings = get_option( 'erp_settings_general', [] );
        $company_settings['company_address_1'] = $data['address'];
        $company_settings['company_location']  = $data['city'];
        $company_settings['company_country']   = $data['country'];
        update_option( 'erp_settings_general', $company_settings );

        // Update currency and date format
        $erp_settings = get_option( 'erp_settings_erp', [] );
        $erp_settings['currency']    = $data['currency'];
        $erp_settings['date_format'] = $data['date_format'];
        update_option( 'erp_settings_erp', $erp_settings );

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Organization information saved successfully', 'erp' ),
            'data'    => $data,
        ] );
    }

    /**
     * Save import information
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function save_import_info( $request ) {
        $data = [
            'import_type'       => $request->get_param( 'importType' ),
            'has_existing_data' => $request->get_param( 'hasExistingData' ),
        ];

        update_option( 'erp_onboarding_import', $data );

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Import preferences saved successfully', 'erp' ),
            'data'    => $data,
        ] );
    }

    /**
     * Save module selection
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function save_module_selection( $request ) {
        $modules = $request->get_param( 'modules' );

        if ( ! is_array( $modules ) ) {
            return new WP_Error(
                'invalid_modules',
                __( 'Modules must be an array', 'erp' ),
                [ 'status' => 400 ]
            );
        }

        update_option( 'erp_onboarding_modules', $modules );

        // Activate selected modules
        $active_modules = get_option( 'erp_modules', [] );

        foreach ( $modules as $module ) {
            $module_key = strtolower( $module );
            if ( in_array( $module_key, [ 'hrm', 'crm', 'accounting' ] ) ) {
                $active_modules[ $module_key ] = 1;
            }
        }

        update_option( 'erp_modules', $active_modules );

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Modules activated successfully', 'erp' ),
            'data'    => [
                'selected_modules' => $modules,
                'active_modules'   => $active_modules,
            ],
        ] );
    }

    /**
     * Complete onboarding process - Save all data atomically
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function complete_onboarding( $request ) {
        // Get all form data
        $data = $request->get_json_params();
        
        if ( empty( $data ) ) {
            $data = [];
        }

        // 1. Save Basic Settings (Step 1)
        if ( ! empty( $data['companyName'] ) ) {
            $company = new \WeDevs\ERP\Company();
            $company->update( [
                'name' => sanitize_text_field( $data['companyName'] ),
            ] );
        }

        // Convert month name to number (1-12) to match existing system format
        $month_map = [
            'january' => '1', 'february' => '2', 'march' => '3', 'april' => '4',
            'may' => '5', 'june' => '6', 'july' => '7', 'august' => '8',
            'september' => '9', 'october' => '10', 'november' => '11', 'december' => '12',
        ];
        $financial_month = isset( $data['financialYearStarts'] )
            ? ( isset( $month_map[ strtolower( $data['financialYearStarts'] ) ] )
                ? $month_map[ strtolower( $data['financialYearStarts'] ) ]
                : '1' )
            : '1';

        // Get existing settings to preserve unrelated fields
        $existing_settings = get_option( 'erp_settings_general', [] );

        $updated_settings = array_merge( $existing_settings, [
            'gen_financial_month' => $financial_month,
            'gen_com_start'       => isset( $data['companyStartDate'] ) ? sanitize_text_field( $data['companyStartDate'] ) : '',
        ] );

        update_option( 'erp_settings_general', $updated_settings );

        // 2. Save Organization (Departments & Designations) (Step 2)
        if ( ! empty( $data['departments'] ) && is_array( $data['departments'] ) ) {
            foreach ( $data['departments'] as $department ) {
                if ( ! empty( $department ) ) {
                    erp_hr_create_department( [
                        'title' => sanitize_text_field( $department ),
                    ] );
                }
            }
        }

        if ( ! empty( $data['designations'] ) && is_array( $data['designations'] ) ) {
            foreach ( $data['designations'] as $designation ) {
                if ( ! empty( $designation ) ) {
                    erp_hr_create_designation( [
                        'title' => sanitize_text_field( $designation ),
                    ] );
                }
            }
        }

        // 3. Save Import Settings (Step 3) - Handle later if needed
        // CSV file upload would be handled separately if present

        // 4. Save Module & Workday Settings (Step 4)
        if ( isset( $data['enableLeaveManagement'] ) ) {
            // Save leave management preference
            update_option( 'erp_enable_leave_management', (bool) $data['enableLeaveManagement'] );
        }

        if ( ! empty( $data['workingDays'] ) && is_array( $data['workingDays'] ) ) {
            foreach ( $data['workingDays'] as $day => $status ) {
                $day_key = strtolower( sanitize_text_field( $day ) );
                // 8 = full day, 0 = non-working (status values: "full" or "non-working")
                $value = ( $status === 'full' ) ? '8' : '0';
                update_option( $day_key, $value );
            }
        }

        if ( ! empty( $data['workingHours'] ) && is_array( $data['workingHours'] ) ) {
            update_option( 'erp_working_hours', [
                'start' => sanitize_text_field( $data['workingHours']['start'] ?? '09:00' ),
                'end'   => sanitize_text_field( $data['workingHours']['end'] ?? '17:00' ),
            ] );
        }

        // Mark onboarding as complete
        update_option( 'erp_onboarding_completed', true );
        update_option( 'erp_onboarding_completed_at', current_time( 'mysql' ) );
        update_option( 'erp_setup_wizard_ran', '1' );

        // Remove the onboarding redirect flag
        delete_option( 'erp_activation_redirect' );

        return rest_ensure_response( [
            'success'      => true,
            'message'      => __( 'Onboarding completed successfully!', 'erp' ),
            'redirect_url' => admin_url( 'admin.php?page=erp' ),
        ] );
    }

    /**
     * Get onboarding status
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_status( $request ) {
        $completed = get_option( 'erp_onboarding_completed', false );

        $data = [
            'completed'    => (bool) $completed,
            'basic'        => get_option( 'erp_onboarding_basic', [] ),
            'organization' => get_option( 'erp_onboarding_organization', [] ),
            'import'       => get_option( 'erp_onboarding_import', [] ),
            'modules'      => get_option( 'erp_onboarding_modules', [] ),
        ];

        if ( $completed ) {
            $data['completed_at'] = get_option( 'erp_onboarding_completed_at' );
        }

        return rest_ensure_response( $data );
    }
}
