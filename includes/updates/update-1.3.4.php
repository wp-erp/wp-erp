<?php
/**
 * Delete all the entitlements those created for inactive employees
 * Version 1.3.4 updated
 *
 *
 * @return void
 */
function erp_att_update_1_3_4() {
    $period = erp_att_get_start_end_date( 'this_year' );

    $employees    = \WeDevs\ERP\HRM\Models\Employee::select( 'user_id' )->where( 'status', '!=', 'active' )->get()->toArray();
    if( $period && $employees ){
        $entitlements = \WeDevs\ERP\HRM\Models\Leave_Entitlement::select( 'id' )
                                                                ->whereIn( 'user_id', $employees )
                                                                ->whereDate( 'from_date', '>=', $period['start'] )
                                                                ->whereDate( 'to_date', '<=', $period['end'] )
                                                                ->delete();
    }

}

erp_att_update_1_3_4();
