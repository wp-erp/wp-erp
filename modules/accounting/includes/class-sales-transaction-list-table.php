<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Sales_Transaction_List_Table extends Transaction_List_Table {
    private $page_status = '';

    function __construct() {
        global $page_status;
        \WP_List_Table::__construct([
            'singular' => 'sale',
            'plural'   => 'sales',
            'ajax'     => false
        ]);

        $this->type = 'sales';
        $this->slug = 'erp-accounting-sales';
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $section = isset( $_GET['section'] ) ? $_GET['section'] : false;
        $columns = array(
           // 'cb'         => '<input type="checkbox" />',
            'issue_date' => __( 'Date', 'erp' ),
            'form_type'  => __( 'Type', 'erp' ),
            'ref'        => __( 'Ref', 'erp' ),
            'user_id'    => __( 'Customer', 'erp' ),
            'due_date'   => __( 'Due Date', 'erp' ),
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
     * Count sales status
     *
     * @since  1.1.6
     *
     * @return  array
     */
    function get_counts() {
        global $wpdb;

        $cache_key   = 'erp-ac-sales-trnasction-counts-' . get_current_user_id();
        $results     = wp_cache_get( $cache_key, 'erp' );
        $type        = !empty( $_REQUEST['form_type'] ) ? $_REQUEST['form_type'] : false;
        $start       = !empty( $_GET['start_date'] ) ? $_GET['start_date'] :  date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
        $end         = !empty( $_GET['end_date'] ) ? $_GET['end_date'] : date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
        $customer_id = !empty( $_GET['customer_id'] ) ? $_GET['customer_id'] : '';

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
                            ->where(function($query) use( $customer_id ) {
                                if (!empty ( $customer_id )) {
                                    $query->where( 'user_id', $customer_id );
                                }
                            })
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
                    'awaiting_payment'  => __( 'Payment', 'erp' ),
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
     * Filters
     *
     * @param  string  $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( 'top' == $which ) {
            echo '<div class="alignleft mishu actions">';

            $type = [];

            $all_types = $this->get_form_types();
            $all_sects = $this->get_section();
            $types     = [];
            $statuses  = [];

            foreach ($all_types as $key => $type) {
                $types[ $key ] = $type['label'];
            }

            foreach ( $all_sects as $key => $status ) {
                $statuses[$key] = $status['label'];
            }

            // $selected_customer = '';

            // if ( ! empty( $_GET['customer_id'] ) ) {
            //     $user = \erp_get_people( intval( $_GET['customer_id'] ) );
            //     $selected_customer = array($user->id => $user->first_name + ' ' + $user->last_name);
            // }

            erp_html_form_input( array(
                'name'        => 'customer_id',
                'placeholder' => __( 'Select a name', 'erp' ),
                'type'        => 'select',
                'class'       => 'filter-customer',
                'id'          => 'erp-ac-select-user-for-assign-contact',
                // 'value'       => array(
                //     'id' => 2,
                //     'text' => 'Mustak Ahmed'
                // ),
                'options'     => [
                        '' => __( '&mdash; Select &mdash;', 'erp' )
                ], erp_get_peoples_array(
                        ['type' => 'customer', 'number' => '-1' ]
                    ),
                    'custom_attr' => [
                        'data-placeholder' => __( 'Search by customer', 'erp' ),
                        'data-content' => 'erp-ac-new-customer-content-pop',
                        'data-type'    => 'customer'
                    ]
                )
            );

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

            erp_html_form_input([
                'name'    => 'section',
                'type'    => 'select',
                'value'   => isset( $_REQUEST['section'] ) && !empty( $_REQUEST['section'] ) ? $_REQUEST['section'] : '',
                'options' => $statuses
            ]);

            submit_button( __( 'Filter', 'erp' ), 'button', 'submit_filter_sales', false );

            echo '</div>';
        }
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
                'label' => __( 'Closed Installments', 'erp' ),
                'count' => isset( $counts['closed'] ) ? intval( $counts['closed'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'closed' )
            ],

            'paid' => [
                'label' => __( 'Completed Invoices', 'erp' ),
                'count' => isset( $counts['paid'] ) ? intval( $counts['paid'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'paid' )
            ],

            'void' => [
                'label' => __( 'Void', 'erp' ),
                'count' => isset( $counts['void'] ) ? intval( $counts['void'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'void' )
            ]
        ];

        return $section;
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
     * Get form types
     *
     * @return array
     */
    public function get_form_types() {
        return erp_ac_get_sales_form_types();
    }
}
