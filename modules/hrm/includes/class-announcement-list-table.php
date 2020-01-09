<?php
namespace Wedevs\ERP\HRM;

/**
 *  List table class
 */
class Announcement_List_Table extends \WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singuler'  => 'announcement',
            'plural'    =>  'announcements',
            'ajax'      =>  true
        ) );
    }

    /**
     * Message to show if no announcement found.
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No announcement  found.' );
    }

    /**
     * Get all columns
     *
     * @return array
     */
    public function get_columns() {
        $columns    =   array(
            'cb'        =>  '<input type="checkbox" />',
            'title'     =>  __( 'Title', 'erp' ),
            'sent_to'   =>  __( 'Sent to', 'erp' ),
            'type'      =>  __( 'Type', 'erp' ),
            'date'      =>  __( 'Date', 'erp' ),
        );

        return $columns;
    }

    public function get_bulk_actions() {
        $actions = array(
            'edit_announce_ment'    =>  __( 'Edit', 'erp' ),
            'delete_announcement'   =>  __( 'Move to trash', 'erp' ),
        );

        return $actions;
    }
}
