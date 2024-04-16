<?php

namespace WeDevs\ERP\Updates\BP;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
}

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ERP_BG_PROCESS_1_6_5
 */
class ERP_BG_PROCESS_1_6_5 extends \WP_Background_Process {

    /**
     * Background process name.
     *
     * @var string
     */
    protected $action = 'erp_bg_process_1_6_5';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param array $db_data queue item to iterate over
     *
     * @return mixed
     */
    protected function task( $db_data ) {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/install-helper.php';

        if ( isset( $db_data['fields'] ) && is_array( $db_data['fields'] ) ) {

            // get required data from input array
            $table_name     = $wpdb->prefix . $db_data['table'];
            $col_type       = $db_data['type'];
            $null           = $db_data['null'];
            $is_null        = $null === 'NULL' ? 'YES' : 'NO';
            $default_value  = $db_data['default'];

            foreach ( $db_data['fields'] as $field ) {
                // Check the column.
                if ( ! check_column( $table_name, $field, $col_type ) ) {
                    if ( $wpdb->last_error ) {
                        error_log( __FILE__ . ' ' . __LINE__ . ' ' . $wpdb->last_error . PHP_EOL );
                    }
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

global $erp_bg_process_1_6_5;
$erp_bg_process_1_6_5 = new ERP_BG_PROCESS_1_6_5();
