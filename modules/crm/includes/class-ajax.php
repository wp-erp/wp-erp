<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Ajax_Handler {

    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for CRM
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {

        // Customer
        $this->action( 'wp_ajax_erp-crm-customer-new', 'create_customer' );
        $this->action( 'wp_ajax_erp-crm-customer-get', 'customer_get' );
        $this->action( 'wp_ajax_erp-crm-customer-delete', 'customer_remove' );
        $this->action( 'wp_ajax_erp-crm-customer-restore', 'customer_restore' );
    }

    /**
     * Craete new customer
     *
     * @since 1.0
     *
     * @return json
     */
    public function create_customer() {
        $this->verify_nonce( 'wp-erp-crm-customer-nonce' );

        // @TODO: check permission
        unset( $_POST['_wp_http_referer'] );
        unset( $_POST['_wpnonce'] );
        unset( $_POST['action'] );

        $posted               = array_map( 'strip_tags_deep', $_POST );
        $posted['type']       = 'customer';
        $customer_id          = erp_insert_people( $posted );

        if ( is_wp_error( $customer_id ) ) {
            $this->send_error( $customer_id->get_error_message() );
        }

        $customer = new Customer( intval( $customer_id ) );

        if ( $posted['photo_id'] ) {
            $customer->update_meta( 'photo_id', $posted['photo_id'] );
        }

        if ( $posted['life_stage'] ) {
            $customer->update_meta( 'life_stage', $posted['life_stage'] );
        }

        $data = $customer->to_array();

        $this->send_success( $data );

    }

    /**
     * Get customer details
     *
     * @since 1.0
     *
     * @return array
     */
    public function customer_get() {

        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $customer_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $customer    = new Customer( $customer_id );

        if ( ! $customer_id || ! $customer ) {
            $this->send_error( __( 'Customer does not exists.', 'wp-erp' ) );
        }

        $this->send_success( $customer->to_array() );
    }

    /**
     * Delete customer data with meta
     *
     * @since 1.0
     *
     * @return json
     */
    public function customer_remove() {

        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $customer_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $hard        = isset( $_REQUEST['hard'] ) ? intval( $_REQUEST['hard'] ) : 0;

        if ( ! $customer_id ) {
            $this->send_error( __( 'No Customer found', 'wp-erp' ) );
        }

        erp_crm_customer_delete( $customer_id, $hard );

        // @TODO: check permission
        $this->send_success( __( 'Customer has been removed successfully', 'wp-erp' ) );
    }

    /**
     * Restore customer from trash
     *
     * @since 1.0
     *
     * @return json
     */
    public function customer_restore() {

        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $customer_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $customer_id ) {
            $this->send_error( __( 'No Customer found', 'wp-erp' ) );
        }

        erp_crm_customer_restore( $customer_id );

        // @TODO: check permission
        $this->send_success( __( 'Customer has been removed successfully', 'wp-erp' ) );

    }

}
