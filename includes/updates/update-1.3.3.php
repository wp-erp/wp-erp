<?php

/**
 * Update CRM new roles and capabilities
 *
 * @since 1.3.3
 *
 * @return void
 */
function wperp_update_1_3_3_update_holidays_table() {
    $holidays = \WeDevs\ERP\HRM\Models\Leave_Holiday::all();
    foreach ( $holidays as $holiday ) {
        $end = $holiday->end;
        $corrected_end = date('Y-m-d 23:59:59', strtotime('-1 day', strtotime($end)));
        $holiday->end = $corrected_end;
        $holiday->save();
    }
}
wperp_update_1_3_3_update_holidays_table();

