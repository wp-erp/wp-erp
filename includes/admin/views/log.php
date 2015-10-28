<?php
namespace WeDevs\ERP\Admin;

/**
 * List table class
 */
class Auditlog_List_Table extends \WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'audit_log',
            'plural'   => 'audit_logs',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    /**
     * Table column width css
     *
     * @return void
     */
    function table_css() {
        echo '<style type="text/css">';
        echo '.audit-log-list-table .column-name { width: 10%; }';
        echo '.audit-log-list-table .column-sections { width: 12%; }';
        echo '.audit-log-list-table .column-message { width: 45%; }';
        echo '</style>';
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'audit-log-list-table', $this->_args['plural'] );
    }

    /**
     * Message to show if no logs found
     *
     * @return void
     */
    function no_items() {
        _e( 'No logs found.', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $audit_log, $column_name ) {

        switch ( $column_name ) {
            case 'name':
                return strtoupper( $audit_log->component );

            case 'sections':
                return ucfirst( $audit_log->sub_component );
                
            case 'message':
                if ( $audit_log->changetype == 'edit' ) {
                    if ( !empty( $audit_log->old_value ) && !empty( $audit_log->new_value ) ) {
                        return sprintf( '%s <a href="#" class="erp-audit-log-view-changes erp-tips" data-id="%d" title="%s"> View Changes</a>', $audit_log->message, $audit_log->id, __( 'View what elements are changes', 'wp-erp' ) );
                    } else {
                        return $audit_log->message;
                    }
                }
                return $audit_log->message;
                
            case 'created_by':
                return $audit_log->display_name;
                
            case 'created_at':
                return erp_format_date( $audit_log->created_at );

            default:
                return isset( $audit_log->$column_name ) ? $audit_log->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'         => '',
            'name'       => __( 'Module', 'wp-erp' ),
            'sections'   => __( 'Sections', 'wp-erp' ),
            'message'    => __( 'Message', 'wp-erp' ),
            'created_by' => __( 'Created By', 'wp-erp' ),
            'created_at' => __( 'Created At', 'wp-erp' ),
        );

        return apply_filters( 'erp_hr_audit_table_cols', $columns );
    }


    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return false;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';
        $args                  = [];
        
        $total_items           = erp_log()->count( $args );
        
        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        $this->items  = erp_log()->get( $args );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );
    }

}
?>

<div class="wrap erp erp-hr-audit-log">

    <h2><?php _e( 'Audit Log', 'wp-erp' ); ?></h2>

    <div id="erp-audit-log-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-audit-log">
                <?php
                $audit_log = new \WeDevs\ERP\Admin\Auditlog_List_Table();
                $audit_log->prepare_items();
                $audit_log->views();

                $audit_log->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>