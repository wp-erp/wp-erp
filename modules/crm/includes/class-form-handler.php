<?php
namespace WeDevs\ERP\CRM;

/**
* Form request data handler class
*
* @since 1.0
*
* @package WP-ERP\CRM
*/
class Form_Handler {

    /**
     * Hook all actions
     *
     * @since 1.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'load-crm_page_erp-sales-customers', array( $this, 'customer_bulk_action') );
    }

    /**
     * Handle customer bulk action
     *
     * @since 1.0
     *
     * @return void [redirection]
     */
    public function customer_bulk_action() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] != 'erp-sales-customers' ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-customers' ) ) {
            return;
        }

        $customer_table = new \WeDevs\ERP\CRM\Customer_List_Table();
        $action         = $customer_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'customer_search' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'delete' :

                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        erp_crm_customer_delete( $_GET['customer_id'], false );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'permanent_delete' :
                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        erp_employee_delete( $_GET['customer_id'], true );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'restore' :
                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        erp_crm_customer_restore( $_GET['customer_id'] );
                    }

                    wp_redirect( $redirect );
                    exit();

                // case 'filter_employee':
                //     wp_redirect( $redirect );
                //     exit();

                default:
                    wp_redirect( $redirect );
                    exit();

            }
        }
    }

}