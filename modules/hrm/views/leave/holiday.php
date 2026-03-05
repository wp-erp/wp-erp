<?php
/**
 * List table class
 */
class Leave_Holiday_List_Table extends WP_List_Table {

    protected $page_status;
    public function __construct() {
        global $status, $page;

        parent::__construct( [
            'singular' => 'holiday',
            'plural'   => 'holiday',
            'ajax'     => false,
        ] );
    }

    /**
     * Get table classes
     *
     * @return array
     */
    public function get_table_classes() {
        return [ 'widefat', 'fixed', 'striped', 'erp-leave-policy-list-table', $this->_args['plural'] ];
    }

    public function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $from         = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : gmdate( 'Y-01-01' );
        $to           = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : gmdate( 'Y-12-31' ); ?>

        <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'From', 'erp' ); ?></label>
        <input type="text" placeholder="<?php esc_attr_e( 'From date', 'erp' ); ?>" name="from" value="<?php echo esc_attr( $from ); ?>" class="erp-leave-date-picker-from">

        <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'To', 'erp' ); ?></label>
        <input type="text" placeholder="<?php esc_attr_e( 'To date', 'erp' ); ?>" name="to" value="<?php echo esc_attr( $to ); ?>" class="erp-leave-date-picker-to">
        <?php
        submit_button( __( 'Filter', 'erp' ), 'button', 'filter', false );
    }

    /**
     * Message to show if no policy found
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No holiday record found!', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $holiday, $column_name ) {
        switch ( $column_name ) {
            case 'name':
                return $holiday->title;

            case 'start':
                return erp_format_date( $holiday->start );

            case 'end':
                return erp_format_date( gmdate( 'Y-m-d', strtotime( $holiday->end ) ) );

            case 'duration':

                $days = erp_date_duration( $holiday->start, $holiday->end );

                return $days . ' ' . _n( 'day', 'days', $days, 'erp' );

            case 'description':
                return ! empty( $holiday->description ) ? $holiday->description : '--';
            default:
                return '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'name'        => __( 'Title', 'erp' ),
            'start'       => __( 'Start Date', 'erp' ),
            'end'         => __( 'End Date', 'erp' ),
            'duration'    => __( 'Duration', 'erp' ),
            'description' => __( 'Description', 'erp' ),
        ];

        return apply_filters( 'erp_hr_holiday_table_cols', $columns );
    }

    /**
     * Render the leave policy name column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_name( $holiday ) {
        $actions           = [];
        $delete_url        = '';
        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" class="erp-hr-holiday-edit" title="%s">%s</a>', $delete_url, $holiday->id, __( 'Edit this item', 'erp' ), __( 'Edit', 'erp' ) );
        $actions['delete'] = sprintf( '<a href="%s" class="erp-hr-holiday-delete" data-id="%d" title="%s">%s</a>', $delete_url, $holiday->id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );

        return sprintf( '<a href="#" class="link" data-id="%3$s"><strong>%1$s</strong></a> %2$s', esc_html( $holiday->title ), $this->row_actions( $actions ), $holiday->id );
    }

    /**
     * Modify single row element
     *
     * @param array $item
     *
     * @return void
     */
    public function single_row( $item ) {
        ?>
            <tr data-json='<?php echo wp_json_encode( $item ); ?>'>
                <?php $this->single_row_columns( $item ); ?>
            </tr>
        <?php
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = [
            'title' => [ 'title', true ],
            'start' => [ 'start', true ],
        ];

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'trash'  => __( 'Delete', 'erp' ),
        ];

        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="holiday_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {
        $status_links   = [];
        $base_link      = admin_url( 'admin.php?page=erp-hr&section=leave' );

        foreach ( $this->counts as $key => $value ) {
            $class                = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( [ 'status' => $key ], $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

        // only ncessary because we have sample data
        $args = [
            'offset'  => $offset,
            'number'  => $per_page,
            'orderby' => 'start',
            'order'   => 'ASC',
        ];

        if ( ! empty( $_GET['s'] ) ) {
            $args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
        }

        if ( isset( $_GET['from'] ) && $_GET['from'] != '' ) {
            $args['from'] = gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_GET['from'] ) ) ) );
        } else {
            $args['from'] = gmdate( 'Y-01-01' );
        }

        if ( isset( $_GET['to'] ) && $_GET['to'] != '' ) {
            $args['to'] = gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_GET['to'] ) ) . '+1day' ) );
        } else {
            $args['to'] = gmdate( 'Y-12-31' );
        }

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
            $args['order']   = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
        }

        $this->items  = erp_hr_get_holidays( $args );

        $this->set_pagination_args( [
            'total_items' => erp_hr_count_holidays( $args ),
            'per_page'    => $per_page,
        ] );
    }
}

?>

<div class="wrap erp-hr-holiday-wrap">

    <div id="holiday_msg"></div>

    <h2>
        <?php esc_html_e( 'Holiday', 'erp' ); ?>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo erp_help_tip( esc_html__( 'Add holidays for current year.', 'erp' ) ); ?>
        <a href="#" id="erp-hr-new-holiday" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
        <a href="#" id="erp-hr-import-holiday" class="add-new-h2"><?php esc_html_e( 'Import iCal / CSV', 'erp' ); ?></a>
    </h2>

    <div class="list-table-wrap">
        <div class="list-wrap-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="leave">
                <input type="hidden" name="sub-section" value="holidays">
                <?php

                $holiday = new Leave_Holiday_List_Table();
                $holiday->prepare_items();
                $holiday->search_box( __( 'Search Holiday', 'erp' ), 'erp-hr-holiday-serach' );

                $holiday->views();

                $holiday->display();
                ?>
            </form>

            <form method="post" enctype="multipart/form-data" style="position: absolute; visibility: hidden;">
                <input type="file" id="erp-ical-input">
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>

<style>
    .erp-help-tip {
        width: 15px;
        bottom: 0.2rem;
    }
</style>
