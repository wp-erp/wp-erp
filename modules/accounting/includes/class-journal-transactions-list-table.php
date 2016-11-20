<?php

namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Journal_Transactions_List_Table extends Transaction_List_Table {
    public $type_id = [];
    public $chart_group  = [];
    public $account_prev_balance = 0;

    function __construct() {

        $this->type = 'journal';
        $this->slug = 'erp-accounting-journal';

        parent::__construct();

        \WP_List_Table::__construct([
            'singular' => 'journal',
            'plural'   => 'journals',
            'ajax'     => false
        ]);

    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $ledger_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : false;
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'issue_date' => __( 'Date', 'erp' ),
            'ref'        => __( 'Ref', 'erp' ),
            'summary'    => __( 'Summary', 'erp' ),
            'total'      => __( 'Total', 'erp' )
        );

        if ( $ledger_id ) {
            $columns['balance'] = __( 'Balance', 'erp' );
        }

        return $columns;
    }

    /**
     * Balance
     *
     * @param  array $item
     *
     * @return string
     */
    function column_balance( $item ) {
        $balance = 0;

        // if ( in_array( $this->type_id, $this->chart_group['customer'] ) ) {
        //     $balance =  ( $item->debit + $this->customer_prev_balance ) - $item->credit;
        //     $this->customer_prev_balance = $balance;
        // }

        // if ( in_array( $this->type_id, $this->chart_group['vendor'] ) ) {
        //     $balance =  ( $item->credit + $this->vendor_prev_balance ) - $item->debit;
        //     $this->vendor_prev_balance = $balance;
        // }

        $balance =  ( $item->debit + $this->account_prev_balance ) - $item->credit;
        $this->account_prev_balance = $balance;

        return erp_ac_get_price( $balance );
    }

    /**
     * Get section for sales table list
     *
     * @since  1.1.6
     *
     * @return array
     */
    public function get_section() {
        return [];
    }

    /**
     * Filters
     *
     * @param  string  $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            echo '<div class="alignleft actions">';
            erp_html_form_input([
                'name'        => 'user_id',
                'type'        => 'hidden',
                'class'       => 'erp-ac-customer-search',
                'placeholder' => __( 'Search for Customer', 'erp' ),
            ]);

            erp_html_form_input([
                'name'        => 'start_date',
                'class'       => 'erp-date-picker-from',
                'value'       => isset( $_REQUEST['start_date'] ) && !empty( $_REQUEST['start_date'] ) ? $_REQUEST['start_date'] : '',
                'placeholder' => __( 'Start Date', 'erp' )
            ]);

            erp_html_form_input([
                'name'        => 'end_date',
                'class'       => 'erp-date-picker-to',
                'value'       => isset( $_REQUEST['end_date'] ) && !empty( $_REQUEST['end_date'] ) ? $_REQUEST['end_date'] : '',
                'placeholder' => __( 'End Date', 'erp' )
            ]);

            erp_html_form_input([
                'name'        => 'ref',
                'value'       => isset( $_REQUEST['ref'] ) && ! empty( $_REQUEST['ref'] ) ? $_REQUEST['ref'] : '',
                'placeholder' => __( 'Ref No.', 'erp' )
            ]);

            submit_button( __( 'Filter', 'erp' ), 'button', 'submit_filter_sales', false );

            echo '</div>';
        }
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {
        $ledger_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : false;
        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 25;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'type'   => $this->type,
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        // search params
        if ( isset( $_REQUEST['start_date'] ) && !empty( $_REQUEST['start_date'] ) ) {
            $args['start_date'] = $_REQUEST['start_date'];
        }

        if ( isset( $_REQUEST['end_date'] ) && !empty( $_REQUEST['end_date'] ) ) {
            $args['end_date'] = $_REQUEST['end_date'];
        }

        if ( isset( $_REQUEST['form_type'] ) && ! empty( $_REQUEST['form_type'] ) ) {
            if ( $_REQUEST['form_type'] == 'deleted' ) {
                $args['status'] = $_REQUEST['form_type'];
            } else {
                $args['form_type'] = $_REQUEST['form_type'];
            }
        }

        if ( isset( $_REQUEST['ref'] ) && ! empty( $_REQUEST['ref'] ) ) {
            $args['ref'] = $_REQUEST['ref'];
        }

        if ( 'sales' == $args['type'] && ! erp_ac_view_other_sales() ) {
            $args['created_by'] = get_current_user_id();
        }

        if ( isset( $_REQUEST['section'] ) ) {
            $args['status']  = str_replace('-', '_', $_REQUEST['section'] );
        }

        if ( 'expense' == $args['type'] && ! erp_ac_view_other_expenses() ) {
            $args['created_by'] = get_current_user_id();
        }

        if ( 'journal' == $args['type'] && ! erp_ac_view_other_journals() ) {
            $args['created_by'] = get_current_user_id();
        }

        if ( $ledger_id ) {
            $this->ledger_id = true;
            // $this->chart_group = erp_ac_chart_grouping();
            // var_dump( $this->chart_group ); die();
            // $individual_ledger = \WeDevs\ERP\Accounting\Model\Ledger::select('type_id')->find( $ledger_id );
            // $this->type_id     = isset( $individual_ledger->type_id ) ? $individual_ledger->type_id : false;

            $this->items = erp_ac_get_ledger_transactions( $args, $ledger_id );
        } else {
            $this->items = $this->get_transactions( $args );
        }

        $this->set_pagination_args( array(
            'total_items' => $this->get_transaction_count( $args ),
            'per_page'    => $per_page
        ) );
    }
}
