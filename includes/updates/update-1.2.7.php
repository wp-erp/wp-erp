<?php

function erp_crm_alter_peoples_table_1_2_7() {
    global $wpdb;
    $wpdb->query( "set wait_timeout = 200" );

    $people_table         = $wpdb->prefix . 'erp_peoples';
    $people_table_columns = $wpdb->get_col( "DESC " . $people_table, 0 );

    if ( ! in_array( 'life_stage', $people_table_columns ) ) {
        $alter_peoples_table_sql = "ALTER TABLE {$people_table} 
				  	ADD COLUMN  
					life_stage VARCHAR(100) DEFAULT NULL 
					AFTER currency";
        $wpdb->query( $alter_peoples_table_sql );
    }

    if ( ! in_array( 'contact_owner', $people_table_columns ) ) {
        $alter_peoples_table_sql = "ALTER TABLE {$people_table} 
				  	ADD COLUMN  
					contact_owner bigint(20) DEFAULT NULL
					AFTER currency";
        $wpdb->query( $alter_peoples_table_sql );
    }

    if ( ! in_array( 'hash', $people_table_columns ) ) {
        $alter_peoples_table_sql = "ALTER TABLE {$people_table} 
				  	ADD COLUMN  
					`hash` VARCHAR(40) NULL DEFAULT NULL
					AFTER currency";
        $wpdb->query( $alter_peoples_table_sql );
    }

}

erp_crm_alter_peoples_table_1_2_7();


/**
 * Move people life stage from erp_peoplemeta table to base erp_peoples table
 *
 * @since 1.2.6
 */
function erp_crm_move_people_life_stage_from_meta_to_base_table_1_2_7() {

    global $wpdb;
    $wpdb->query( "set wait_timeout = 1200" );
    $people_table         = $wpdb->prefix . 'erp_peoples';
    $people_table_columns = $wpdb->get_col( "DESC " . $people_table, 0 );
    $peoplemeta_table     = $wpdb->prefix . 'erp_peoplemeta';

    $metas_to_remove = array();

    if ( in_array( 'life_stage', $people_table_columns ) ) {


        $migrate_life_stage_sql = "UPDATE {$people_table},"
                                  . " (select * from {$peoplemeta_table} where meta_key = 'life_stage') AS meta"
                                  . " SET"
                                  . " wp_erp_peoples.life_stage = meta.meta_value"
                                  . " where wp_erp_peoples.id = meta.erp_people_id";
        $wpdb->query( $migrate_life_stage_sql );

        $metas_to_remove[] = 'life_stage';
    }

    if ( in_array( 'contact_owner', $people_table_columns ) ) {

        $peoplemeta_table = $wpdb->prefix . 'erp_peoplemeta';

        $migrate_contact_owner_sql = "UPDATE {$people_table}, "
                                     . " (select * from {$peoplemeta_table} where meta_key = 'contact_owner') AS meta"
                                     . " SET"
                                     . " wp_erp_peoples.contact_owner = meta.meta_value"
                                     . " where wp_erp_peoples.id = meta.erp_people_id";
        $wpdb->query( $migrate_contact_owner_sql );

        $metas_to_remove[] = 'contact_owner';
        $metas_to_remove[] = 'created_by';
        $metas_to_remove[] = '_assign_crm_agent';
    }

    if ( in_array( 'hash', $people_table_columns ) ) {

        $peoplemeta_table = $wpdb->prefix . 'erp_peoplemeta';

        $migrate_contact_owner_sql = "UPDATE {$people_table}, "
                                     . " (select * from {$peoplemeta_table} where meta_key = 'hash') AS meta"
                                     . " SET"
                                     . " wp_erp_peoples.hash = meta.meta_value"
                                     . " where wp_erp_peoples.id = meta.erp_people_id";
        $wpdb->query( $migrate_contact_owner_sql );

        $metas_to_remove[] = 'hash';
    }

        //@todo remove residual data on more updated version
//    if ( ! empty( $metas_to_remove ) ) {
//        $array_string = implode( "','", $metas_to_remove );
//        $sql          = "DELETE FROM {$peoplemeta_table} WHERE meta_key IN ('{$array_string}')";
//        $wpdb->query( $sql );
//    }

}

erp_crm_move_people_life_stage_from_meta_to_base_table_1_2_7();
