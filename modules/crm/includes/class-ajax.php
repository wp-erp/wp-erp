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

        $this->action( 'wp_ajax_erp-crm-customer-add-company', 'customer_add_company' );
        $this->action( 'wp_ajax_erp-crm-customer-edit-company', 'customer_edit_company' );
        $this->action( 'wp_ajax_erp-crm-customer-update-company', 'customer_update_company' );
        $this->action( 'wp_ajax_erp-crm-customer-remove-company', 'customer_remove_company' );

        // script reload
        $this->action( 'wp_ajax_erp-crm-customer-company-reload', 'customer_company_template_refresh' );

        // Single customer view
        $this->action( 'wp_ajax_erp-crm-customer-social', 'customer_social_profile' );
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

    /**
     * Adds compnay to custmer individual profile
     *
     * @since 1.0
     *
     * @return
     */
    public function customer_add_company() {

        $this->verify_nonce( 'wp-erp-crm-assign-customer-company-nonce' );

        $customer_id = isset( $_REQUEST['customer_id'] ) ? intval( $_REQUEST['customer_id'] ) : 0;
        $company_id = isset( $_REQUEST['erp_assign_company_id'] ) ? intval( $_REQUEST['erp_assign_company_id'] ) : 0;


        if ( ! $customer_id ) {

            $this->send_error( __( 'No Customer found', 'wp-erp' ) );
        }

        erp_crm_customer_add_company( $customer_id, $company_id );

        $this->send_success( __( 'Company has been added successfully', 'wp-erp' ) );

    }

    /**
     * Get data for Company edit field for customer
     */
    public function customer_edit_company() {

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        $result = erp_crm_customer_company_by_id( $query_id );

        $this->send_success( $result );
    }

    /**
     * Save Company edit field for customer
     */
    public function customer_update_company() {

        $this->verify_nonce( 'wp-erp-crm-customer-update-company-nonce' );

        $row_id = isset( $_REQUEST['row_id'] ) ? intval( $_REQUEST['row_id'] ) : 0;
        $company_id = isset( $_REQUEST['company_id'] ) ? intval( $_REQUEST['company_id'] ) : 0;

        $result = erp_crm_customer_update_company( $row_id, $company_id );

        $this->send_success( __( 'Company has been updated successfully', 'wp-erp' ) );

    }

    /**
     * Remove Company from Customer Single Profile
     */
    public function customer_remove_company() {

        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if( $id ) {
            erp_crm_customer_remove_company( $id );
        }

        $this->send_success('hello');

    }

    /**
     * Customer add company template refresh
     *
     * @since  1.0
     *
     * @return void
     */
    public function customer_company_template_refresh() {
        ob_start();
        include WPERP_CRM_JS_TMPL . '/new-assign-company.php';
        $this->send_success( array( 'cont' => ob_get_clean() ) );
    }

    /**
     * Set customer social profile info
     *
     * @since 1.0
     *
     * @return void
     */
    public function customer_social_profile() {
        $this->verify_nonce( 'wp-erp-crm-customer-social-nonce' );

        // @TODO: check permission
        unset( $_POST['_wp_http_referer'] );
        unset( $_POST['_wpnonce'] );
        unset( $_POST['action'] );

        if ( ! $_POST['customer_id'] ) {
            $this->send_error( __( 'No customer found', 'wp-erp' ) );
        }

        $customer_id = (int) $_POST['customer_id'];
        unset( $_POST['customer_id'] );

        $customer = new \WeDevs\ERP\CRM\Customer( $customer_id );
        $customer->update_meta( 'crm_social_profile', $_POST );

        $this->send_success( __( 'Succesfully added social profiles', 'wp-erp' ) );
    }

}
