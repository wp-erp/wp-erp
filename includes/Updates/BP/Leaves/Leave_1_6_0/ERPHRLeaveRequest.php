<?php

namespace WeDevs\ERP\Updates\BP\Leaves\Leave_1_6_0;

use WP_Background_Process;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
}

/**
 * Migrate Leave Request Data to new database model
 *
 * For each leave request table entry, there will be an entry in new leave request table, leave approval status table,
 * leave entitlements table and leave request details table.
 *
 * @since 1.6.0
 */
class ERPHRLeaveRequest extends WP_Background_Process {

    /**
     * Background process name.
     *
     * @var string
     */
    protected $action = 'erp_hr_leaves_request_1_5_15';

    /**
     * Possible array elements
     *
     * @var array
     */
    protected $request_data = [
        'task'                     => 'leave_request',
        'id'                       => 0,
        'user_id'                  => 0,
        'policy_id'                => 0,
        'days'                     => 0,
        'start_date'               => 0,
        'end_date'                 => 0,
        'comments'                 => '',
        'reason'                   => '',
        'status'                   => 0,
        'created_by'               => '',
        'updated_by'               => '',
        'created_on'               => '',
        'updated_on'               => '',
        'leave_request_id'         => 0,
        'leave_id'                 => '',
        'f_year'                   => '',
        'leave_policy_id'          => 0,
        'leave_approval_status_id' => 0,
        'leave_entitlement_id'     => 0,
    ];

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param array $leave_request queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $leave_request ) {
        $this->request_data = wp_parse_args( $leave_request, $this->request_data );

        $ret = '';

        switch ( $this->request_data['task'] ) {
            case 'leave_request':
                $ret = $this->insert_leave_request();
                break;

            case 'leave_approval_status':
                $ret = $this->insert_leave_approval_status();
                break;

            case 'leave_entitlements':
                $ret = $this->insert_leave_entitlement();
                break;

            case 'leave_request_details':
                $ret = $this->insert_leave_request_details();
                break;

            default:
                $ret = false;
                break;
        }

        return $ret;
    }

    /**
     * This method will insert old leave request single row data to new leave request table.
     *
     * @since 1.6.0
     *
     * @return array will return updated data to further run current background process
     */
    protected function insert_leave_request() {
        global $wpdb;

        // get leave request data
        $leave_request_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_hr_leave_requests WHERE id = %d",
                [ $this->request_data['id'] ]
            ),
            ARRAY_A
        );

        if ( null === $leave_request_data ) {
            error_log(
                print_r(
                    [
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => 'No old leave request data.',
                    ],
                    true
                )
            );
			// no result found: can be because of query error, handle this problem here probably log this error.
            // todo: keep log here.
        } elseif ( is_array( $leave_request_data ) && ! empty( $leave_request_data ) ) {
            // store datas for further use.
            $this->request_data = wp_parse_args( $leave_request_data, $this->request_data );

            // get financial year
            $financial_year = erp_get_financial_year_dates( $this->request_data['start_date'] );
            $start_date     = erp_mysqldate_to_phptimestamp( $financial_year['start'], false );
            $end_date       = erp_mysqldate_to_phptimestamp( $financial_year['end'], false );

            // check f_year already exist for given date range
            $f_year_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}erp_hr_financial_years_new WHERE start_date >= %d AND end_date <= %d LIMIT 1",
                    [ $start_date->getTimestamp(), $end_date->getTimestamp() ]
                )
            );
            $this->request_data['f_year'] = $f_year_id;

            // fix dates.
            if ( isset( $this->request_data['start_date'] ) ) {
                $this->request_data['start_date'] = erp_mysqldate_to_phptimestamp( $this->request_data['start_date'] );
            }

            if ( isset( $this->request_data['end_date'] ) ) {
                $this->request_data['end_date'] = erp_mysqldate_to_phptimestamp( $this->request_data['end_date'] );
            }

            if ( isset( $this->request_data['created_on'] ) ) {
                $this->request_data['created_on'] = erp_mysqldate_to_phptimestamp( $this->request_data['created_on'] );
            }

            if ( isset( $this->request_data['updated_on'] ) ) {
                $this->request_data['updated_on'] = erp_mysqldate_to_phptimestamp( $this->request_data['updated_on'] );
            }

            // now get data from new leave policy table.
            $policy_data = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT leave_id FROM {$wpdb->prefix}erp_hr_leave_policies_new WHERE old_policy_id = %d AND f_year = %d",
                    [ $this->request_data['policy_id'], $f_year_id ]
                ),
                ARRAY_A
            );

            if ( null === $policy_data ) {
                // no result found: can be because of query error, handle this problem here probably log this error.
                error_log(
                    print_r(
                        [
                            'file'    => __FILE__,
                            'line'    => __LINE__,
                            'message' => '(Query error) No policies data found from new table. ' . $wpdb->last_error,
                        ],
                        true
                    )
                );
            } elseif ( is_array( $policy_data ) && ! empty( $policy_data ) ) {
                $this->request_data['leave_id'] = $policy_data['leave_id'];

                // get entitlement id for current request
                $entitlement_id = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements_new WHERE trn_type = %s AND user_id = %d AND leave_id = %d AND f_year = %d",
                        [
                            'leave_policies',
                            $this->request_data['user_id'],
                            $this->request_data['leave_id'],
                            $f_year_id,
                        ]
                    )
                );

                $this->request_data['leave_policy_id'] = $entitlement_id;

                // insert into new leave request table.
                $table_data = [
                    'user_id'              => $this->request_data['user_id'],
                    'leave_id'             => $this->request_data['leave_id'],
                    'leave_entitlement_id' => $entitlement_id,
                    'day_status_id'        => 1,
                    'days'                 => $this->request_data['days'],
                    'start_date'           => $this->request_data['start_date'],
                    'end_date'             => $this->request_data['end_date'],
                    'reason'               => wp_kses_post( $this->request_data['reason'] ),
                    'last_status'          => $this->request_data['status'],
                    'created_by'           => $this->request_data['created_by'],
                    'created_at'           => $this->request_data['created_on'],
                    'updated_at'           => $this->request_data['updated_on'],
                ];

                $table_format = [
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                ];

                if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_requests_new", $table_data, $table_format ) === false ) {
                    error_log(
                        print_r(
                            [
                                'file'    => __FILE__,
                                'line'    => __LINE__,
                                'message' => '(Query error) Insertion failed into new leave requests table: ' . $wpdb->last_error,
                            ],
                            true
                        )
                    );
                } else {
                    $this->request_data['leave_request_id'] = $wpdb->insert_id;
                }
            }
        }
        $this->request_data['task'] = 'leave_approval_status';

        return $this->request_data;
    }

    /**
     * This method will insert old leave request single row data to new leave approval status table.
     *
     * @since 1.6.0
     *
     * @return array will return updated data to further run current background process
     */
    protected function insert_leave_approval_status() {
        // insert only if leave is approved or rejected and request is already made.
        if ( isset( $this->request_data['status'] ) && in_array( $this->request_data['status'], [ 1, 3 ] ) && isset( $this->request_data['leave_request_id'] ) && $this->request_data['leave_request_id'] > 0 ) {
            // leave approved or rejected.
            global $wpdb;
            $table_data = [
                'leave_request_id'   => $this->request_data['leave_request_id'],
                'approval_status_id' => $this->request_data['status'],
                'approved_by'        => $this->request_data['updated_by'],
                'created_at'         => $this->request_data['updated_on'],
                'updated_at'         => $this->request_data['updated_on'],
            ];

            $table_format = [
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
            ];

            if ( isset( $this->request_data['comments'] ) && $this->request_data['comments'] != '' ) {
                $table_data['message'] = wp_kses_post( $this->request_data['comments'] );
                $table_format[]        = '%s';
            }

            if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_approval_status_new", $table_data, $table_format ) === false ) {
                error_log(
                    print_r(
                        [
                            'file'    => __FILE__,
                            'line'    => __LINE__,
                            'message' => '(Query error) Insertion failed into new leave approval status table: ' . $wpdb->last_error,
                        ],
                        true
                    )
                );
            } else {
                $this->request_data['leave_approval_status_id'] = $wpdb->insert_id;
            }
        }
        $this->request_data['task'] = 'leave_entitlements';

        return $this->request_data;
    }

    protected function insert_leave_entitlement() {
        if ( isset( $this->request_data['leave_approval_status_id'] ) && $this->request_data['leave_approval_status_id'] > 0 && isset( $this->request_data['status'] ) && $this->request_data['status'] == 1 ) {
            global $wpdb;

            $table_data = [
                'user_id'     => $this->request_data['user_id'],
                'leave_id'    => $this->request_data['leave_id'],
                'created_by'  => $this->request_data['updated_by'],
                'trn_id'      => $this->request_data['leave_approval_status_id'],
                'trn_type'    => 'leave_approval_status',
                'day_in'      => 0,
                'day_out'     => $this->request_data['days'],
                'description' => erp_hr_leave_request_get_statuses( $this->request_data['status'] ),
                'f_year'      => $this->request_data['f_year'],
                'created_at'  => $this->request_data['created_on'],
                'updated_at'  => $this->request_data['created_on'],
            ];

            $table_format = [
                '%d',
                '%d',
                '%d',
                '%d',
                '%s',
                '%d',
                '%d',
                '%s',
                '%d',
                '%d',
                '%d',
            ];

            if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_entitlements_new", $table_data, $table_format ) === false ) {
                error_log(
                    print_r(
                        [
                            'file'    => __FILE__,
                            'line'    => __LINE__,
                            'message' => '(Query error) Insertion failed into new leave entitlement table: ' . $wpdb->last_error,
                        ],
                        true
                    )
                );
				// todo: query error, do logging or something here.
            } else {
                $this->request_data['leave_entitlement_id'] = $wpdb->insert_id;

                // now get days data from new leave policy table.
                $policy_days = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT days FROM {$wpdb->prefix}erp_hr_leave_policies_new WHERE old_policy_id = %d AND f_year = %d",
                        [ $this->request_data['policy_id'], $this->request_data['f_year'] ]
                    )
                );

                $days_count = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT SUM(days) FROM {$wpdb->prefix}erp_hr_leave_requests_new WHERE leave_entitlement_id = %d and user_id = %d and id <= %d and last_status = 1",
                        [ $this->request_data['leave_policy_id'], $this->request_data['user_id'], $this->request_data['leave_request_id'] ]
                    )
                );

                if ( $days_count > $policy_days ) {
                    // calculate extra leaves
                    // already got extra leaves ?
                    $extra_days_count = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT SUM(days) FROM {$wpdb->prefix}erp_hr_leaves_unpaid_new WHERE leave_id = %d and user_id = %d and f_year = %d and leave_request_id < %d",
                            [ $this->request_data['leave_id'], $this->request_data['user_id'], $this->request_data['f_year'], $this->request_data['leave_request_id'] ]
                        )
                    );

                    $current_count = absint( $days_count ) - absint( $policy_days ) - absint( $extra_days_count );

                    // insert into new unpaid leave table.
                    $table_data = [
                        'leave_id'                 => $this->request_data['leave_id'],
                        'leave_request_id'         => $this->request_data['leave_request_id'],
                        'leave_approval_status_id' => $this->request_data['leave_approval_status_id'],
                        'user_id'                  => $this->request_data['user_id'],
                        'days'                     => $current_count,
                        'amount'                   => 0,
                        'total'                    => 0,
                        'f_year'                   => $this->request_data['f_year'],
                        'created_at'               => $this->request_data['created_on'],
                        'updated_at'               => $this->request_data['updated_on'],
                    ];

                    $table_format = [
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                    ];

                    if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leaves_unpaid_new", $table_data, $table_format ) === false ) {
                        error_log(
                            print_r(
                                [
                                    'file'    => __FILE__,
                                    'line'    => __LINE__,
                                    'message' => '(Query error) Insertion failed new unpaid leave table: ' . $wpdb->last_error,
                                ],
                                true
                            )
                        );
                    } else {
                        $table_data = [
                            'user_id'     => $this->request_data['user_id'],
                            'leave_id'    => $this->request_data['leave_id'],
                            'created_by'  => $this->request_data['updated_by'],
                            'trn_id'      => $this->request_data['leave_approval_status_id'],
                            'trn_type'    => 'unpaid_leave',
                            'day_in'      => $current_count,
                            'day_out'     => 0,
                            'description' => 'Accounts',
                            'f_year'      => $this->request_data['f_year'],
                            'created_at'  => $this->request_data['created_on'],
                            'updated_at'  => $this->request_data['created_on'],
                        ];

                        $table_format = [
                            '%d',
                            '%d',
                            '%d',
                            '%d',
                            '%s',
                            '%d',
                            '%d',
                            '%s',
                            '%d',
                            '%d',
                            '%d',
                        ];

                        if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_entitlements_new", $table_data, $table_format ) === false ) {
                            error_log(
                                print_r(
                                    [
                                        'file'    => __FILE__,
                                        'line'    => __LINE__,
                                        'message' => '(Query error) Insertion failed new leave entitlements table: ' . $wpdb->last_error,
                                    ],
                                    true
                                )
                            );
                        }
                    }
                }
            }
        }
        $this->request_data['task'] = 'leave_request_details';

        return $this->request_data;
    }

    /**
     * This method will insert old hr_leaves table data to new leave request details table. Can be multiple rows.
     *
     * @since 1.6.0
     *
     * @return array will return false on success that will prevent for this task to run further and remove this task from current queue
     */
    protected function insert_leave_request_details() {
        if ( isset( $this->request_data['leave_entitlement_id'] ) && $this->request_data['leave_entitlement_id'] > 0 ) {
            // get hr leaves: data coming from old db table.
            global $wpdb;
            $hr_leaves_data = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT `date` FROM {$wpdb->prefix}erp_hr_leaves WHERE request_id = %d",
                    [ $this->request_data['id'] ]
                )
            );

            if ( is_array( $hr_leaves_data ) && ! empty( $hr_leaves_data ) ) {
                $table_rows = [];

                foreach ( $hr_leaves_data as $leave_date ) {
                    $table_rows[] = [
                        'leave_request_id'         => $this->request_data['leave_request_id'],
                        'leave_approval_status_id' => $this->request_data['leave_approval_status_id'],
                        'workingday_status'        => 1,
                        'user_id'                  => $this->request_data['user_id'],
                        'f_year'                   => $this->request_data['f_year'],
                        'leave_date'               => erp_mysqldate_to_phptimestamp( $leave_date ),
                        'created_at'               => $this->request_data['created_on'],
                        'updated_at'               => $this->request_data['created_on'],
                    ];
                }

                if ( ! empty( $table_rows ) ) {
                    if ( erp_wp_insert_rows( $table_rows, "{$wpdb->prefix}erp_hr_leave_request_details_new" ) === false ) {
                        error_log(
                            print_r(
                                [
                                    'file'    => __FILE__,
                                    'line'    => __LINE__,
                                    'message' => '(Query error) Insertion failed new leave request details table: ' . $wpdb->last_error,
                                ],
                                true
                            )
                        );
                    }
                }
            }
        }
        $this->request_data['task'] = 'completed';

        // all import task in completed now we can safely return false from here.
        return $this->request_data;
    }

    /**
     * Complete
     */
    protected function complete() {
        parent::complete();

        if ( ! class_exists( '\WeDevs\ERP\HRM\Update\ERP_1_6_0' ) ) {
            require_once WPERP_INCLUDES . '/Updates/update-1.6.0.php';
        }

        // now delete all old db tables and data.
        $erp_update_1_6_0 = new \WeDevs\ERP\HRM\Update\ERP_1_6_0();

        if ( $erp_update_1_6_0->alter_old_db_tables() ) {
            $erp_update_1_6_0->alter_new_db_tables();
            delete_option( 'policy_migrate_data_1_6_0' );
        }
    }
}

global $bg_progess_hr_leave_requests;
$bg_progess_hr_leave_requests = new ERPHRLeaveRequest();
