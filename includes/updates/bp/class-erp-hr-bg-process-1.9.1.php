<?php

namespace WeDevs\ERP\Updates\BP;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
    require_once WPERP_INCLUDES . '/lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
    require_once WPERP_INCLUDES . '/lib/bgprocess/wp-background-process.php';
}

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ERP_HR_BG_PROCESS_1_9_1
 */
class ERP_HR_BG_PROCESS_1_9_1 extends \WP_Background_Process {

    /**
     * Background process action name
     *
     * @var string
     */
    protected $action = 'erp_hr_bg_process_1_9_1';

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
            $pay     = preg_replace( '/[^0-9.]/', '', $pay_rate  );
            $pay_arr = explode( '.', $pay );

            if ( count( $pay_arr ) > 1 ) {
                $pay = $pay_arr[0] . '.' . substr( $pay_arr[1], 0, 2 );
            }

            $wpdb->update(
                "{$wpdb->prefix}erp_hr_employees",
                [ 'pay_rate' => $pay ],
                [ 'user_id'  => $user_id ]
            );
        }

        $phone      = get_user_meta( $user_id, 'phone', true );
        $mobile     = get_user_meta( $user_id, 'mobile', true );
        $work_phone = get_user_meta( $user_id, 'work_phone', true );

        if ( ! empty( $phone ) && ! erp_is_valid_contact_no( $phone ) ) {
            $phone = erp_sanitize_phone_number( $phone, true );

            update_user_meta( $user_id, 'phone', $phone );
        }

        if ( ! empty( $mobile ) && ! erp_is_valid_contact_no( $mobile ) ) {
            $mobile = erp_sanitize_phone_number( $mobile, true );

            update_user_meta( $user_id, 'mobile', $mobile );
        }

        if ( ! empty( $work_phone ) && ! erp_is_valid_contact_no( $work_phone ) ) {
            $work_phone = erp_sanitize_phone_number( $work_phone, true );

            update_user_meta( $user_id, 'work_phone', $work_phone );
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

global $erp_hr_bg_process_1_9_1;

$erp_hr_bg_process_1_9_1 = new ERP_HR_BG_PROCESS_1_9_1();
