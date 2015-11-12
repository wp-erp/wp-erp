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
     * Render extra filtering option in
     * top of the table
     *
     * @since 0.1
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $modules          = erp_get_audit_log_modules();
        $sections         = erp_get_audit_log_sub_component();
        $selected_module  = ( isset( $_GET['filter_module'] ) ) ? $_GET['filter_module'] : '';
        $selected_section = ( isset( $_GET['filter_section'] ) ) ? $_GET['filter_section'] : '';
        ?>
        <div class="alignleft actions">

            <label class="screen-reader-text" for="filter_module"><?php _e( 'Filter by Module', 'wp-erp' ) ?></label>
            <select name="filter_module" id="filter_module">
                <option value=""><?php _e( '-- Select module --', 'wp-erp' ); ?></option>
                <?php foreach ( $modules as $key => $module ): ?>
                    <option value="<?php echo $module['component'] ?>" <?php selected( $selected_module, $module['component'] ); ?>><?php echo $module['component']; ?></option>
                <?php endforeach ?>
            </select>

            <label class="screen-reader-text" for="filter_section"><?php _e( 'Filter by Section', 'wp-erp' ) ?></label>
            <select name="filter_section" id="filter_section">
                <option value=""><?php _e( '-- Select Section --', 'wp-erp' ); ?></option>
                <?php foreach ( $sections as $key => $section ): ?>
                    <option value="<?php echo $section['sub_component'] ?>" <?php selected( $section['sub_component'], $selected_section ); ?>><?php echo $section['sub_component']; ?></option>
                <?php endforeach ?>
            </select>

            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_audit_log', false );
        echo '</div>';
    }

    /**
     * Render current actions
     * @return [type] [description]
     */
    public function current_action() {

        if ( isset( $_REQUEST['filter_audit_log'] ) ) {
            return 'filter_audit_log';
        }

        return parent::current_action();
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

        $per_page              = 10;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';
        $args                  = [];


        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['filter_module'] ) && !empty( $_REQUEST['filter_module'] ) ) {
            $args['component'] = $_REQUEST['filter_module'];
        }

        if ( isset( $_REQUEST['filter_section'] ) && !empty( $_REQUEST['filter_section'] ) ) {
            $args['sub_component'] = $_REQUEST['filter_section'];
        }

        $this->items  = erp_log()->get( $args );
        $total_items  = erp_log()->count( $args );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );
    }

}