<?php

/**
 * Auto create customer when creating CRM contact/company
 * 
 * @param  $customer
 * @param  $customer_id
 * @param  $data
 * 
 * @since  1.2.7
 * 
 * @return void
 */
function erp_ac_customer_create_from_crm( $customer, $customer_id, $data ) {

	$customer_auto_import = (int) erp_get_option( 'customer_auto_import', false, 0 );
	$crm_user_type 		  = erp_get_option( 'crm_user_type', false, [] ); // Contact or Company

    if ( ! $customer_auto_import ) {
        return;
    }

    if ( ! count( $crm_user_type ) ) {
    	return;
    }

    if ( is_null( $data['company'] ) ) {
        // the created crm user type is NOT a company
        if ( ! in_array( 'contact', $crm_user_type ) ) {
            return;
        }
    } else {
        if ( ! in_array( 'company', $crm_user_type ) ) {
            return;
        }
    }

	$data['people_id'] = $customer_id;
	$data['type'] = 'customer';

    erp_convert_to_people( $data );
}
