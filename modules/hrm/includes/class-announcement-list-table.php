<?php

namespace WeDevs\ERP\HRM;

use WP_List_Table;

use function PHPSTORM_META\type;

/**
 *  List table class
 */
class Announcement_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct( [
            'singuler'  => 'announcement',
            'plural'    => 'announcements',
            'ajax'      => true,
        ] );
    }

    /**
     * Message to show if no announcement found.
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No announcements found.', 'erp' );
    }

    /**
     * Get all columns
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'        => '<input type="checkbox" />',
            'title'     => __( 'Title', 'erp' ),
            'type'      => __( 'Type', 'erp' ),
            'sent_to'   => __( 'Sent to', 'erp' ),
            'date'      => __( 'Date', 'erp' ),
        ];

        return $columns;
    }

    /**
     * Default column values if no callback found
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'title':
                return "<a href='" . admin_url( "post.php?post={$item->ID}&action=edit" ) . "'>{$item->post_title}</a>";

            case 'sent_to':
                $type = get_post_meta( $item->ID, '_announcement_type', true );
                $sent_to = [];
                if ( $type === 'by_department' ) {
                    $sent_to = get_post_meta( $item->ID, '_announcement_department', true );
                    $sent_to = array_map( function( $id ) {
                        if ( $d = \WeDevs\ERP\HRM\Models\Department::find( (int) $id ) ) {
                            return $d->title;
                        }
                    }, $sent_to );
                } elseif ( $type === 'by_designation' ) {
                    $sent_to = get_post_meta( $item->ID, '_announcement_designation', true );
                    $sent_to = array_map( function( $id ) {
                        if ( $d = \WeDevs\ERP\HRM\Models\Designation::find( (int) $id ) ) {
                            return $d->title;
                        }
                    }, $sent_to );
                } else {
                    $sent_to = get_post_meta( $item->ID, '_announcement_selected_user', true );
                    $sent_to = array_map( function( $id ) {
                        if ( $u = get_user_by( 'id', $id ) ) {
                            return $u->display_name;
                        }
                    }, $sent_to );
                }

                return count( $sent_to ) === 0 ? 'None' : implode( ', ', $sent_to );

            case 'type':
                $type = ucfirst( get_post_meta( $item->ID, '_announcement_type', true ) );
                return ( empty( $type ) ? 'Unknown' : $type );

            case 'date':
                return erp_format_date( $item->post_date );

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /* public function get_bulk_actions() {
        $actions = [
            'delete_announcement'   => __( 'Move to trash', 'erp' ),
        ];

        return $actions;
    } */

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = [];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;

        // only ncessary because we have sample data
        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        $this->items = erp_hr_get_announcements( /* $args */ );

        $this->set_pagination_args( [
            'total_items' => erp_hr_get_announcements_count( $args ),
            'per_page'    => $per_page,
        ] );
    }
}
