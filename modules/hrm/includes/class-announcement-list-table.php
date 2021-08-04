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
                return esc_html( $item->post_title );

            case 'type':
                $type = ucfirst( get_post_meta( $item->ID, '_announcement_type', true ) );
                return ( empty( $type ) ? 'Unknown' : $type );

            case 'date':
                return erp_format_date( $item->post_date );

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Render checkbox column
     *
     * @param Object $item
     *
     * @return void
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="announcement_ids[]" value="%d" />', $item->ID );
    }

    /**
     * Render the name column
     *
     * @param Object $item
     *
     * @return void
     */
    public function column_title( $item ) {
        $actions = [];

        $params = [
            'post' => $item->ID,
        ];

        $params['action'] = 'edit';
        $edit_url         = add_query_arg( $params, admin_url( 'post.php' ) );

        $params['action']   = 'trash';
        $params['_wpnonce'] = wp_create_nonce( 'trash-post_' . $item->ID );
        $delete_url         = add_query_arg( $params, admin_url( 'post.php' ) );

        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $edit_url, $item->id, esc_html__( 'Edit this item', 'erp' ), esc_html__( 'Edit', 'erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="submitcopy" data-id="%d" title="%s">%s</a>', $delete_url, $item->id, esc_html__( 'Delete this item', 'erp' ), esc_html__( 'Delete', 'erp' ) );

        return sprintf( '<strong>%s</strong> %s', esc_html( $item->post_title ), $this->row_actions( $actions ) );
    }

    /**
     * Render Sent To Column
     *
     * @param Object $item
     *
     * @return void
     */
    public function column_sent_to( $item ) {
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
    }

    public function get_bulk_actions() {
        $actions = [
            'delete_announcement'   => __( 'Move to trash', 'erp' ),
        ];

        return $actions;
    }

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
            'offset'      => $offset,
            'numberposts' => $per_page,
        ];

        $this->items = erp_hr_get_announcements( $args );

        $this->set_pagination_args( [
            'total_items' => erp_hr_get_announcements_count( $args ),
            'per_page'    => $per_page,
        ] );
    }
}
