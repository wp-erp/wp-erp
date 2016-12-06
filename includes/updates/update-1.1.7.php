<?php

/**
 * Update contact_owner meta in people and user meta table
 *
 * @since 1.1.7
 *
 * @return void
 **/
function erp_update_poeple_meta_1_1_7() {
    \WeDevs\ORM\WP\UserMeta::where( 'meta_key', '_assign_crm_agent' )->update( [ 'meta_key' => 'contact_owner' ] );
    \WeDevs\ERP\Framework\Models\Peoplemeta::where( 'meta_key', '_assign_crm_agent' )->update( [ 'meta_key' => 'contact_owner' ] );
}

/**
 * Sync people with wp user
 *
 * @since 1.1.7
 *
 * @return void
 **/
function erp_update_poeples_1_1_7() {

    $people = \WeDevs\ERP\Framework\Models\People::whereNotNull( 'user_id' )->where( 'user_id', '!=', 0 )->get();

    $people->each( function ( $contact ) {
        $meta        = $contact->meta()->availableMeta()->lists( 'meta_value', 'meta_key' );
        $main_fields = $contact->toArray();

        $all_fields = array_merge( $main_fields, $meta );

        erp_insert_people( $all_fields );
    } );
}

// Run udpater functions
erp_update_poeple_meta_1_1_7();
erp_update_poeples_1_1_7();