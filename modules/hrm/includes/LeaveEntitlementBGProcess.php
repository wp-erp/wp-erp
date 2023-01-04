<?php

namespace WeDevs\ERP\HRM;

use WP_Background_Process;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
}

/**
 * Class LeaveEntitlementBGProcess
 */
class LeaveEntitlementBGProcess extends WP_Background_Process {

    /**
     * Background process id, must be unique.
     *
     * @var string
     */
    protected $action = 'erp_leave_entl_bg_process';

    protected $request_data = [
        'user_id'       => 0,
        'leave_id'      => 0,
        'created_by'    => 0,
        'trn_id'        => 0,
        'trn_type'      => 'leave_policies',
        'day_in'        => 0,
        'day_out'       => 0,
        'description'   => 'Generated',
        'f_year'        => 0,
    ];

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param array $args Queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $args ) {
        $this->request_data = wp_parse_args( $args, $this->request_data );
        $inserted           = erp_hr_leave_insert_entitlement( $this->request_data );

        return false;
    }

    /**
     * Complete
     */
    protected function complete() {
        parent::complete();
    }
}

new LeaveEntitlementBGProcess();
