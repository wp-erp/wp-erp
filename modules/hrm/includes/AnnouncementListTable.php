<?php

namespace WeDevs\ERP\HRM;

use WP_List_Table;

/**
 *  List table class
 */
class AnnouncementListTable extends WP_List_Table {
    private $counts = [];

    public function __construct() {
        parent::__construct( [
            'singuler' => 'announcement',
            'plural'   => 'announcements',
            'ajax'     => true,
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
            'cb'      => '<input type="checkbox" />',
            'title'   => __( 'Title', 'erp' ),
            'type'    => __( 'Type', 'erp' ),
            'sent_to' => __( 'Sent to', 'erp' ),
            'date'    => __( 'Date', 'erp' ),
        ];

        return $columns;
    }

    /**
     * Default column values if no callback found
     *
     * @since 1.10.0
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
                $type = get_post_meta( $item->ID, '_announcement_type', true );

                if ( starts_with( $type, 'by_' ) ) {
                    $type = substr( $type, 3 );
                } else {
                    $type = implode( ' ', explode( '_', $type ) );
                    $type .= empty( $type ) ? '' : 's';
                }

                $type = ucfirst( $type );
                return ( empty( $type ) ? '&mdash;' : esc_html( $type ) );

            case 'date':
                return erp_format_date( $item->post_date );

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Render checkbox column
     *
     * @since 1.10.0
     *
     * @param Object $item
     *
     * @return void
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="id[]" value="%d" />', $item->ID );
    }

    /**
     * Render the name column
     *
     * @since 1.10.0
     *
     * @param Object $item
     *
     * @return void
     */
    public function column_title( $item ) {
        $is_trash = ( ! empty( $_GET['status'] ) && sanitize_text_field( wp_unslash( $_GET['status'] ) ) === 'trash' );
        $actions  = [];
        $params   = [
            'post' => $item->ID,
        ];

        if ( $is_trash ) {
            $params['action']   = 'untrash';
            $params['_wpnonce'] = wp_create_nonce( 'untrash-post_' . $item->ID );
            $restore_url        = add_query_arg( $params, admin_url( 'post.php' ) );

            $params['action']   = 'delete';
            $params['_wpnonce'] = wp_create_nonce( 'delete-post_' . $item->ID );
            $delete_url         = add_query_arg( $params, admin_url( 'post.php' ) );

            $actions['untrash'] = sprintf( '<a href="%s" data-id=%d title="%s">%s</a>', $restore_url, $item->ID, esc_html__( 'Restore this announcement', 'erp' ), esc_html__( 'Restore', 'erp' ) );
            $actions['delete']  = sprintf( '<a href="%s" data-id=%d title="%s">%s</a>', $delete_url, $item->ID, esc_html__( 'Delete this announcement permanently', 'erp' ), esc_html__( 'Delete Permanently', 'erp' ) );
        } else {
            $params['action'] = 'edit';
            $edit_url         = add_query_arg( $params, admin_url( 'post.php' ) );

            $params['action']   = 'trash';
            $params['_wpnonce'] = wp_create_nonce( 'trash-post_' . $item->ID );
            $trash_url          = add_query_arg( $params, admin_url( 'post.php' ) );

            $actions['edit']  = sprintf( '<a href="%s" data-id=%d title="%s">%s</a>', $edit_url, $item->ID, esc_html__( 'Edit this announcement', 'erp' ), esc_html__( 'Edit', 'erp' ) );
            $actions['trash'] = sprintf( '<a href="%s" data-id=%d title="%s">%s</a>', $trash_url, $item->ID, esc_html__( 'Trash this announcement', 'erp' ), esc_html__( 'Trash', 'erp' ) );
        }

        return sprintf( '<strong>%s</strong> %s', esc_html( $item->post_title ), $this->row_actions( $actions ) );
    }

    /**
     * Render Sent To Column
     *
     * @since 1.10.0
     *
     * @param Object $item
     *
     * @return void
     */
    public function column_sent_to( $item ) {
        $type    = get_post_meta( $item->ID, '_announcement_type', true );
        $sent_to = [];

        if ( $type === 'by_department' ) {
            $sent_to = get_post_meta( $item->ID, '_announcement_department', true );
            $sent_to = array_map( function( $id ) {
                $department = \WeDevs\ERP\HRM\Models\Department::find( (int) $id );

                if ( $department ) {
                    return $department->title;
                }
            }, $sent_to );
        } elseif ( $type === 'by_designation' ) {
            $sent_to = get_post_meta( $item->ID, '_announcement_designation', true );
            $sent_to = array_map( function( $id ) {
                $designation = \WeDevs\ERP\HRM\Models\Designation::find( (int) $id );

                if ( $designation ) {
                    return $designation->title;
                }
            }, $sent_to );
        } else {
            $sent_to = get_post_meta( $item->ID, '_announcement_selected_user', true );
            $sent_to = array_map( function( $id ) {
                $user = get_user_by( 'id', $id );

                if ( $user ) {
                    return $user->display_name;
                }
            }, $sent_to );
        }

        //prepare modal for large number of text data in a cell
        $cell_content     = count( $sent_to ) === 0 ? '&mdash;' : implode( ', ', $sent_to );
        $cell_content     = $type === 'all_employee' ? esc_html__( 'All employees', 'erp' ) : $cell_content;
        $threshold_length = 65;

        if ( strlen( $cell_content ) < $threshold_length ) {
            return $cell_content;
        } else {
            $list_content = '<ul style="list-style: square;">'; //this list will be shown in the modal popup

            foreach ( $sent_to as $sent ) {
                $escaped = esc_html( $sent );
                $list_content .= "<li>$escaped</li>";
            }

            $list_content .= '</ul>';

            return substr( $cell_content, 0, $threshold_length ) . "<span style='cursor: pointer;' title='" . esc_attr__( 'Show More', 'erp' ) . "' data-more-content='$list_content' class='expand-for-more-ann'> (" . esc_html__( 'more', 'erp' ) . ')</button>';
        }
    }

    /**
     * Get bulk actions for the table
     *
     * @since 1.10.0
     *
     * @return void
     */
    public function get_bulk_actions() {
        $actions = [
            'trash' => __( 'Move to trash', 'erp' ),
        ];

        if ( ! empty( $_GET['status'] ) && sanitize_text_field( wp_unslash( $_GET['status'] ) ) === 'trash' ) {
            unset( $actions['trash'] );
            $actions['delete_permanently'] = __( 'Delete Parmanently', 'erp' );
            $actions['restore']            = __( 'Restore', 'erp' );
        }

        return $actions;
    }

    /**
     * Extra filters for the list table
     *
     * @since 1.10.0
     *
     * @param string $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $start_date = ( ! empty( $_GET['start_date'] ) ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
        $end_date   = ( ! empty( $_GET['end_date'] ) ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
        $status     = ( ! empty( $_GET['status'] ) ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
        ?>
        <div class="wperp-filter-dropdown" style="margin: -46px 0 0 0;">
            <a class="wperp-btn btn--default"><span class="dashicons dashicons-filter"></span><?php esc_html_e( 'Filters', 'erp' ); ?><span class="dashicons dashicons-arrow-down-alt2"></span></a>

            <div class="erp-dropdown-filter-content" id="erp-dropdown-content">
                <div class="wperp-filter-panel wperp-filter-panel-default">
                    <h3><?php esc_html_e( 'Filter', 'erp' ); ?></h3>
                    <div class="wperp-filter-panel-body">
                        <label class="screen-reader-text" for="start_date"><?php esc_html_e( 'Start Date', 'erp' ); ?></label>
                        <input autocomplete="off" class="erp-date-field" name="start_date" id="start_date" value="<?php echo esc_attr( $start_date ); ?>"
                            placeholder="<?php esc_attr_e( 'Select Start Date', 'erp' ); ?>" />

                        <label class="screen-reader-text" for="end_date"><?php esc_html_e( 'End Date', 'erp' ); ?></label>
                        <input autocomplete="off" class="erp-date-field" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date ); ?>"
                            placeholder="<?php esc_attr_e( 'Select End Date', 'erp' ); ?>" />

                        <input hidden name="status" value="<?php echo esc_attr( $status ); ?>"/>
                    </div>

                    <div class="wperp-filter-panel-footer">
                        <input type="submit" class="wperp-btn btn--cancel btn--filter" value="<?php esc_attr_e( 'Cancel', 'erp' ); ?>" name="hide_filter">
                        <input type="submit" class="wperp-btn btn--reset btn--filter" value="<?php esc_attr_e( 'Reset', 'erp' ); ?>" name="reset_announcement_filter">
                        <input type="submit" name="filter_announcements" id="filter" class="wperp-btn btn--primary" value="<?php esc_attr_e( 'Apply', 'erp' ); ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Prepare the class items
     *
     * @since 1.10.0
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

        $args = [
            'offset'      => $offset,
            'numberposts' => $per_page,
        ];

        if ( ! empty( $_GET['start_date'] ) && ! empty( $_GET['end_date'] ) ) {
            $args['date_query'] = [
                'after'     => sanitize_text_field( wp_unslash( $_GET['start_date'] ) ),
                'before'    => sanitize_text_field( wp_unslash( $_GET['end_date'] ) ),
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

        $this->counts = erp_hr_get_announcements_status_counts();
    }

    /**
     * Set the views
     *
     * @since 1.10.0
     *
     * @return array
     */
    public function get_views() {
        $status_links = [];
        $base_link    = admin_url( 'admin.php?page=erp-hr&section=people&sub-section=announcement' );
        $status       = 'publish';

        if ( ! empty( $_GET['status'] ) ) {
            $status = sanitize_text_field( wp_unslash( $_GET['status'] ) );
        }

        $status_links['publish'] = sprintf( '<a href="%s" class="status-publish %s">%s <span class="count">(%s)</span></a>', $base_link, $status === 'publish' ? 'current' : '', __( 'Publish', 'erp' ), $this->counts['publish'] );
        $status_links['draft']   = sprintf( '<a href="%s" class="status-draft %s">%s <span class="count">(%s)</span></a>', add_query_arg( [ 'status' => 'draft' ], $base_link ), $status === 'draft' ? 'current' : '', __( 'Draft', 'erp' ), $this->counts['draft'] );
        $status_links['trash']   = sprintf( '<a href="%s" class="status-trash %s">%s <span class="count">(%s)</span></a>', add_query_arg( [ 'status' => 'trash' ], $base_link ), $status === 'trash' ? 'current' : '', __( 'Trash', 'erp' ), $this->counts['trash'] );

        return $status_links;
    }
}
