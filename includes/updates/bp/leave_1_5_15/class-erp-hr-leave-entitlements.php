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

class ERP_HR_Leave_Entitlements extends \WP_Background_Process {

	/**
	 * @var string
	 */
    protected $action = 'erp_hr_leaves_entitlements_1_5_15';

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
	protected function task( $entitlement_id ) {
        global $wpdb;


        $entitlement_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb}erp_hr_leave_entitlements WHERE id = %d", [ $entitlement_id ]
        ), ARRAY_A );

        if ( null === $entitlement_data ) {
            //no result found: can be because of query error, handle this problem here probably log this error
            //todo: keep log here

        }
        elseif ( is_array( $entitlement_data ) && ! empty( $entitlement_data ) ) {

            if ( $entitlement_data['created_on'] != '' ) {
                $created_at = $this->mysql_date_to_timestamp( $entitlement_data['created_on'] );
            }
            else {
                $created_at = time();
            }

            //insert into erp_hr_leaves_new table

            $table_data = [
                'user_id'       => $entitlement_data['user_id'],
                'created_by'    => $entitlement_data['created_by'],
                'day_in'        => $entitlement_data['days'],
                'trn_type'      => 'leave_policies',
                'created_at'    => $created_at,
                'updated_at'    => $created_at,
            ];

            $table_format = [
                '%d', '%d', '%d', '%s', '%d', '%d'
            ];

            if ( isset( $entitlement_data['comments'] ) && $entitlement_data['comments'] != '' ) {
                $table_data['description'] = trim( $entitlement_data['comments'] );
                $table_format[] = '%s';
            }

            //get policy table data
            $policy_data = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, leave_id, f_year FROM {$wpdb->prefix}erp_hr_leave_policies_new WHERE old_policy_id = %d", [ $entitlement_data['policy_id'] ]
            ) );

            if ( null === $policy_data ) {
                //no result found: can be because of query error, handle this problem here probably log this error
                //todo: keep log here

            }
            elseif ( is_array( $policy_data ) && ! empty( $policy_data ) ) {
                $table_data['trn_id']   = $policy_data['id'];
                $table_format[] = '%d';

                $table_data['leave_id'] = $policy_data['leave_id'];
                $table_format[] = '%d';

                $table_data['f_year']   = $policy_data['f_year'];
                $table_format[] = '%d';


                if ( $wpdb->insert( "{$wpdb->prefix}erp_hr_leave_entitlements_new", $table_data, $table_format ) === false ) {
                    //todo: query error, do loging or something here
                }
                else {
                    return false;
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
