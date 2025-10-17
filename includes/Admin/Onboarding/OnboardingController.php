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
        // Complete onboarding - saves all data atomically
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/complete', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'complete_onboarding' ],
                'permission_callback' => [ $this, 'check_permission' ],
            ],
        ] );

        // Get onboarding status and initial data
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

        // Debug: Log received data
        error_log( 'ERP Onboarding Data Received: ' . print_r( $data, true ) );

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

        // 3. Save Import Settings (Step 3)
        // CSV file upload is handled separately via existing import system

        // 4. Save Module & Workday Settings (Step 4)

        // Save leave years (financial years for leave management)
        if ( ! empty( $data['leaveYears'] ) && is_array( $data['leaveYears'] ) ) {
            $leave_years = [];
            foreach ( $data['leaveYears'] as $leave_year ) {
                if ( ! empty( $leave_year['fy_name'] ) && ! empty( $leave_year['start_date'] ) && ! empty( $leave_year['end_date'] ) ) {
                    $leave_years[] = [
                        'fy_name'     => sanitize_text_field( $leave_year['fy_name'] ),
                        'start_date'  => sanitize_text_field( $leave_year['start_date'] ),
                        'end_date'    => sanitize_text_field( $leave_year['end_date'] ),
                        'description' => 'Year for leave',
                    ];
                }
            }

            // Save all leave years using the same function as the old setup wizard
            if ( ! empty( $leave_years ) ) {
                $result = erp_settings_save_leave_years( $leave_years );
                if ( is_wp_error( $result ) ) {
                    error_log( 'ERP Onboarding: Failed to save leave years - ' . $result->get_error_message() );
                }
            }
        }

        // Save leave management preference
        if ( isset( $data['enableLeaveManagement'] ) ) {
            update_option( 'erp_enable_leave_management', (bool) $data['enableLeaveManagement'] );
        }

        // Save working days - use defaults if not provided
        $default_working_days = [
            'mon' => '8',
            'tue' => '8',
            'wed' => '8',
            'thu' => '8',
            'fri' => '8',
            'sat' => '0',
            'sun' => '0',
        ];

        $working_days_to_save = ! empty( $data['workingDays'] ) && is_array( $data['workingDays'] )
            ? $data['workingDays']
            : $default_working_days;

        // Save to individual options (what HR Settings reads from)
        foreach ( $working_days_to_save as $day => $hours ) {
            update_option( sanitize_text_field( $day ), sanitize_text_field( $hours ) );
        }

        // Also save to HR workdays option for consistency
        update_option( 'erp_settings_erp-hr_workdays', $working_days_to_save );

        // Save working hours
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
     * Get onboarding status and initial data from database
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_status( $request ) {
        $completed = get_option( 'erp_onboarding_completed', false );
        $company   = new \WeDevs\ERP\Company();
        $general_settings = get_option( 'erp_settings_general', [] );

        // Convert financial month number back to month name
        $month_map = [
            '1' => 'january', '2' => 'february', '3' => 'march', '4' => 'april',
            '5' => 'may', '6' => 'june', '7' => 'july', '8' => 'august',
            '9' => 'september', '10' => 'october', '11' => 'november', '12' => 'december',
        ];
        $financial_month = $general_settings['gen_financial_month'] ?? '1';
        $financial_month_name = $month_map[ $financial_month ] ?? 'january';

        // Get existing departments and designations
        $departments = erp_hr_get_departments( [ 'number' => -1 ] );
        $department_names = array_map( function( $dept ) {
            return $dept->title;
        }, $departments );

        $designations = erp_hr_get_designations( [ 'number' => -1 ] );
        $designation_names = array_map( function( $desig ) {
            return $desig->title;
        }, $designations );

        // Get existing leave years
        $leave_years = [];
        $financial_years = erp_get_hr_financial_years();
        if ( ! empty( $financial_years ) ) {
            foreach ( $financial_years as $fy ) {
                $leave_years[] = [
                    'id'         => $fy['id'],
                    'fy_name'    => $fy['fy_name'],
                    'start_date' => $fy['start_date'],
                    'end_date'   => $fy['end_date'],
                ];
            }
        }

        // Get working days
        $workingDays = [];
        $days = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
        foreach ( $days as $day ) {
            $day_key = 'erp_' . $day;
            $workingDays[ $day ] = $general_settings[ $day_key ] ?? '8';
        }

        // Get working hours
        $working_hours = get_option( 'erp_working_hours', [
            'start' => '09:00',
            'end'   => '17:00',
        ] );

        $data = [
            'completed' => (bool) $completed,
            // Step 1 - Basic
            'companyName'          => $company->name ?? '',
            'companyStartDate'     => $general_settings['gen_com_start'] ?? '',
            'financialYearStarts'  => $financial_month_name,
            // Step 2 - Organization
            'departments'          => $department_names,
            'designations'         => $designation_names,
            // Step 3 - Import (handled separately)
            // Step 4 - Module & Workday
            'leaveYears'           => $leave_years,
            'enableLeaveManagement' => (bool) get_option( 'erp_enable_leave_management', true ),
            'workingDays'          => $workingDays,
            'workingHours'         => $working_hours,
        ];

        if ( $completed ) {
            $data['completed_at'] = get_option( 'erp_onboarding_completed_at' );
        }

        return rest_ensure_response( $data );
    }
}
