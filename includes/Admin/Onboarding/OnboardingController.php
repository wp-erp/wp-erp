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

        // Save step data - saves data after each step
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/save-step', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_step' ],
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

        // Get predefined leave types list
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/leave-types', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_leave_types' ],
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
     * Complete onboarding process - Create leave policies and mark complete
     *
     * Note: All data is already saved via save_step() on each step.
     * This method only handles leave policy creation if enabled.
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function complete_onboarding( $request ) {
        $data = $request->get_json_params();

        if ( empty( $data ) ) {
            $data = [];
        }

        // Create leave policies for all selected types (if any selected and no policies exist yet)
        $selected_types = get_option( 'erp_onboarding_selected_leave_types', [] );

        if ( ! empty( $selected_types ) && ! empty( $data['leaveYears'] ) ) {
            global $wpdb;
            $policies_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_leave_policies" );

            if ( (int) $policies_count === 0 ) {
                $this->create_default_leave_policies( $data['leaveYears'], $selected_types );
            }
        }

        return rest_ensure_response( [
            'success'      => true,
            'message'      => __( 'Onboarding completed successfully!', 'erp' ),
            'redirect_url' => admin_url( 'admin.php?page=erp' ),
        ] );
    }

    /**
     * Save step data - called after each step (except import)
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function save_step( $request ) {
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

        if ( isset( $data['financialYearStarts'] ) || isset( $data['companyStartDate'] ) ) {
            $month_map = [
                'january' => '1', 'february' => '2', 'march' => '3', 'april' => '4',
                'may' => '5', 'june' => '6', 'july' => '7', 'august' => '8',
                'september' => '9', 'october' => '10', 'november' => '11', 'december' => '12',
            ];

            $existing_settings = get_option( 'erp_settings_general', [] );
            $updated_settings = $existing_settings;

            if ( isset( $data['financialYearStarts'] ) ) {
                $financial_month = isset( $month_map[ strtolower( $data['financialYearStarts'] ) ] )
                    ? $month_map[ strtolower( $data['financialYearStarts'] ) ]
                    : '1';
                $updated_settings['gen_financial_month'] = $financial_month;
            }

            if ( isset( $data['companyStartDate'] ) ) {
                $updated_settings['gen_com_start'] = sanitize_text_field( $data['companyStartDate'] );
            }

            update_option( 'erp_settings_general', $updated_settings );
        }

        // 2. Save Organization (Departments & Designations) (Step 2)
        if ( isset( $data['departments'] ) && is_array( $data['departments'] ) ) {
            // Get existing departments
            $existing_departments = erp_hr_get_departments( [ 'number' => -1 ] );
            $existing_dept_names = array_map( function( $dept ) {
                return strtolower( trim( $dept->title ) );
            }, $existing_departments );

            // Only add departments that don't exist
            foreach ( $data['departments'] as $department ) {
                if ( ! empty( $department ) ) {
                    $dept_name_lower = strtolower( trim( sanitize_text_field( $department ) ) );

                    // Check if department already exists (case-insensitive)
                    if ( ! in_array( $dept_name_lower, $existing_dept_names ) ) {
                        erp_hr_create_department( [
                            'title' => sanitize_text_field( $department ),
                        ] );
                        // Add to existing list to prevent duplicates within same save
                        $existing_dept_names[] = $dept_name_lower;
                    }
                }
            }
        }

        if ( isset( $data['designations'] ) && is_array( $data['designations'] ) ) {
            // Get existing designations
            $existing_designations = erp_hr_get_designations( [ 'number' => -1 ] );
            $existing_desig_names = array_map( function( $desig ) {
                return strtolower( trim( $desig->title ) );
            }, $existing_designations );

            // Only add designations that don't exist
            foreach ( $data['designations'] as $designation ) {
                if ( ! empty( $designation ) ) {
                    $desig_name_lower = strtolower( trim( sanitize_text_field( $designation ) ) );

                    // Check if designation already exists (case-insensitive)
                    if ( ! in_array( $desig_name_lower, $existing_desig_names ) ) {
                        erp_hr_create_designation( [
                            'title' => sanitize_text_field( $designation ),
                        ] );
                        // Add to existing list to prevent duplicates within same save
                        $existing_desig_names[] = $desig_name_lower;
                    }
                }
            }
        }

        // 3. Skip Import (Step 3) - handled separately

        // 4. Save Module & Workday Settings (Step 4)
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

            if ( ! empty( $leave_years ) ) {
                erp_settings_save_leave_years( $leave_years );
            }
        }

        if ( isset( $data['enableLeaveManagement'] ) ) {
            update_option( 'erp_enable_leave_management', (bool) $data['enableLeaveManagement'] );
        }

        if ( isset( $data['selectedLeaveTypes'] ) && is_array( $data['selectedLeaveTypes'] ) ) {
            update_option( 'erp_onboarding_selected_leave_types', array_map( 'sanitize_text_field', $data['selectedLeaveTypes'] ) );
        }

        if ( ! empty( $data['workingDays'] ) && is_array( $data['workingDays'] ) ) {
            foreach ( $data['workingDays'] as $day => $hours ) {
                update_option( sanitize_text_field( $day ), sanitize_text_field( $hours ) );
            }
            update_option( 'erp_settings_erp-hr_workdays', $data['workingDays'] );
        }

        if ( ! empty( $data['workingHours'] ) && is_array( $data['workingHours'] ) ) {
            update_option( 'erp_working_hours', [
                'start' => sanitize_text_field( $data['workingHours']['start'] ?? '09:00' ),
                'end'   => sanitize_text_field( $data['workingHours']['end'] ?? '17:00' ),
            ] );
        }

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Step data saved successfully!', 'erp' ),
        ] );
    }

    /**
     * Get predefined leave types available for selection
     *
     * @return WP_REST_Response
     */
    public function get_leave_types( $request ) {
        $predefined = $this->get_predefined_leave_types();

        global $wpdb;
        $existing_leaves = $wpdb->get_results(
            "SELECT id, name FROM {$wpdb->prefix}erp_hr_leaves ORDER BY name ASC"
        );

        // Build a name→db_id map from existing DB leaves
        $db_name_to_id = [];
        foreach ( $existing_leaves as $leave ) {
            $db_name_to_id[ strtolower( trim( $leave->name ) ) ] = (int) $leave->id;
        }

        // Enrich predefined types with db_id if they already exist in DB
        $predefined_lower_names = [];
        foreach ( $predefined as &$type ) {
            $lower = strtolower( $type['name'] );
            $predefined_lower_names[] = $lower;
            if ( isset( $db_name_to_id[ $lower ] ) ) {
                $type['db_id'] = $db_name_to_id[ $lower ];
            }
        }
        unset( $type );

        // Append custom DB leaves not in predefined list
        foreach ( $existing_leaves as $leave ) {
            if ( ! in_array( strtolower( $leave->name ), $predefined_lower_names, true ) ) {
                $predefined[] = [
                    'id'    => 'custom_' . $leave->id,
                    'name'  => $leave->name,
                    'db_id' => (int) $leave->id,
                ];
            }
        }

        // Strip color/days/description from API response — not needed by the frontend
        $response = array_map( function( $type ) {
            return [
                'id'    => $type['id'],
                'name'  => $type['name'],
                'db_id' => $type['db_id'] ?? null,
            ];
        }, $predefined );

        return rest_ensure_response( $response );
    }

    /**
     * Returns the list of predefined leave types — matches the plugin's CLI seeder (DataProvider::leave_types).
     *
     * @return array
     */
    private function get_predefined_leave_types() {
        return [
            [ 'id' => 'annual_leave',    'name' => 'Annual Leave',    'days' => 20, 'color' => '#4CAF50', 'description' => 'Paid vacation days for personal time off' ],
            [ 'id' => 'sick_leave',      'name' => 'Sick Leave',      'days' => 14, 'color' => '#F44336', 'description' => 'Leave for illness or medical appointments' ],
            [ 'id' => 'casual_leave',    'name' => 'Casual Leave',    'days' => 10, 'color' => '#2196F3', 'description' => 'Short-notice leave for personal matters' ],
            [ 'id' => 'maternity_leave', 'name' => 'Maternity Leave', 'days' => 90, 'color' => '#E91E63', 'description' => 'Leave for expecting mothers' ],
            [ 'id' => 'paternity_leave', 'name' => 'Paternity Leave', 'days' => 10, 'color' => '#9C27B0', 'description' => 'Leave for new fathers' ],
        ];
    }

    /**
     * Get onboarding status and initial data from database
     *
     * @param \WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_status( $request ) {
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
                    // Convert timestamp to Y-m-d format for date inputs
                    'start_date' => is_numeric( $fy['start_date'] ) ? date( 'Y-m-d', $fy['start_date'] ) : $fy['start_date'],
                    'end_date'   => is_numeric( $fy['end_date'] ) ? date( 'Y-m-d', $fy['end_date'] ) : $fy['end_date'],
                ];
            }
        }

        // Get working days - check individual options first, then fallback to workdays option
        $workingDays = [];
        $days = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
        $saved_workdays = get_option( 'erp_settings_erp-hr_workdays', [] );

        foreach ( $days as $day ) {
            // Try individual option first (saved by onboarding)
            $day_value = get_option( $day, null );

            if ( $day_value !== null ) {
                $workingDays[ $day ] = $day_value;
            } elseif ( isset( $saved_workdays[ $day ] ) ) {
                // Fallback to workdays option
                $workingDays[ $day ] = $saved_workdays[ $day ];
            } else {
                // Default: Mon-Fri = 8hrs, Sat-Sun = 0hrs
                $workingDays[ $day ] = in_array( $day, [ 'sat', 'sun' ] ) ? '0' : '8';
            }
        }

        // Get working hours
        $working_hours = get_option( 'erp_working_hours', [
            'start' => '09:00',
            'end'   => '17:00',
        ] );

        // Check if leave policies already exist
        global $wpdb;
        $policies_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_leave_policies" );
        $has_leave_policies = (int) $policies_count > 0;

        // Resolve selected leave types:
        // Priority 1: saved onboarding selection (user explicitly chose these)
        // Priority 2: if policies exist but no saved selection, derive from existing DB leave names
        // Priority 3: fresh user with no policies — empty (frontend defaults to all selected)
        $selected_leave_types = get_option( 'erp_onboarding_selected_leave_types', null );

        if ( $selected_leave_types === null ) {
            if ( $has_leave_policies ) {
                // Returning user who completed onboarding before this feature existed.
                // Match existing leave policy names/ids against predefined list + custom leaves.
                $predefined = $this->get_predefined_leave_types();
                $predefined_name_map = [];
                foreach ( $predefined as $type ) {
                    $predefined_name_map[ strtolower( $type['name'] ) ] = $type['id'];
                }

                $existing_leaves_with_policies = $wpdb->get_results(
                    "SELECT DISTINCT l.id, l.name FROM {$wpdb->prefix}erp_hr_leave_policies p
                     JOIN {$wpdb->prefix}erp_hr_leaves l ON p.leave_id = l.id"
                );

                $selected_leave_types = [];
                foreach ( $existing_leaves_with_policies as $leave ) {
                    $key = strtolower( trim( $leave->name ) );
                    if ( isset( $predefined_name_map[ $key ] ) ) {
                        // Predefined leave — use slug ID
                        $selected_leave_types[] = $predefined_name_map[ $key ];
                    } else {
                        // Custom DB leave — use custom_{db_id} format
                        $selected_leave_types[] = 'custom_' . $leave->id;
                    }
                }
            } else {
                // Fresh user — empty array signals frontend to default-select all
                $selected_leave_types = [];
            }
        }

        $data = [
            // Step 1 - Basic
            'companyName'          => $company->name ?? '',
            'companyStartDate'     => $general_settings['gen_com_start'] ?? '',
            'financialYearStarts'  => $financial_month_name,
            // Step 2 - Organization
            'departments'          => $department_names,
            'designations'         => $designation_names,
            // Step 3 - Leave Setup
            'leaveYears'           => $leave_years,
            'enableLeaveManagement' => $has_leave_policies ? false : (bool) get_option( 'erp_enable_leave_management', true ),
            'hasLeavePolicies'     => $has_leave_policies,
            'selectedLeaveTypes'   => $selected_leave_types,
            // Step 4 - Workday
            'workingDays'          => $workingDays,
            'workingHours'         => $working_hours,
        ];

        return rest_ensure_response( $data );
    }

    /**
     * Create default leave types and policies
     *
     * @param array $leave_years Financial years array
     * @return void
     */
    private function create_default_leave_policies( $leave_years, $selected_type_ids = [] ) {
        $first_fy = $leave_years[0] ?? null;

        if ( empty( $first_fy['fy_name'] ) || empty( $first_fy['start_date'] ) || empty( $first_fy['end_date'] ) ) {
            return;
        }

        $financial_years = erp_get_hr_financial_years();
        $first_year_id = null;

        foreach ( $financial_years as $fy ) {
            if ( $fy['fy_name'] === $first_fy['fy_name'] ) {
                $first_year_id = $fy['id'];
                break;
            }
        }

        if ( ! $first_year_id ) {
            return;
        }

        global $wpdb;
        $all_predefined = $this->get_predefined_leave_types();

        // Separate selected IDs into predefined slugs and custom DB IDs
        $selected_predefined_ids = [];
        $selected_custom_db_ids  = [];
        foreach ( $selected_type_ids as $sid ) {
            if ( strpos( $sid, 'custom_' ) === 0 ) {
                $selected_custom_db_ids[] = (int) substr( $sid, 7 );
            } else {
                $selected_predefined_ids[] = $sid;
            }
        }

        // Determine which predefined types to create
        if ( ! empty( $selected_type_ids ) ) {
            $default_leave_types = array_filter( $all_predefined, function( $type ) use ( $selected_predefined_ids ) {
                return in_array( $type['id'], $selected_predefined_ids, true );
            } );
        } else {
            $default_leave_types = $all_predefined;
        }

        // Fetch existing policy leave_ids so we don't duplicate
        $existing_policy_leave_ids = $wpdb->get_col(
            "SELECT DISTINCT leave_id FROM {$wpdb->prefix}erp_hr_leave_policies WHERE f_year = {$first_year_id}"
        );
        $existing_policy_leave_ids = array_map( 'intval', $existing_policy_leave_ids );

        foreach ( $default_leave_types as $leave_type_data ) {
            $leave_type_id = erp_hr_insert_leave_policy_name( [
                'name'        => $leave_type_data['name'],
                'description' => $leave_type_data['description'],
            ] );

            if ( is_wp_error( $leave_type_id ) ) {
                $existing_leave = \WeDevs\ERP\HRM\Models\Leave::where( 'name', $leave_type_data['name'] )->first();
                if ( $existing_leave ) {
                    $leave_type_id = $existing_leave->id;
                } else {
                    continue;
                }
            }

            // Skip if policy already exists for this leave type + year
            if ( in_array( (int) $leave_type_id, $existing_policy_leave_ids, true ) ) {
                continue;
            }

            $policy_data = [
                'leave_id'            => $leave_type_id,
                'employee_type'       => -1,
                'department_id'       => 0,
                'designation_id'      => 0,
                'location_id'         => 0,
                'gender'              => '',
                'marital'             => '',
                'f_year'              => $first_year_id,
                'description'         => $leave_type_data['description'],
                'days'                => $leave_type_data['days'],
                'color'               => $leave_type_data['color'],
                'applicable_from'     => 0,
                'apply_for_new_users' => 1,
            ];

            $policy_id = erp_hr_leave_insert_policy( $policy_data );

            if ( is_wp_error( $policy_id ) ) {
                error_log( 'ERP Onboarding: Failed to create leave policy for ' . $leave_type_data['name'] . ' - ' . $policy_id->get_error_message() );
            }
        }

        // Also create policies for custom DB leaves that were selected and don't have a policy yet
        foreach ( $selected_custom_db_ids as $custom_db_id ) {
            if ( in_array( $custom_db_id, $existing_policy_leave_ids, true ) ) {
                continue;
            }
            $custom_leave = \WeDevs\ERP\HRM\Models\Leave::find( $custom_db_id );
            if ( ! $custom_leave ) {
                continue;
            }
            $policy_data = [
                'leave_id'            => $custom_db_id,
                'employee_type'       => -1,
                'department_id'       => 0,
                'designation_id'      => 0,
                'location_id'         => 0,
                'gender'              => '',
                'marital'             => '',
                'f_year'              => $first_year_id,
                'description'         => '',
                'days'                => 0,
                'color'               => '#6B7280',
                'applicable_from'     => 0,
                'apply_for_new_users' => 1,
            ];
            $policy_id = erp_hr_leave_insert_policy( $policy_data );
            if ( is_wp_error( $policy_id ) ) {
                error_log( 'ERP Onboarding: Failed to create leave policy for custom leave ID ' . $custom_db_id . ' - ' . $policy_id->get_error_message() );
            }
        }
    }
}
