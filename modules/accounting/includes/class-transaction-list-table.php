<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Transaction_List_Table extends \WP_List_Table {

    protected $type = '';
    protected $slug = '';

    function __construct() {
        parent::__construct( array(
            'singular' => 'transaction',
            'plural'   => 'transactions',
            'ajax'     => false
        ) );

    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No transaction found!', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'issue_date':
                return erp_format_date( $item->issue_date );

            case 'due_date':
                return  ( strtotime( $item->due_date ) > 0 )  ? erp_format_date( $item->due_date ) : '&mdash;';

            case 'form_type':
                return sprintf('<a href="#" data-transaction_id="%1$s" class="erp-ac-transaction-report">%2$s</a>', $item->id, str_replace( '_', ' ', $item->form_type ) );

            case 'user_id':
                return $item->user_id;

            case 'due':
                return number_format_i18n( $item->due, 2 );

            case 'total':
                return number_format_i18n( $item->total, 2 );

            case 'status':
                return erp_ac_get_status_label( $item, $this->slug );

            default:
                return isset( $item->$column_name ) && !empty( $item->$column_name ) ? $item->$column_name : '&mdash;';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $section = isset( $_GET['section'] ) ? $_GET['section'] : false;
        $columns = array(
            //'cb'         => '<input type="checkbox" />',
            'issue_date' => __( 'Date', 'erp' ),
            'due_date'   => __( 'Due Date', 'erp' ),
            'form_type'  => __( 'Type', 'erp' ),
            'user_id'    => __( 'Vendor', 'erp' ),
            'ref'        => __( 'Ref', 'erp' ),
            'due'        => __( 'Due', 'erp' ),
            'total'      => __( 'Total', 'erp' ),
            'status'     => __( 'Status', 'erp' ),
        );

        if ( $section == 'awaiting-approval' || $section == 'draft' || $section == 'awaiting-payment' || $section == 'closed' || $section == 'void' || $section == 'paid' || $section == 'partial' ) {
            $action = [ 'cb' => '<input type="checkbox" />'];
            $columns = array_merge( $action, $columns );
        }

        return $columns;
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'issue_date' => array( 'issue_date', true ),
        );

        return $sortable_columns;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="transaction_id[]" value="%d" />', $item->id
        );
    }

    public function column_user_id( $item ) {
        $url               = admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id );
        $user_display_name = '';
        $actions           = array();
        if ( ! $item->user_id ) {
            $user_display_name = __( '(no vendor)', 'erp' );
        } else {
            $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $item->id );
            $user_display_name = ( NULL !== $transaction->user ) ? $transaction->user->first_name . ' ' . $transaction->user->last_name : '--';
        }

        return sprintf( '<a href="%1$s">%2$s</a> %3$s', $url, $user_display_name, $this->row_actions( $actions ) );
    }

    public function column_total( $item ) {
        return erp_ac_get_price( $item->total );
    }

    public function column_due( $item ) {
        return erp_ac_get_price( $item->due );
    }

    /**
     * Render the issue date column
     *
     * @since  1.1.6
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_issue_date( $item ) {
        if ( $item->status == 'draft' ) {
            $actions['approval'] = sprintf( '<a class="erp-accountin-trns-row-bulk-action" data-status="%1s" data-id="%2d" href="#">%4s</a>', 'awaiting_approval', $item->id, __( 'Submit for approval', 'erp' ) );
        }

        if ( $item->status == 'awaiting_approval' ) {
            $actions['payment'] = sprintf( '<a class="erp-accountin-trns-row-bulk-action" data-id="%1$s" data-status="%2$s" href="#">%3$s</a>', $item->id, 'awaiting_payment', __( 'Approve', 'erp' ) );
        }

        if ( $item->status == 'awaiting_payment' || $item->status == 'partial' ) {
            $url = $this->slug == 'erp-accounting-expense' ? erp_ac_get_vendor_credit_payment_url( $item->id ) : erp_ac_get_slaes_payment_url( $item->id );
            $actions['paid'] = sprintf( '<a href="%1$s">%2$s</a>', $url, __( 'Receive Payment', 'erp' ) );
        }

        if ( $item->status == 'awaiting_approval' || $item->status == 'awaiting_payment' || $item->status == 'closed' || $item->status == 'partial' || $item->status == 'paid' ) {
            $actions['void'] = sprintf( '<a class="erp-accountin-trns-row-bulk-action" data-id="%1$s" data-status="%2$s" href="#">%3$s</a>', $item->id, 'void', __( 'Void', 'erp' ) );
        }

        if ( $item->status == 'void' ) {
            //$actions['draft'] = sprintf( '<a class="erp-accountin-trns-row-bulk-action" data-id="%1$s" data-status="%2$s" href="#">%3$s</a>', $item->id, 'draft', __( 'Draft', 'erp' ) );
        }

        if ( $item->status == 'pending' || $item->status == 'draft' || $item->status == 'awaiting_payment' || $item->status == 'awaiting_approval' ) {
            $url   = admin_url( 'admin.php?page='.$this->slug.'&action=new&type=' . $item->form_type . '&transaction_id=' . $item->id );
            $actions['edit'] = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Edit', 'erp' ) );
        }

        if ( $item->status == 'draft' || $item->status == 'void' ) {
            $actions['delete'] = sprintf( '<a href="#" class="erp-accountin-trns-row-bulk-action" data-status="%s" data-id="%d" title="%s">%s</a>', 'delete', $item->id, __( 'Delete', 'erp' ), __( 'Delete', 'erp' ) );
        }

        if ( isset( $actions ) && count( $actions ) ) {
            return sprintf( '<a href="%1$s">%2$s</a> %3$s', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ), $this->row_actions( $actions ) );
        } else {
            return sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ) );
        }
    }

    /**
     * Field for bulk action
     *
     * @since  1.1.6
     *
     * @return void
     */
    public function bulk_actions( $which = '' ) {
        $section = isset( $_GET['section'] ) ? $_GET['section'] : false;
        $type    = [];

        if ( 'top' == $which && $this->items ) {
            if ( $section == 'draft' ) {
                $type = [
                    'awaiting_approval'  => __( 'Approve', 'erp' ),
                    'delete' => __( 'Delete', 'erp' )
                ];
            } else if ( $section == 'awaiting-payment' ) {
                $type = [
                    'void'  => __( 'Void', 'erp' ),
                ];
            } else if ( $section == 'closed' ) {
                $type = [
                    'void'  => __( 'Void', 'erp' ),
                ];
            } else if ( $section == 'void' ) {
                $type = [
                    'delete'  => __( 'Delete', 'erp' ),
                ];
            } else if ( $section == 'awaiting-approval' ) {
                $type = [
                    'awaiting_payment'  => __( 'Approve', 'erp' ),
                    'void'  => __( 'Void', 'erp' ),
                ];
            } else if ( $section == 'paid' ) {
                $type = [
                    'void'  => __( 'Void', 'erp' ),
                ];
            } else if ( $section == 'partial' ) {
                $type = [
                    'void'  => __( 'Void', 'erp' ),
                ];
            }

            if ( $section ) {
                erp_html_form_input([
                    'name'    => 'action',
                    'type'    => 'select',
                    'options' => [ '-1' => __( 'Bulk Actions', 'erp' ) ] + $type
                ]);

                submit_button( __( 'Action', 'erp' ), 'button', 'submit_sales_bulk_action', false );
            }

        }
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $counts       = $this->get_section();
        $status_links = array();
        $section      = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : 'all';

        foreach ( $counts as $key => $value ) {
            $key   = str_replace( '_', '-', $key );
            $class = ( $key == $section ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', $value['url'], $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }


    /**
     * Count sales status
     *
     * @since  1.1.6
     *
     * @return  array
     */
    function get_counts() {
        global $wpdb;
        $cache_key = 'erp-ac-sales-trnasction-counts-' . get_current_user_id();
        $results = wp_cache_get( $cache_key, 'erp' );
        $type = isset( $_REQUEST['form_type'] ) ? $_REQUEST['form_type'] : false;
        $start = isset( $_GET['start_date'] ) ? $_GET['start_date'] :  date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
        $end = isset( $_GET['end_date'] ) ? $_GET['end_date'] : date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

        if ( false === $results ) {
            $trans = new \WeDevs\ERP\Accounting\Model\Transaction();
            $db = new \WeDevs\ORM\Eloquent\Database();

            if ( $type ) {
                $results = $trans->select( array( 'status', $db->raw('COUNT(id) as num') ) )
                            ->where( 'type', '=', $this->type )
                            ->where( 'form_type', '=', $type )
                            ->where( 'issue_date', '>=', $start )
                            ->where( 'issue_date', '<=', $end )
                            ->groupBy('status')
                            ->get()->toArray();
            } else {
                $results = $trans->select( array( 'status', $db->raw('COUNT(id) as num') ) )
                            ->where( 'type', '=', $this->type )
                            ->where( 'issue_date', '>=', $start )
                            ->where( 'issue_date', '<=', $end )
                            ->groupBy('status')
                            ->get()->toArray();
            }

            wp_cache_set( $cache_key, $results, 'erp' );
        }

        $count = [];

        foreach ( $results as $key => $value ) {
            $count[$value['status']] = $value['num'];
        }

        return $count;
    }

    /**
     * Get section for sales table list
     *
     * @since  1.1.6
     *
     * @return array
     */
    public function get_section() {
        $counts = $this->get_counts();

        $section = [
            'all'   => [
                'label' => __( 'All', 'erp' ),
                'count' => array_sum( $counts),
                'url'   => erp_ac_get_section_sales_url()
            ],

            'draft' => [
                'label' => __( 'Draft', 'erp' ),
                'count' => isset( $counts['draft'] ) ? intval( $counts['draft'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'draft' )
            ],

            'awaiting_approval' => [
                'label' => __( 'Awaiting Approval', 'erp' ),
                'count' => isset( $counts['awaiting_approval'] ) ? intval( $counts['awaiting_approval'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'awaiting_approval' )
            ],

            'awaiting_payment' => [
                'label' => __( 'Awaiting Payment', 'erp' ),
                'count' => isset( $counts['awaiting_payment'] ) ? intval( $counts['awaiting_payment'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'awaiting_payment' )
            ],

            'partial' => [
                'label' => __( 'Partial', 'erp' ),
                'count' => isset( $counts['partial'] ) ? intval( $counts['partial'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'partial' )
            ],

            'closed' => [
                'label' => __( 'Paid', 'erp' ),
                'count' => isset( $counts['closed'] ) ? intval( $counts['closed'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'closed' )
            ],

            'void' => [
                'label' => __( 'Void', 'erp' ),
                'count' => isset( $counts['void'] ) ? intval( $counts['void'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'void' )
            ]
        ];

        return $section;
    }

    public function get_form_types() {
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

            $all_types = $this->get_form_types();
            $types = [];

            foreach ($all_types as $key => $type) {
                $types[ $key ] = $type['label'];
            }

            erp_html_form_input([
                'name'    => 'form_type',
                'type'    => 'select',
                'value'   => isset( $_REQUEST['form_type'] ) && ! empty( $_REQUEST['form_type'] ) ? strtolower( $_REQUEST['form_type'] ) : '',
                'options' => [ '' => __( 'All Types', 'erp' ) ] + $types
            ]);

            erp_html_form_input([
                'name'        => 'user_id',
                'type'        => 'hidden',
                'class'       => 'erp-ac-customer-search',
                'placeholder' => __( 'Search for Customer', 'erp' ),
            ]);

            erp_html_form_input([
                'name'        => 'start_date',
                'class'       => 'erp-date-field',
                'value'       => isset( $_REQUEST['start_date'] ) && !empty( $_REQUEST['start_date'] ) ? $_REQUEST['start_date'] : '',
                'placeholder' => __( 'Start Date', 'erp' )
            ]);

            erp_html_form_input([
                'name'        => 'end_date',
                'class'       => 'erp-date-field',
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
     * Get all transactions
     *
     * @param  array  $args
     *
     * @return array
     */
    protected function get_transactions( $args ) {
        return erp_ac_get_all_transaction( $args );
    }

    /**
     * Get transaction count
     *
     * @param  array  $args
     *
     * @return int
     */
    protected function get_transaction_count( $args ) {
        return erp_ac_get_transaction_count( $args );
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
        if ( isset( $_REQUEST['customer_id'] ) && !empty( $_REQUEST['customer_id'] ) ) {
            $args['user_id'] = $_REQUEST['customer_id'];
         }

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

        $this->items = $this->get_transactions( $args );

        $this->set_pagination_args( array(
            'total_items' => $this->get_transaction_count( $args ),
            'per_page'    => $per_page
        ) );
    }
}
