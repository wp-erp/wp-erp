<?php

namespace WeDevs\ERP\HRM;

use WP_List_Table;

use function PHPSTORM_META\type;

/**
 *  List table class
 */
class Announcement_List_Table extends WP_List_Table {
    private $counts = [];

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
     * @since 1.9.1
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
     * @since 1.9.1
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
     * @since 1.9.1
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
        $edit_text        = esc_html__( 'Edit', 'erp' );
        $edit_title       = esc_html__( 'Edit this item', 'erp' );
        if ( ! empty( $_GET['status'] ) && wp_unslash( $_GET['status'] ) === 'trash' ) {
            $params['action']   = 'untrash';
            $params['_wpnonce'] = wp_create_nonce( 'untrash-post_' . $item->ID );
            $edit_text          = esc_html__( 'Restore', 'erp' );
            $edit_title         = esc_html__( 'Restore this item', 'erp' );
        }
        $edit_url = add_query_arg( $params, admin_url( 'post.php' ) );

        $params['action']   = ( ! empty( $_GET['status'] ) && wp_unslash( $_GET['status'] ) === 'trash' ) ? 'delete' : 'trash';
        $params['_wpnonce'] = wp_create_nonce( $params['action'] . '-post_' . $item->ID );
        $delete_url         = add_query_arg( $params, admin_url( 'post.php' ) );

        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $edit_url, $item->id, $edit_title, $edit_text );
        $actions['delete'] = sprintf( '<a href="%s" class="submitcopy" data-id="%d" title="%s">%s</a>', $delete_url, $item->id, esc_html__( 'Delete this item', 'erp' ), esc_html__( 'Delete', 'erp' ) );

        return sprintf( '<strong>%s</strong> %s', esc_html( $item->post_title ), $this->row_actions( $actions ) );
    }

    /**
     * Render Sent To Column
     *
     * @since 1.9.1
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

    /**
     * Get bulk actions for the table
     *
     * @since 1.9.1
     *
     * @return void
     */
    public function get_bulk_actions() {
        $actions = [
            'delete_announcement'   => __( 'Move to trash', 'erp' ),
        ];
        if ( ! empty( $_GET['status'] ) && wp_unslash( $_GET['status'] ) === 'trash' ) {
            unset( $actions['delete_announcement'] );
            $actions['delete_announcement_p'] = __( 'Delete Parmanently', 'erp' );
        }
        return $actions;
    }

    /**
     * Extra filters for the list table
     *
     * @since 1.9.1
     *
     * @param string $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $ann_start_date = ( ! empty( $_GET['ann_start_date'] ) ) ? sanitize_text_field( wp_unslash( $_GET['ann_start_date'] ) ) : '';
        $ann_end_date   = ( ! empty( $_GET['ann_end_date'] ) ) ? sanitize_text_field( wp_unslash( $_GET['ann_end_date'] ) ) : '';
        $status         = ( ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
        ?>
        <div class="wperp-filter-dropdown" style="margin: -46px 0 0 0;">
            <a class="wperp-btn btn--default"><span class="dashicons dashicons-filter"></span>Filters<span class="dashicons dashicons-arrow-down-alt2"></span></a>

            <div class="erp-dropdown-filter-content" id="erp-dropdown-content">
                <div class="wperp-filter-panel wperp-filter-panel-default">
                    <h3><?php esc_html_e( 'Filter', 'erp' ); ?></h3>
                    <div class="wperp-filter-panel-body">
                        <label for="ann_start_date"><?php esc_html_e( 'Start Date', 'erp' ); ?></label>
                        <input autocomplete="off" style="border-radius: 3px; width: 100%; border: 1px black solid;" class="erp-date-field" name="ann_start_date" id="ann_start_date" value="<?php echo $ann_start_date; ?>"/>

                        <label for="ann_end_date"><?php esc_html_e( 'End Date', 'erp' ); ?></label>
                        <input autocomplete="off" style="border-radius: 3px; width: 100%; border: 1px black solid;" class="erp-date-field" name="ann_end_date" id="ann_end_date" value="<?php echo $ann_end_date; ?>"/>
                        <input hidden name="status" value="<?php echo $status; ?>"/>
                    </div>

                    <div class="wperp-filter-panel-footer">
                        <input type="submit" class="wperp-btn btn--cancel btn--filter" value="<?php esc_html_e( 'Cancel', 'erp' ); ?>" name="hide_filter">
                        <input type="submit" class="wperp-btn btn--reset btn--filter" value="<?php esc_html_e( 'Reset', 'erp' ); ?>" name="reset_filter_ann">
                        <input type="submit" name="filter_announcements" id="filter" class="wperp-btn btn--primary" value="<?php esc_html_e( 'Apply', 'erp' ); ?>">
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * Prepare the class items
     *
     * @since 1.9.1
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

        if ( ! empty( $_GET['ann_start_date'] ) && ! empty( $_GET['ann_end_date'] ) ) {
            $args['date_query'] = [
                'after'     => sanitize_text_field( wp_unslash( $_GET['ann_start_date'] ) ),
                'end'       => sanitize_text_field( wp_unslash( $_GET['ann_end_date'] ) ),
                'inclusive' => true,
            ];
        }

        if ( ! empty( $_GET['status'] ) ) {
            $args['post_status'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
        }

        $this->items = erp_hr_get_announcements( $args );

        $this->set_pagination_args( [
            'total_items' => erp_hr_get_announcements_count( $args ),
            'per_page'    => $per_page,
        ] );

        $this->counts = erp_hr_get_announcements_status_counts( $args );
    }

    /**
     * Set the views
     *
     * @since 1.9.1
     *
     * @return array
     */
    public function get_views() {
        $status_links   = [];
        $base_link      = admin_url( 'admin.php?page=erp-hr&section=people&sub-section=&sub-section=announcement' );

        $status = 'publish';
        if ( ! empty( $_GET['status'] ) ) {
            $status = sanitize_text_field( wp_unslash( $_GET['status'] ) );
        }
        $status_links['publish'] = sprintf( '<a href="%s" class="status-publish %s">%s <span class="count">(%s)</span></a>', $base_link, $status === 'publish' ? 'current' : '', __( 'Publish', 'erp' ), $this->counts['publish'] );
        $status_links['draft']   = sprintf( '<a href="%s" class="status-draft %s">%s <span class="count">(%s)</span></a>', add_query_arg( [ 'status' => 'draft' ], $base_link ), $status === 'draft' ? 'current' : '', __( 'Draft', 'erp' ), $this->counts['draft'] );
        $status_links['trash']   = sprintf( '<a href="%s" class="status-trash %s">%s <span class="count">(%s)</span></a>', add_query_arg( [ 'status' => 'trash' ], $base_link ), $status === 'trash' ? 'current' : '', __( 'Trash', 'erp' ), $this->counts['trash'] );

        return $status_links;
    }
}
