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
        $this->action( 'wp_ajax_erp-crm-bulk-contact-subscriber', 'bulk_assign_group' );

        $this->action( 'wp_ajax_erp-crm-customer-add-company', 'customer_add_company' );
        $this->action( 'wp_ajax_erp-crm-customer-edit-company', 'customer_edit_company' );
        $this->action( 'wp_ajax_erp-crm-customer-update-company', 'customer_update_company' );
        $this->action( 'wp_ajax_erp-crm-customer-remove-company', 'customer_remove_company' );
        $this->action( 'wp_ajax_erp-search-crm-user', 'search_crm_user' );
        $this->action( 'wp_ajax_erp-crm-save-assign-contact', 'save_assign_contact' );

        // Contact Group
        $this->action( 'wp_ajax_erp-crm-contact-group', 'contact_group_create' );
        $this->action( 'wp_ajax_erp-crm-edit-contact-group', 'contact_group_edit' );
        $this->action( 'wp_ajax_erp-crm-contact-group-delete', 'contact_group_delete' );
        $this->action( 'wp_ajax_erp-crm-exclued-already-assigned-contact', 'check_assing_contact' );

        // Contact Subscriber
        $this->action( 'wp_ajax_erp-crm-contact-subscriber', 'assign_contact_as_subscriber' );
        $this->action( 'wp_ajax_erp-crm-edit-contact-subscriber', 'edit_assign_contact' );
        $this->action( 'wp_ajax_erp-crm-contact-subscriber-delete', 'assign_contact_delete' );
        $this->action( 'wp_ajax_erp-crm-contact-subscriber-edit', 'edit_assign_contact_submission' );

        // Customer Feeds
        add_action( 'wp_ajax_erp_crm_get_customer_activity', array( $this, 'fetch_all_activity' ) );
        add_action( 'wp_ajax_erp_customer_feeds_save_notes', array( $this, 'save_activity_feeds' ) );
        add_action( 'wp_ajax_erp_crm_delete_customer_activity', array( $this, 'delete_customer_activity_feeds' ) );

        // Schedule page
        add_action( 'wp_ajax_erp_crm_add_schedules_action', array( $this, 'save_activity_feeds' ) );

        // script reload
        $this->action( 'wp_ajax_erp-crm-customer-company-reload', 'customer_company_template_refresh' );

        // Single customer view
        $this->action( 'wp_ajax_erp-crm-customer-social', 'customer_social_profile' );

        // Save Search actions
        $this->action( 'wp_ajax_erp_crm_create_new_save_search', 'create_save_search' );
        $this->action( 'wp_ajax_erp_crm_get_save_search_data', 'get_save_search' );
        $this->action( 'wp_ajax_erp_crm_delete_save_search_data', 'delete_save_search' );

        // CRM Dashboard
        $this->action( 'wp_ajax_erp-crm-get-single-schedule-details', 'get_single_schedule_details' );

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

        $posted      = array_map( 'strip_tags_deep', $_POST );
        $customer_id = erp_insert_people( $posted );

        if ( is_wp_error( $customer_id ) ) {
            $this->send_error( $customer_id->get_error_message() );
        }

        $customer = new Contact( intval( $customer_id ) );

        if ( $posted['photo_id'] ) {
            $customer->update_meta( 'photo_id', $posted['photo_id'] );
        }

        if ( $posted['life_stage'] ) {
            $customer->update_meta( 'life_stage', $posted['life_stage'] );
        }

        if ( !empty( $posted['date_of_birth'] ) ) {
            $customer->update_meta( 'date_of_birth', $posted['date_of_birth'] );
        }

        if ( $posted['source'] ) {
            $customer->update_meta( 'source', $posted['source'] );
        }

        if ( isset( $posted['social'] ) ) {
            foreach ( $posted['social'] as $field => $value ) {
                $customer->update_meta( $field, $value );
            }
        }

        do_action( 'erp_crm_save_contact_data', $customer, $customer_id, $posted );

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
        $customer    = new Contact( $customer_id );

        if ( ! $customer_id || ! $customer ) {
            $this->send_error( __( 'Cotact does not exists.', 'wp-erp' ) );
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
     * Contact bulk assign in contact group
     *
     * @since 1.0
     *
     * @return json
     */
    public function bulk_assign_group() {
        $this->verify_nonce( 'wp-erp-crm-bulk-contact-subscriber' );

        $contact_subscriber = [];
        $user_ids           = ( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] ) ) ? explode(',', $_POST['user_id'] ) : [];
        $group_ids          = ( isset( $_POST['group_id'] ) && ! empty( $_POST['group_id'] ) ) ? $_POST['group_id'] : [];

        if ( empty( $user_ids ) ) {
            $this->send_error( __( 'Contact must be required', 'wp-erp' ) );
        }

        if ( empty( $group_ids ) ) {
            $this->send_error( __( 'Atleast one group must be selected', 'wp-erp' ) );
        }

        foreach ( $user_ids as $user_key => $user_id ) {
            foreach ( $group_ids as $group_key => $group_id ) {
                $contact_subscriber = [
                    'user_id' => $user_id,
                    'group_id' => $group_id,
                    'status' => 'subscribe',
                    'subscribe_at' => current_time( 'mysql' ),
                    'unsubscribe_at' => current_time( 'mysql' )
                ];

                erp_crm_create_new_contact_subscriber( $contact_subscriber );
            }
        }

        $this->send_success( __( 'Selected contact are successfully subscribed', 'wp-erp' ) );

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

        $type        = isset( $_REQUEST['assign_type'] ) ? $_REQUEST['assign_type'] : '';
        $id          = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $company_id  = isset( $_REQUEST['erp_assign_company_id'] ) ? intval( $_REQUEST['erp_assign_company_id'] ) : 0;
        $customer_id = isset( $_REQUEST['erp_assign_customer_id'] ) ? intval( $_REQUEST['erp_assign_customer_id'] ) : 0;

        if ( $company_id && erp_crm_check_customer_exist_company( $id, $company_id ) ) {
            $this->send_error( __( 'Company already assigned. Choose another company', 'wp-erp' ) );
        }

        if ( $customer_id && erp_crm_check_customer_exist_company( $customer_id, $id ) ) {
            $this->send_error( __( 'Customer already assigned. Choose another customer', 'wp-erp' ) );
        }

        if ( ! $id ) {
            $this->send_error( __( 'No Customer found', 'wp-erp' ) );
        }

        if ( $type == 'assign_customer' ) {
            erp_crm_customer_add_company( $customer_id, $id );
        }

        if ( $type == 'assign_company' ) {
            erp_crm_customer_add_company( $id, $company_id );
        }

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

        $row_id     = isset( $_REQUEST['row_id'] ) ? intval( $_REQUEST['row_id'] ) : 0;
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

        $this->send_success( __('hello', 'wp-erp' ) );

    }

    public function search_crm_user() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $term = isset( $_REQUEST['q'] ) ? stripslashes( $_REQUEST['q'] ) : '';

        if ( empty( $term ) ) {
            die();
        }

        $found_crm_user = [];

        $crm_users = erp_crm_get_crm_users();

        if ( ! empty( $crm_users ) ) {
            foreach ( $crm_users as $user ) {
                $found_crm_user[ $user->ID ] = $user->display_name . ' (' . sanitize_email( $user->user_email ) . ')';
            }
        }

        $this->send_success( $found_crm_user );
    }

    /**
     * Save assign contact to crm manager
     *
     * @since 1.0
     *
     * @return json [object]
     */
    public function save_assign_contact() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        parse_str( $_POST['formData'], $output );

        if ( isset( $output['erp_select_assign_contact'] ) && empty( $output['erp_select_assign_contact'] ) ) {
            $this->send_error( __( 'Please select a user', 'wp-erp' ) );
        }

        if ( empty( $output['assign_contact_id'] ) ) {
            $this->send_error( __( 'No contact found', 'wp-erp' ) );
        }

        erp_people_update_meta( $output['assign_contact_id'], '_assign_crm_agent', $output['erp_select_assign_contact'] );

        $this->send_success( __( 'Assing to agent successfully', 'wp-erp' ) );
    }


    /**
     * Create Contact Group
     *
     * @since 1.0
     *
     * @return json
     */
    public function contact_group_create() {
        $this->verify_nonce( 'wp-erp-crm-contact-group' );

        if ( empty( $_POST['group_name'] ) ) {
            $this->send_error( __('Contact Group Name must be required', 'wp-erp' ) );
        }

        $data = [
            'id'          => ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) ? $_POST['id'] : '',
            'name'        => $_POST['group_name'],
            'description' => $_POST['group_description']
        ];

        erp_crm_save_contact_group( $data );

        $this->send_success( __( 'Contact group save successfully', 'wp-erp' ) );
    }

    /**
     * Edit Contact Group
     *
     * @since 1.0
     *
     * @return json
     */
    public function contact_group_edit() {

        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        $result = erp_crm_get_contact_group_by_id( $query_id );

        $this->send_success( $result );
    }

    /**
     * Contact group delete
     *
     * @since 1.0
     *
     * @return json
     */
    public function contact_group_delete() {

        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $query_id ) {
            $this->send_error( __( 'Somthing wrong, Please try later', 'wp-erp' ) );
        }

        erp_crm_contact_group_delete( $query_id );

        $this->send_success( __( 'Contact group delete successfully', 'wp-erp' ) );
    }

    /**
     * Get alreay assigned contact into subscriber
     *
     * @since 1.0
     *
     * @return json
     */
    public function check_assing_contact() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $result = erp_crm_get_assign_subscriber_contact();

        $this->send_success( $result );
    }

    /**
     * Edit assignable contact
     *
     * @since 1.0
     *
     * @return json
     */
    public function edit_assign_contact() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $data    = [];
        $user_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $user_id ) {
            $this->send_error( __( 'Contact not found. Try again', 'wp-erp' ) );
        }

        $result = erp_crm_get_editable_assign_contact( $user_id );

        foreach ( $result as $key => $value ) {
            $data[ $value['group_id'] ] = [
                'status'         => $value['status'],
                'subscribe_at'   => erp_format_date( $value['subscribe_at'] ),
                'unsubscribe_at' => erp_format_date( $value['unsubscribe_at'] ),
                'subscribe_message' => sprintf( ' ( %s %s )', __( 'Subscribed on', 'wp-erp' ), erp_format_date( $value['subscribe_at'] ) ),
                'unsubscribe_message' => sprintf( ' ( %s %s )', __( 'Unsubscribed on', 'wp-erp' ), erp_format_date( $value['unsubscribe_at'] ) )
            ];
        }

        $this->send_success( ['groups' => wp_list_pluck( $result, 'group_id' ), 'results' => $data ] );
    }

    /**
     * Assing Contact as a subscriber
     *
     * @since 1.0
     *
     * @return json
     */
    public function assign_contact_as_subscriber() {

        $this->verify_nonce( 'wp-erp-crm-contact-subscriber' );

        $data = [];

        if ( isset ( $_POST['group_id'] ) && isset( $_POST['user_id'] ) ) {
            foreach ( $_POST['group_id'] as $key => $group_id ) {
                $data = [
                    'user_id'  => $_POST['user_id'],
                    'group_id' => $group_id,
                    'status'   => 'subscribe', // @TODO: Set a settings for that
                    'subscribe_at' => current_time('mysql'),
                    'unsubscribe_at' => current_time('mysql')
                ];

            }

            erp_crm_create_new_contact_subscriber( $data );
        }


        return $this->send_success( __( 'Succesfully subscriber for this user', 'wp-erp' ) );
    }

    /**
     * Contact Subscriber delete
     *
     * @since 1.0
     *
     * @return json
     */
    public function assign_contact_delete() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $user_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $user_id ) {
            $this->send_error( __( 'No subscriber user found', 'wp-erp' ) );
        }

        erp_crm_contact_subscriber_delete( $user_id );

        $this->send_success( __( 'Contact group delete successfully', 'wp-erp' ) );
    }

    /**
     * Assing contact after edit form submission
     *
     * @since 1.0
     *
     * @return json
     */
    public function edit_assign_contact_submission() {
        $this->verify_nonce( 'wp-erp-crm-contact-subscriber' );

        $user_id = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : 0;
        $group_id = isset( $_POST['group_id'] ) ? $_POST['group_id'] : [];

        if ( ! $user_id ) {
            $this->send_error( __( 'No subscriber user found', 'wp-erp' ) );
        }

        erp_crm_edit_contact_subscriber( $group_id, $user_id );

        $this->send_success( __( 'Contact group edit successfully', 'wp-erp' ) );
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

        $customer = new \WeDevs\ERP\CRM\Contact( $customer_id );
        $customer->update_meta( 'crm_social_profile', $_POST );

        $this->send_success( __( 'Succesfully added social profiles', 'wp-erp' ) );
    }

    /**
     * Fetch all feed activities
     *
     * @since 1.0
     *
     * @return json
     */
    public function fetch_all_activity() {
        $feeds = erp_crm_get_feed_activity( $_POST );
        $this->send_success( $feeds );
    }

    /**
     * Create a new activity feeds
     *
     * @since 1.0
     *
     * @return json success|error
     */
    public function save_activity_feeds() {
        $this->verify_nonce( 'wp-erp-crm-customer-feed' );

        $save_data = [];
        $postdata  = $_POST;

        if ( ! isset( $postdata['user_id'] ) && empty( $postdata['user_id'] ) ) {
            $this->send_error( __( 'Customer not found', 'wp-erp' ) );
        }

        if ( isset( $postdata['message'] ) && empty( $postdata['message'] ) ) {
            $this->send_error( __( 'Content must be required', 'wp-erp' ) );
        }

        switch ( $postdata['type'] ) {
            case 'new_note':

                $save_data = [
                    'id'         => ( isset( $postdata['id'] ) && ! empty( $postdata['id'] ) ) ? $postdata['id'] : '',
                    'user_id'    => $postdata['user_id'],
                    'created_by' => $postdata['created_by'],
                    'message'    => $postdata['message'],
                    'type'       => $postdata['type']
                ];

                $data = erp_crm_save_customer_feed_data( $save_data );

                do_action( 'erp_crm_save_customer_new_note_feed', $save_data, $postdata );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'wp-erp' ) );
                }

                $this->send_success( $data );

                break;

            case 'email':

                $save_data = [
                    'user_id'       => $postdata['user_id'],
                    'created_by'    => $postdata['created_by'],
                    'message'       => $postdata['message'],
                    'type'          => $postdata['type'],
                    'email_subject' => $postdata['email_subject']
                ];

                $data = erp_crm_save_customer_feed_data( $save_data );

                $contact_id = intval( $postdata['user_id'] );

                $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

                $headers = "";
                $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";

                $is_cloud_active = erp_is_cloud_active();

                if( $is_cloud_active ) {
                    $wp_erp_api_key = get_option( 'wp_erp_apikey' );

                    $reply_to = $wp_erp_api_key . "-" . $postdata['created_by'] . "-" . $contact_id . "@incloud.wperp.com";
                    $headers .= "Reply-To: $reply_to" . "\r\n";
                }

                add_filter( 'wp_mail_from', 'erp_crm_get_email_from_address' );
                add_filter( 'wp_mail_from_name', 'erp_crm_get_email_from_name' );

                $query = [
                    'action' => 'erp_crm_track_email_read',
                    'aid'    => $data['id'],
                ];
                $email_url  = add_query_arg( $query, admin_url('admin-ajax.php') );
                $img_url    = '<img src="' . $email_url . '" width="1" height="1" border="0" />';

                $email_body = $postdata['message'] . $img_url;

                // Send email a contact
                wp_mail( $contact->email, $postdata['email_subject'], $email_body, $headers );

                remove_filter( 'wp_mail_from', 'erp_crm_get_email_from_address' );
                remove_filter( 'wp_mail_from_name', 'erp_crm_get_email_from_name' );
                remove_filter( 'wp_mail_content_type', 'erp_crm_get_email_content_type' );

                do_action( 'erp_crm_save_customer_email_feed', $save_data, $postdata );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'wp-erp' ) );
                }

                $this->send_success( $data );

                break;

            case 'log_activity':

                $extra_data = [
                    'invite_contact' => ( isset( $postdata['invite_contact'] ) && ! empty( $postdata['invite_contact'] ) ) ? $postdata['invite_contact'] : []
                ];

                $save_data = [
                    'id'            => ( isset( $postdata['id'] ) && ! empty( $postdata['id'] ) ) ? $postdata['id'] : '',
                    'user_id'       => $postdata['user_id'],
                    'created_by'    => $postdata['created_by'],
                    'message'       => $postdata['message'],
                    'type'          => $postdata['type'],
                    'log_type'      => $postdata['log_type'],
                    'email_subject' => ( isset( $postdata['email_subject'] ) && ! empty( $postdata['email_subject'] ) ) ? $postdata['email_subject'] : '',
                    'start_date'    => date( 'Y-m-d H:i:s', strtotime( $postdata['log_date'].$postdata['log_time'] ) ),
                    'extra'         => base64_encode( json_encode( $extra_data ) )
                ];

                $data = erp_crm_save_customer_feed_data( $save_data );

                do_action( 'erp_crm_save_customer_log_activity_feed', $save_data, $postdata );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'wp-erp' ) );
                }

                $this->send_success( $data );

                break;

            case 'schedule':

                $save_data = erp_crm_customer_prepare_schedule_postdata( $postdata );

                $data = erp_crm_save_customer_feed_data( $save_data );

                do_action( 'erp_crm_save_customer_schedule_feed', $save_data, $postdata );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'wp-erp' ) );
                }

                $this->send_success( $data );

                break;

            case 'tasks':

                $extra_data = [
                    'task_title'     => ( isset( $postdata['task_title'] ) && ! empty( $postdata['task_title'] ) ) ? $postdata['task_title'] : '',
                    'invite_contact' => ( isset( $postdata['invite_contact'] ) && ! empty( $postdata['invite_contact'] ) ) ? $postdata['invite_contact'] : []
                ];

                $save_data = [
                    'id'            => ( isset( $postdata['id'] ) && ! empty( $postdata['id'] ) ) ? $postdata['id'] : '',
                    'user_id'       => $postdata['user_id'],
                    'created_by'    => $postdata['created_by'],
                    'message'       => $postdata['message'],
                    'type'          => $postdata['type'],
                    'email_subject' => ( isset( $postdata['email_subject'] ) && ! empty( $postdata['email_subject'] ) ) ? $postdata['email_subject'] : '',
                    'start_date'    => date( 'Y-m-d H:i:s', strtotime( $postdata['task_date'].$postdata['task_time'] ) ),
                    'extra'         => base64_encode( json_encode( $extra_data ) )
                ];

                $data = erp_crm_save_customer_feed_data( $save_data );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'wp-erp' ) );
                }

                //@TODO: Need to send confirmation mail for assigned users
                do_action( 'erp_crm_save_customer_tasks_activity_feed', $save_data, $postdata );

                erp_crm_assign_task_to_users( $data, $save_data );

                $this->send_success( $data );

                break;

            default:
                do_action( 'erp_crm_save_customer_feed_data', $postdata );
                break;
        }
    }

    /**
     * Delete Activity feeds
     *
     * @since 1.0
     *
     * @return json
     */
    public function delete_customer_activity_feeds() {
        $this->verify_nonce( 'wp-erp-crm-customer-feed' );

        if ( ! $_POST['feed_id'] ) {
            $this->send_error( __( 'Feeds Not found', 'wp-erp' ) );
        }

        erp_crm_customer_delete_activity_feed( $_POST['feed_id'] );

        $this->send_success( __( 'Feed Deleted successfully', 'wp-erp' ) );
    }

    public function add_schedules_from_calendar() {
        $this->verify_nonce( 'wp-erp-crm-add-schedules' );

    }

    /**
     * Create Save Search
     *
     * @since 1.0
     *
     * @return json
     */
    public function create_save_search() {
        $this->verify_nonce( 'wp-erp-crm-save-search' );

        parse_str( $_POST['form_data'] );

        if ( ! $save_search ) {
            $this->send_error( __( 'Search item not found', 'wp-erp' ) );
        }

        if ( ! $erp_save_search_name ) {
            $this->send_error( __( 'Search Key name not found', 'wp-erp' ) );
        }

        $postdata = [
            'save_search' => ( isset( $save_search ) && !empty( $save_search ) ) ? $save_search : []
        ];

        $query_string = erp_crm_get_save_search_query_string( $postdata );

        if ( ! $query_string ) {
            $this->send_error( __( 'Query not found', 'wp-erp' ) );
        }

        $data = [
            'id'          => isset( $erp_update_save_search_id ) ? $erp_update_save_search_id : 0,
            'user_id'     => get_current_user_id(),
            'global'      => $erp_save_serach_make_global,
            'search_name' => $erp_save_search_name,
            'search_val'  => $query_string,
        ];

        $result = erp_crm_insert_save_search( $data );

        if ( ! $result ) {
            $this->send_error( __( 'Search does not save', 'wp-erp' ) );
        }

        $this->send_success( $result );
    }

    /**
     * Get Save Search
     *
     * @since 1.0
     *
     * @return json object
     */
    public function get_save_search() {
        $this->verify_nonce( 'wp-erp-crm-save-search' );

        $id = ( isset( $_POST['search_id'] ) && ! empty( $_POST['search_id'] ) ) ? $_POST['search_id'] : 0;

        if ( ! $id ) {
            $this->send_error( __( 'Search name not found', 'wp-erp' ) );
        }

        $result = erp_crm_get_save_search_item( [ 'id' => $id ] );

        $this->send_success( $result );
    }

    /**
     * Delete Save Search
     *
     * @since 1.0
     *
     * @return json boolean
     */
    public function delete_save_search() {
        $this->verify_nonce( 'wp-erp-crm-save-search' );

        $id = ( isset( $_POST['search_id'] ) && ! empty( $_POST['search_id'] ) ) ? $_POST['search_id'] : 0;

        if ( ! $id ) {
            $this->send_error( __( 'Search name not found', 'wp-erp' ) );
        }

        $result = erp_crm_delete_save_search_item( $id );

        $this->send_success( $result );
    }

    /**
     * Get single schedule details
     *
     * @since 1.0
     *
     * @return json [array]
     */
    public function get_single_schedule_details() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        $result = erp_crm_customer_get_single_activity_feed( $query_id );

        if ( ! $result ) {
            $this->send_error( __( 'Schedule data no found', 'wp-erp' ) );
        }

        $this->send_success( $result );
    }

}
