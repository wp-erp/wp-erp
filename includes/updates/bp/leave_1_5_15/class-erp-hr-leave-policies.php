<?php
namespace WeDevs\ERP\Updates\BP\Leave;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	require_once WPERP_INCLUDES . '/lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	require_once WPERP_INCLUDES . '/lib/bgprocess/wp-background-process.php';
}

/**
 * Migrate Leave Policies Data to new policy table
 *
 * For each policy table entry, there will be an entry for new leave table, leave policy table, leave sagregation
 * @since 1.5.15
 * @package WeDevs\ERP\Updates\BP\Leave
 */

class ERP_HR_Leave_Policies extends \WP_Background_Process {

	/**
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
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
	protected function task( $policy ) {
        global $wpdb;


        $policy_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb}erp_hr_leave_policies WHERE id = %d", [ $policy ]
        ), ARRAY_A );

        if ( null === $policy_data ) {
            //no result found: can be because of query error, handle this problem here probably log this error
            //todo: keep log here

        }
        elseif ( is_array( $policy_data ) && ! empty( $policy_data ) ) {

            if ( $policy_data['created_at'] != '' ) {
                $created_at = $this->mysql_date_to_timestamp( $policy_data['created_at'] );
            }
            else {
                $created_at = time();
            }

            if ( $policy_data['updated_at'] != '' ) {
                $updated_at = $this->mysql_date_to_timestamp( $policy_data['updated_at'] );
            }
            else {
                $updated_at = time();
            }

            //insert into erp_hr_leaves_new table

            $table_data = [
                'name'          => $policy_data['name'],
                'created_at'    => $created_at,
                'updated_at'    => $updated_at,
            ];

            $table_format = [
                '%s', '%d', '%d'
            ];

            if ( $policy_data['description'] != ''  ) {
                $table_data['description'] = trim( $policy_data['description'] );
                $table_format[] = '%s';
            }

            $leave_id = 0;
            if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leaves_new", $table_data, $table_format ) === false ) {
                //todo: query error, do loging or something here
            }
            else {
                $leave_id = $wpdb->insert_id;
            }

            //insert into erp_hr_leave_policies_new table
            $policy_id = 0;
            if ( $leave_id ) {

                $table_data = [
                    'old_policy_id' => $policy,
                    'leave_id'      => $leave_id,
                    'days'          => $policy_data['value'],
                    'color'         => $policy_data['color'],
                    'created_at'    => $created_at,
                    'updated_at'    => $updated_at,
                ];

                $table_format = [
                    '%d', '%d', '%d', '%s', '%d', '%d'
                ];

                if ( isset( $policy_data['department'] ) && $policy_data['department'] != '' ) {
                    $table_data['department_id'] = $policy_data['department'];
                    $table_format[] = '%d';
                }

                if ( isset( $policy_data['designation'] ) && $policy_data['designation'] != '' ) {
                    $table_data['designation_id'] = $policy_data['designation'];
                    $table_format[] = '%d';
                }

                if ( isset( $policy_data['gender'] ) && $policy_data['gender'] != '' ) {
                    $table_data['gender'] = $policy_data['gender'];
                    $table_format[] = '%s';
                }

                if ( isset( $policy_data['marital'] ) && $policy_data['marital'] != '' ) {
                    $table_data['marital'] = $policy_data['marital'];
                    $table_format[] = '%s';
                }

                if ( isset( $policy_data['location'] ) && $policy_data['location'] != '' ) {
                    $table_data['location_id'] = $policy_data['location'];
                    $table_format[] = '%d';
                }

                if ( isset( $policy_data['effective_date'] ) && $policy_data['effective_date'] != '' ) {
                    $date = $this->mysql_date_to_timestamp( $policy_data['effective_date'] );
                    if ( $date !== false ) {
                        $table_data['f_year'] = date( 'Y', $date );
                    }
                    else {
                        $table_data['f_year'] = date( 'Y', time() );
                    }

                    $table_format[] = '%d';
                }

                if ( isset( $policy_data['execute_day'] ) && $policy_data[''] != 'execute_day' ) {
                    $table_data['applicable_from_days'] = $policy_data['execute_day'];
                    $table_format[] = '%d';
                }

                if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_policies_new", $table_data, $table_format ) === false ) {
                    //todo: query error, log this error for debugging

                }
                else {
                    $policy_id = $wpdb->insert_id;
                }
            }

            //insert into erp_hr_leave_policies_segregation_new table
            if ( $policy_id ) {
                $table_data = [ 'leave_policy_id' => $policy_id ];
                $table_format = [ '%d' ];

                if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_policies_segregation_new", $table_data, $table_format ) === false ) {
                    //todo: query error, log this error for debugging

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

    protected function mysql_date_to_timestamp( $time ) {

	    $timezone = wp_timezone();
	    $datetime = date_create_immutable_from_format( 'Y-m-d H:i:s', $time, $timezone );

        if ( false === $datetime ) {
            return false;
        }

        $datetime =  $datetime->setTimezone( $timezone );

        return $datetime->getTimestamp();
    }

}
