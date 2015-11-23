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

        $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'customer_search' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        wp_redirect( $redirect );
        exit();
    }

}