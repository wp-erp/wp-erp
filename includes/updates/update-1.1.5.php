<?php
/**
 * Update leave holiday tables end column by increase 1 day.
 * 
 * @return void
 */
function erp_ac_update_holiday_table_1_1_5() {
	$results = \WeDevs\ERP\HRM\Models\Leave_Holiday::select('id', 'end')->get();
        
	if ( $results ) {
	    foreach ( $results as $key => $result ) {
	        $date = new \DateTime($result->end);
	        $date->modify('+1 day');
	        $new_date = $date->format('Y-m-d H:i:s') ;
	        \WeDevs\ERP\HRM\Models\Leave_Holiday::where( 'id', '=', $result->id )->update(['end' => $new_date]);
	    }
	}
}

/**
 * Location tables zip column type change from int to varchar 
 * 
 * @return void
 */
function erp_ac_update_location_table_1_1_5() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'erp_company_locations';
	$wpdb->query( "ALTER TABLE $table_name CHANGE `zip` `zip` VARCHAR(10) NULL DEFAULT NULL;" );
}

erp_ac_update_holiday_table_1_1_5();
erp_ac_update_location_table_1_1_5();