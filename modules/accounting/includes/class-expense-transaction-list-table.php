<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Expense_Transaction_List_Table extends Transaction_List_Table {

    function __construct() {
        parent::__construct();

         \WP_List_Table::__construct([
            'singular' => 'expense',
            'plural'   => 'expenses',
            'ajax'     => false
        ]);

        $this->type = 'expense';
        $this->slug = 'erp-accounting-expense';
    }

    /**
     * Get form types
     *
     * @return array
     */
    public function get_form_types() {
        return erp_ac_get_expense_form_types();
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
                'label' => __( 'Paid-Voucher', 'erp' ),
                'count' => isset( $counts['closed'] ) ? intval( $counts['closed'] ) : 0,
                'url'   => erp_ac_get_section_sales_url( 'closed' )
            ],

            'paid' => [
                'label' => __( 'Paid-Credit', 'erp' ),
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

    public function column_user_id( $item ) {
        $url               = admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id );
        $user_display_name = '';
        $actions           = array();
        // $actions['view']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=view&id=' . $item->id ), $item->id, __( 'View this transaction', 'erp' ), __( 'View', 'erp' ) );
        // $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $this->slug . '&action=delete&id=' . $item->id ), $item->id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );

        if ( ! $item->user_id ) {
            $user_display_name = __( '(no vendor)', 'erp' );
        } else {
            $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $item->id );
            if ( NULL === $transaction->user || empty( $transaction->user->company ) ) {
                $user_display_name = __('No Title', 'erp');
            } else {
                $user_display_name = $transaction->user->company;
            }
            //$user_display_name = ( NULL !== $transaction->user ) ? $transaction->user->company : '--';
        }

        return sprintf( '<a href="%1$s">%2$s</a> %3$s', $url, $user_display_name, $this->row_actions( $actions ) );
    }
}
