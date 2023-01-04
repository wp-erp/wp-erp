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
 * Migrate Leave Entitlement Data to new leave entitlement table
 *
 * @since 1.6.0
 */
class ERPHRLeaveEntitlements extends WP_Background_Process {

    /**
     * Background process id, must be unique.
     *
     * @var string
     */
    protected $action = 'erp_hr_leaves_entitlements_1_6_0';

    protected $request_data = [
        'task'          => 'task_required_data',
        'id'            => 0,
        'user_id'       => 0,
        'policy_id'     => 0,
        'days'          => 0,
        'from_date'     => '',
        'to_date'       => '',
        'comments'      => '',
        'status'        => 0,
        'created_by'    => 0,
        'created_on'    => '',
        'policy_data'   => [
            'id'             => 0,
            'name'           => '',
            'value'          => 0,
            'color'          => '',
            'department'     => 0,
            'designation'    => 0,
            'gender'         => '',
            'marital'        => '',
            'description'    => '',
            'location'       => 0,
            'effective_date' => '',
            'activate'       => 0,
            'execute_day'    => 0,
            'created_at'     => '',
            'updated_at'     => '',
        ],
        'f_year'        => 0,
        'new_policy_id' => 0,
        'leave_id'      => 0,
    ];

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param array $leave_entitlement Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $leave_entitlement ) {
        $this->request_data = wp_parse_args( $leave_entitlement, $this->request_data );

        $ret = '';

        switch ( $this->request_data['task'] ) {

            case 'task_required_data':
                $ret = $this->task_required_data();
                break;

            case 'task_leave_policy':
                $ret = $this->task_leave_policy();
                break;

            case 'task_leave_entitlements':
                $ret = $this->task_create_entitlement();
                break;

            default:
                $ret = false;
                break;

        }

        return $ret;
    }

    /**
     * This method will get entitlement data and policy table data from old db tables
     *
     * @return array|bool
     */
    protected function task_required_data() {
        // get leave_entitlement_data.
        global $wpdb;

        $entitlement_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE id = %d",
                [ $this->request_data['id'] ]
            ),
            ARRAY_A
        );

        if ( null === $entitlement_data ) {
            // no result found: can be because of query error, handle this problem here probably log this error.
            error_log(
                print_r(
                    [
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => '(Query error) No data found from leave entitlements table. ' . $wpdb->last_error,
                    ],
                    true
                )
            );

            return false; // exit from current queue.
        } elseif ( is_array( $entitlement_data ) && ! empty( $entitlement_data ) ) {
            // store datas for further use.
            $this->request_data = wp_parse_args( $entitlement_data, $this->request_data );
        }

        // get policy data
        $policy_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_hr_leave_policies WHERE id = %d",
                [ $this->request_data['policy_id'] ]
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
                        'message' => '(Query error) No data found from policy table. ' . $wpdb->last_error,
                    ],
                    true
                )
            );

            return false; // exit from current queue.
        } elseif ( is_array( $policy_data ) && ! empty( $policy_data ) ) {
            // store datas for further use.
            $this->request_data['policy_data'] = wp_parse_args( $policy_data, $this->request_data['policy_data'] );
        }

        $this->request_data['task'] = 'task_leave_policy';

        return $this->request_data;
    }

    /**
     * This method will create financial table data, leave names table data and finally leave policy table data based on entitlement data.
     *
     * @return array|bool
     */
    protected function task_leave_policy() {
        global $wpdb;
        /*
         * check if policy exist based on financial year,
         * if no policies exist for that f_year, insert that policy
         * data will go both leave_name and policy table
         */

        $f_year_id = $this->create_financial_year( $this->request_data['from_date'], $this->request_data['created_by'], $this->request_data['created_on'] );

        if ( false === $f_year_id ) {
            return false; // exit the queue.
        }

        $this->request_data['f_year'] = $f_year_id;

        // get leave name or create one.

        $leave_id = $this->create_leave_name( $this->request_data['policy_data']['name'], $this->request_data['policy_data']['description'], $this->request_data['policy_data']['created_at'], $this->request_data['policy_data']['updated_at'] );

        if ( false === $leave_id ) {
            return false; // exit the queue.
        }

        // save this leave id for further processing.
        $this->request_data['leave_id'] = $leave_id;

        // check if policy already exists
        $new_policy_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_hr_leave_policies_new WHERE department_id = %d AND location_id = %d AND designation_id = %d AND gender = %s AND marital = %s AND f_year = %d AND leave_id = %d",
                [
                    $this->request_data['policy_data']['department'],
                    $this->request_data['policy_data']['location'],
                    $this->request_data['policy_data']['designation'],
                    $this->request_data['policy_data']['gender'],
                    $this->request_data['policy_data']['marital'],
                    $this->request_data['f_year'],
                    $this->request_data['leave_id'],
                ]
            )
        );

        if ( null === $new_policy_id ) {
            // insert this policy into new table.
            $new_policy_id = $this->create_leave_policy();
        }

        if ( false === $new_policy_id ) {
            // exit the current queue, we couldn't create leave policy data for this entitlement.
            return false;
        }

        $this->request_data['new_policy_id'] = $new_policy_id;
        $this->request_data['task']          = 'task_leave_entitlements';

        return $this->request_data;
    }

    /**
     * This method will get financial year id from db or will create one if doesn't exist
     *
     * @param string $date
     *
     * @return bool|int
     */
    protected function create_financial_year( $date, $created_by, $created_on ) {
        global $wpdb;
        // get financial year
        $financial_year = erp_get_financial_year_dates( $date );
        $start_date     = erp_mysqldate_to_phptimestamp( $financial_year['start'], false );
        $end_date       = erp_mysqldate_to_phptimestamp( $financial_year['end'], false );

        // check f_year already exist for given date range
        $f_year_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_hr_financial_years_new WHERE start_date >= %d AND end_date <= %d LIMIT 1",
                [ $start_date->getTimestamp(), $end_date->getTimestamp() ]
            )
        );

        if ( null === $f_year_id ) {
            // we've to create this financial year first.
            $f_year_name = $start_date->format( 'Y' ) === $end_date->format( 'Y' ) ? $start_date->format( 'Y' ) : $start_date->format( 'Y' ) . ' - ' . $end_date->format( 'Y' );
            $insert_data = [
                'fy_name'    => $f_year_name,
                'start_date' => $start_date->getTimestamp(),
                'end_date'   => $end_date->getTimestamp(),
                'created_by' => $created_by,
                'updated_by' => $created_by,
                'created_at' => erp_mysqldate_to_phptimestamp( $created_on ),
                'updated_at' => erp_mysqldate_to_phptimestamp( $created_on ),
            ];

            $insert_format = [
                '%s',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
            ];

            if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_financial_years_new", $insert_data, $insert_format ) ) {
                error_log(
                    print_r(
                        [
                            'file'    => __FILE__,
                            'line'    => __LINE__,
                            'message' => '(Query error) Insertion failed new financial_years table. ' . $wpdb->last_error,
                        ],
                        true
                    )
                );

                return false;
            }
            $f_year_id = $wpdb->insert_id;
        }

        return $f_year_id;
    }

    protected function create_leave_name( $name, $description, $created_at, $updated_at ) {
        global $wpdb;

        if ( $created_at != '' ) {
            $created_at = erp_mysqldate_to_phptimestamp( $created_at );
        } else {
            $created_at = erp_current_datetime()->getTimestamp();
        }

        if ( $updated_at != '' ) {
            $updated_at = erp_mysqldate_to_phptimestamp( $updated_at );
        } else {
            $updated_at = erp_current_datetime()->getTimestamp();
        }

        // check if leave name already exist on database.
        $leave_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}erp_hr_leaves_new WHERE name = %s",
                [ $name ]
            )
        );

        if ( null === $leave_id ) {
            // insert into erp_hr_leaves_new table.
            $table_data = [
                'name'       => $name,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ];

            $table_format = [
                '%s',
                '%d',
                '%d',
            ];

            if ( '' !== $description ) {
                $table_data['description'] = wp_kses_post( $description );
                $table_format[]            = '%s';
            }

            if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leaves_new", $table_data, $table_format ) ) {
                error_log(
                    print_r(
                        [
                            'file'    => __FILE__,
                            'line'    => __LINE__,
                            'message' => '(Query error) Insertion failed new leaves table. ' . $wpdb->last_error,
                        ],
                        true
                    )
                );

                return false;
            } else {
                $leave_id = $wpdb->insert_id;
            }
        }

        return $leave_id;
    }

    /**
     * This method will migrate old entitlements data to new table
     *
     * @return bool
     */
    protected function task_create_entitlement() {
        global $wpdb;

        if ( '' !== $this->request_data['created_on'] ) {
            $created_at = erp_mysqldate_to_phptimestamp( $this->request_data['created_on'] );
        } else {
            $created_at = erp_current_datetime()->getTimestamp();
        }

        // check if already entitled
        $already_entitled = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT count(id) FROM {$wpdb->prefix}erp_hr_leave_entitlements_new WHERE user_id = %d AND leave_id = %d AND f_year = %d AND trn_type = %s",
                [
                    $this->request_data['user_id'],
                    $this->request_data['leave_id'],
                    $this->request_data['f_year'],
                    'leave_policies',
                ]
            )
        );

        if ( $already_entitled ) {
            return false;
        }

        // insert into erp_hr_leaves_new table.

        $table_data = [
            'user_id'     => $this->request_data['user_id'],
            'created_by'  => $this->request_data['created_by'],
            'day_in'      => $this->request_data['days'],
            'trn_id'      => $this->request_data['new_policy_id'],
            'trn_type'    => 'leave_policies',
            'description' => 'Generated',
            'leave_id'    => $this->request_data['leave_id'],
            'f_year'      => $this->request_data['f_year'],
            'created_at'  => $created_at,
            'updated_at'  => $created_at,
        ];

        $table_format = [
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
        ];

        if ( isset( $this->request_data['comments'] ) && '' !== $this->request_data['comments'] ) {
            $table_data['description'] = wp_kses_post( $this->request_data['comments'] );
            $table_format[]            = '%s';
        }

        if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_entitlements_new", $table_data, $table_format ) ) {
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

        return false;
    }

    /**
     * This method will create leave_name, leave_policy data.
     *
     * @return bool|int
     */
    private function create_leave_policy() {
        global $wpdb;

        if ( $this->request_data['policy_data']['created_at'] != '' ) {
            $created_at = erp_mysqldate_to_phptimestamp( $this->request_data['policy_data']['created_at'] );
        } else {
            $created_at = erp_current_datetime()->getTimestamp();
        }

        if ( $this->request_data['policy_data']['updated_at'] != '' ) {
            $updated_at = erp_mysqldate_to_phptimestamp( $this->request_data['policy_data']['updated_at'] );
        } else {
            $updated_at = erp_current_datetime()->getTimestamp();
        }

        // insert into erp_hr_leave_policies_new table.
        $table_data = [
            'old_policy_id' => $this->request_data['policy_data']['id'],
            'leave_id'      => $this->request_data['leave_id'],
            'days'          => $this->request_data['policy_data']['value'],
            'color'         => $this->request_data['policy_data']['color'],
            'f_year'        => $this->request_data['f_year'],
            'created_at'    => $created_at,
            'updated_at'    => $updated_at,
        ];

        $table_format = [
            '%d',
            '%d',
            '%d',
            '%s',
            '%d',
            '%d',
            '%d',
        ];

        if ( isset( $this->request_data['policy_data']['department'] ) && '' !== $this->request_data['policy_data']['department'] ) {
            $table_data['department_id'] = $this->request_data['policy_data']['department'];
            $table_format[]              = '%d';
        }

        if ( isset( $this->request_data['policy_data']['designation'] ) && '' !== $this->request_data['policy_data']['designation'] ) {
            $table_data['designation_id'] = $this->request_data['policy_data']['designation'];
            $table_format[]               = '%d';
        }

        if ( isset( $this->request_data['policy_data']['gender'] ) && '' !== $this->request_data['policy_data']['gender'] ) {
            $table_data['gender'] = $this->request_data['policy_data']['gender'];
            $table_format[]       = '%s';
        }

        if ( isset( $this->request_data['policy_data']['marital'] ) && '' !== $this->request_data['policy_data']['marital'] ) {
            $table_data['marital'] = $this->request_data['policy_data']['marital'];
            $table_format[]        = '%s';
        }

        if ( isset( $this->request_data['policy_data']['location'] ) && '' !== $this->request_data['policy_data']['location'] ) {
            $table_data['location_id'] = $this->request_data['policy_data']['location'];
            $table_format[]            = '%d';
        }

        if ( isset( $this->request_data['policy_data']['execute_day'] ) && '' !== $this->request_data['policy_data']['execute_day'] ) {
            $table_data['applicable_from_days'] = $this->request_data['policy_data']['execute_day'];
            $table_format[]                     = '%d';
        }

        if ( isset( $this->request_data['policy_data']['activate'] ) && '1' == $this->request_data['policy_data']['activate'] ) {
            $table_data['apply_for_new_users']  = $this->request_data['policy_data']['activate'];
            $table_format[]                     = '%d';
        }

        if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_policies_new", $table_data, $table_format ) ) {
            error_log(
                print_r(
                    [
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => '(Query error) Insertion failed new leave policies table: ' . $wpdb->last_error,
                    ],
                    true
                )
            );

            return false;
        } else {
            $policy_id = $wpdb->insert_id;
        }

        // insert into erp_hr_leave_policies_segregation_new table.
        $table_data   = [
            'leave_policy_id' => $policy_id,
            'created_at'      => $created_at,
            'updated_at'      => $updated_at,
        ];
        $table_format = [ '%d', '%d', '%d' ];

        if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_policies_segregation_new", $table_data, $table_format ) ) {
            error_log(
                print_r(
                    [
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => '(Query error) Insertion failed new leave policies segregation table: ' . $wpdb->last_error,
                    ],
                    true
                )
            );

            // i'm not sending return false here, because segregation is optional.
        }

        return $policy_id;
    }

    protected function create_orphaned_policies() {
        // check old policy table data to find out any orphaned policy exists or not, if found migrate them to new db
        global $wpdb;

        $orphaned_policies = $wpdb->get_results(
            "SELECT policy.* FROM {$wpdb->prefix}erp_hr_leave_policies as policy WHERE NOT EXISTS ( SELECT  null FROM {$wpdb->prefix}erp_hr_leave_entitlements as en WHERE policy.id = en.policy_id)",
            ARRAY_A
        );

        if ( is_array( $orphaned_policies ) && ! empty( $orphaned_policies ) ) {
            foreach ( $orphaned_policies as $policy_data ) {
                // store datas for further use.
                $this->request_data['policy_data'] = wp_parse_args( $policy_data, $this->request_data['policy_data'] );

                // get financial year for this policy
                $f_year_id = $this->create_financial_year( $policy_data['created_at'], get_current_user_id(), $policy_data['created_at'] );

                if ( false === $f_year_id ) {
                    continue;
                }

                $this->request_data['f_year'] = $f_year_id;

                // get leave name or create one.

                $leave_id = $this->create_leave_name( $this->request_data['policy_data']['name'], $this->request_data['policy_data']['description'], $this->request_data['policy_data']['created_at'], $this->request_data['policy_data']['updated_at'] );

                if ( false === $leave_id ) {
                    continue;
                }

                // save this leave id for further processing.
                $this->request_data['leave_id'] = $leave_id;

                $this->create_leave_policy();
            }
        }
    }

    /**
     * Complete
     */
    protected function complete() {
        parent::complete();

        // create orphaned policies
        $this->create_orphaned_policies();

        global $bg_progess_hr_leave_requests;

        $bg_progess_hr_leave_requests->dispatch();
    }
}

global $bg_progess_hr_leaves_entitlements;
$bg_progess_hr_leaves_entitlements = new ERPHRLeaveEntitlements();
