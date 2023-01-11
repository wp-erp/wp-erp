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
 * Class ERP_HR_BG_PROCESS_1_10_0
 */
class ERP_HR_BG_PROCESS_1_10_0 extends \WP_Background_Process {

    /**
     * Background process action name
     *
     * @var string
     */
    protected $action = 'erp_hr_bg_process_1_10_0';

    /**
     * Performs the queued task
     *
     * @param array $employee queue containing single employee object to iterate over
     *
     * @return mixed
     */
    protected function task( $employee ) {
        global $wpdb;

        $user_id  = $employee['id'];
        $pay_rate = $employee['pay'];

        if ( ! erp_is_valid_currency_amount( $pay_rate ) ) {
            $pay = preg_replace( '/[^0-9.]/', '', $pay_rate  );

            $wpdb->update(
                "{$wpdb->prefix}erp_hr_employees",
                [ 'pay_rate' => $pay ],
                [ 'user_id'  => $user_id ]
            );
        }

        $user_meta = [
            'phone'      => get_user_meta( $user_id, 'phone', true ),
            'mobile'     => get_user_meta( $user_id, 'mobile', true ),
            'work_phone' => get_user_meta( $user_id, 'work_phone', true )
        ];

        foreach ( $user_meta as $key => $value ) {
            if ( ! empty( $value ) && ! erp_is_valid_contact_no( $value ) ) {
                $value = erp_sanitize_phone_number( $value );

                if ( strlen( $value ) > 18 ) {
                    $value = substr( $value, 0, 18 );
                }

                update_user_meta( $user_id, $key, $value );
            }
        }

        return false;
    }

    /**
     * Complete the process
     */
    protected function complete() {
        parent::complete();
    }
}

global $erp_hr_bg_process_1_10_0;

$erp_hr_bg_process_1_10_0 = new ERP_HR_BG_PROCESS_1_10_0();
