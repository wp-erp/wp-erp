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
    protected $action = 'erp_hr_leaves_1_5_15_process';

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

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
        parent::complete();
    }

}
