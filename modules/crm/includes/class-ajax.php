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
        $customer_id          = erp_insert_people( $posted );

        if ( is_wp_error( $customer_id ) ) {
            $this->send_error( $customer_id->get_error_message() );
        }

        $customer = new Customer( intval( $customer_id ) );

        $customer->add_meta( 'photo_id', $posted['photo_id'] );
        $data     = $customer->to_array();

        $this->send_success( $data );

    }

}
