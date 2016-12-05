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

    $peoples = \WeDevs\ERP\Framework\Models\Peoplemeta::whereNotNull( 'user_id' )->get()->toArray();

    foreach ( $peoles as $key => $value ) {
        # code...
    }

}


erp_update_poeple_meta_1_1_7();