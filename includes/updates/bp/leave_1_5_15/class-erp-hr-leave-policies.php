<?php
namespace WeDevs\ERP\Updates\BP\Leave;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/lib/bgprocess/wp-background-process.php';
}

/**
 * Migrate Leave Policies Data to new leave policy table.
 *
 * For each policy table entry, there will be an entry for new leave table, leave policy table and leave segregation table.
 *
 * @since 1.5.15
 * @package WeDevs\ERP\Updates\BP\Leave
 */
class ERP_HR_Leave_Policies extends \WP_Background_Process {

    /**
     * Background process name, must be unique.
     *
     * @var string
     */
    protected $action = 'erp_hr_leaves_policies_1_5_15';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param int $policy single policy id to migrate.
     *
     * @return bool false on successful task end.
     */
    protected function task( $policy ) {
        global $wpdb;

        $policy_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb}erp_hr_leave_policies WHERE id = %d",
                array( $policy )
            ),
            ARRAY_A
        );

        if ( null === $policy_data ) {
            // no result found: can be because of query error, handle this problem here probably log this error
            // todo: keep log here
            error_log( print_r(
                array(
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => '(Query error) Policy not found.'
                ), true )
            );

        } elseif ( is_array( $policy_data ) && ! empty( $policy_data ) ) {

            if ( $policy_data['created_at'] != '' ) {
                $created_at = erp_mysqldate_to_phptimestamp( $policy_data['created_at'] );
            } else {
                $created_at = current_datetime()->getTimestamp();
            }

            if ( $policy_data['updated_at'] != '' ) {
                $updated_at = erp_mysqldate_to_phptimestamp( $policy_data['updated_at'] );
            } else {
                $updated_at = current_datetime()->getTimestamp();
            }

            // insert into erp_hr_leaves_new table.
            $table_data = array(
                'name'       => $policy_data['name'],
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            );

            $table_format = array(
                '%s',
                '%d',
                '%d',
            );

            if ( '' !== $policy_data['description'] ) {
                $table_data['description'] = wp_kses_post( $policy_data['description'] );
                $table_format[]            = '%s';
            }

            $leave_id = 0;
            if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leaves_new", $table_data, $table_format ) ) {
                error_log( print_r(
                    array(
                        'file' => __FILE__, 'line' => __LINE__,
                        'message' => '(Query error) Insertion failed new leaves table.'
                    ), true )
                );
                // todo: query error, do loging or something here.
            } else {
                $leave_id = $wpdb->insert_id;
            }

            // insert into erp_hr_leave_policies_new table.
            $policy_id = 0;
            if ( $leave_id ) {
                $table_data = array(
                    'old_policy_id' => $policy,
                    'leave_id'      => $leave_id,
                    'days'          => $policy_data['value'],
                    'color'         => $policy_data['color'],
                    'created_at'    => $created_at,
                    'updated_at'    => $updated_at,
                );

                $table_format = array(
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                );

                if ( isset( $policy_data['department'] ) && '' !== $policy_data['department'] ) {
                    $table_data['department_id'] = $policy_data['department'];
                    $table_format[]              = '%d';
                }

                if ( isset( $policy_data['designation'] ) && '' !== $policy_data['designation'] ) {
                    $table_data['designation_id'] = $policy_data['designation'];
                    $table_format[]               = '%d';
                }

                if ( isset( $policy_data['gender'] ) && '' !== $policy_data['gender'] ) {
                    $table_data['gender'] = $policy_data['gender'];
                    $table_format[]       = '%s';
                }

                if ( isset( $policy_data['marital'] ) && '' !== $policy_data['marital'] ) {
                    $table_data['marital'] = $policy_data['marital'];
                    $table_format[]        = '%s';
                }

                if ( isset( $policy_data['location'] ) && '' !== $policy_data['location'] ) {
                    $table_data['location_id'] = $policy_data['location'];
                    $table_format[]            = '%d';
                }

                if ( isset( $policy_data['effective_date'] ) && '' !== $policy_data['effective_date'] ) {
                    $date = erp_mysqldate_to_phptimestamp( $policy_data['effective_date'], false );

                    if ( false !== $date ) {
                        $table_data['f_year'] = $date->format('Y');
                    } else {
                        $table_data['f_year'] = current_datetime()->format('Y');
                    }

                    $table_format[] = '%d';
                }

                if ( isset( $policy_data['execute_day'] ) && '' !== $policy_data['execute_day'] ) {
                    $table_data['applicable_from_days'] = $policy_data['execute_day'];
                    $table_format[]                     = '%d';
                }

                if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_policies_new", $table_data, $table_format ) ) {
                    error_log( print_r(
                        array(
                            'file' => __FILE__, 'line' => __LINE__,
                            'message' => '(Query error) Insertion failed new leave policies table.'
                        ), true )
                    );
                    // todo: query error, log this error for debugging.

                } else {
                    $policy_id = $wpdb->insert_id;
                }
            }

            // insert into erp_hr_leave_policies_segregation_new table.
            if ( $policy_id ) {
                $table_data   = array(
                    'leave_policy_id' => $policy_id,
                    'created_at'      => $created_at,
                    'updated_at'      => $updated_at,
                );
                $table_format = array( '%d', '%d', '%d' );

                if ( false === $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_policies_segregation_new", $table_data, $table_format ) ) {
                    // todo: query error, log this error for debugging.
                    error_log( print_r(
                        array(
                            'file' => __FILE__, 'line' => __LINE__,
                            'message' => '(Query error) Insertion failed new leave policies segregation table.'
                        ), true )
                    );
                }
            }
        }

        return false;
    }

    /**
     * Complete
     */
    protected function complete() {
        parent::complete();
    }
}
