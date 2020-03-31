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

        // Set localize string
        $this->action( 'wp_ajax_erp_crm_set_localize_string', 'load_localize_string' );
        // Customer
        $this->action( 'wp_ajax_erp-crm-customer-new', 'create_customer' );
        $this->action( 'wp_ajax_erp-crm-customer-get', 'customer_get' );
        $this->action( 'wp_ajax_erp-crm-customer-delete', 'customer_remove' );
        $this->action( 'wp_ajax_erp-crm-customer-restore', 'customer_restore' );
        $this->action( 'wp_ajax_erp-crm-bulk-contact-subscriber', 'bulk_assign_group' );
        $this->action( 'wp_ajax_erp-crm-convert-user-to-contact', 'convert_user_to_customer' );
        $this->action( 'wp_ajax_erp-crm-get-contacts', 'get_all_contact' );
        $this->action( 'wp_ajax_erp-crm-get-contact-companies', 'get_contact_companies' );
        $this->action( 'wp_ajax_erp-crm-get-assignable-group', 'get_assignable_contact' );
        $this->action( 'wp_ajax_erp-search-crm-contacts', 'search_crm_contacts' );

        $this->action( 'wp_ajax_erp-crm-customer-add-company', 'customer_add_company' );
        $this->action( 'wp_ajax_erp-crm-customer-update-company', 'customer_update_company' );
        $this->action( 'wp_ajax_erp-crm-customer-remove-company', 'customer_remove_company' );
        $this->action( 'wp_ajax_erp-search-crm-user', 'search_crm_user' );
        $this->action( 'wp_ajax_erp-crm-save-assign-contact', 'save_assign_contact' );
        $this->action( 'wp_ajax_erp-crm-make-wp-user', 'make_wp_user' );

        // Contact by company
        $this->action( 'wp_ajax_erp-search-crm-company', 'search_company_contact' );

        // Contact Group
        $this->action( 'wp_ajax_erp-crm-contact-group', 'contact_group_create' );
        $this->action( 'wp_ajax_erp-crm-edit-contact-group', 'contact_group_edit' );
        $this->action( 'wp_ajax_erp-crm-contact-group-delete', 'contact_group_delete' );
        $this->action( 'wp_ajax_erp-crm-exclued-already-assigned-contact', 'check_assign_contact' );

        // Contact Subscriber
        $this->action( 'wp_ajax_erp-crm-contact-subscriber', 'assign_contact_as_subscriber' );
        $this->action( 'wp_ajax_erp-crm-edit-contact-subscriber', 'edit_assign_contact' );
        $this->action( 'wp_ajax_erp-crm-contact-subscriber-delete', 'assign_contact_delete' );
        $this->action( 'wp_ajax_erp-crm-contact-subscriber-edit', 'edit_assign_contact_submission' );

        // Customer Feeds
        add_action( 'wp_ajax_erp_crm_get_customer_activity', array( $this, 'fetch_all_activity' ) );
        add_action( 'wp_ajax_erp_customer_feeds_save_notes', array( $this, 'save_activity_feeds' ) );
        add_action( 'wp_ajax_erp_crm_delete_customer_activity', array( $this, 'delete_customer_activity_feeds' ) );
        add_action( 'wp_ajax_email_attachment', array( $this, 'email_attachment' ) );

        // Schedule page
        add_action( 'wp_ajax_erp_crm_add_schedules_action', array( $this, 'save_activity_feeds' ) );

        // script reload
        $this->action( 'wp_ajax_erp-crm-customer-company-reload', 'customer_company_template_refresh' );

        // Single customer view
        $this->action( 'wp_ajax_erp-crm-customer-social', 'customer_social_profile' );

        // Save Search actions
        $this->action( 'wp_ajax_erp_crm_create_new_save_search', 'create_save_search' );
        $this->action( 'wp_ajax_erp_crm_get_save_search_data', 'get_save_search' );
        // $this->action( 'wp_ajax_erp_crm_delete_save_search_data', 'delete_save_search' );
        $this->action( 'wp_ajax_erp-crm-delete-search-segment', 'delete_save_search' );
        //save group
        $this->action( 'wp_ajax_erp_crm_create_new_save_group', 'create_save_group' );

        // CRM Dashboard
        $this->action( 'wp_ajax_erp-crm-get-single-schedule-details', 'get_single_schedule_details' );

        // Save Replies in Settings page
        $this->action( 'wp_ajax_erp-crm-save-replies', 'save_template_save_replies' );
        $this->action( 'wp_ajax_erp-crm-edit-save-replies', 'edit_save_replies' );
        $this->action( 'wp_ajax_erp-crm-delete-save-replies', 'delete_save_replies' );
        $this->action( 'wp_ajax_erp-crm-load-save-replies-data', 'load_save_replies' );

        //update tags
        $this->action( 'wp_ajax_erp_crm_update_contact_tag', 'update_contact_tags' );
    }

    /**
     * Load crm localize string for customer signle view
     *
     * @since 1.1.2
     *
     * @return array
     */
    public function load_localize_string() {
        $strings = erp_crm_get_contact_feeds_localize_string();
        $this->send_success( $strings );
    }

    /**
     * Get all contact
     *
     * @since 1.1.0
     *
     * @return json
     */
    public function get_all_contact() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-vue-table' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $contacts = [];

        // only ncessary because we have sample data
        $args = [
            'type'      => '',
            'offset'    => 0,
            'number'    => 20,
            'no_object' => true,
        ];

        // Set type. By defaul it sets to contact :p
        if ( isset( $_REQUEST['type'] ) && ! empty( $_REQUEST['type'] ) ) {
            $args['type'] = sanitize_text_field( wp_unslash( $_REQUEST['type'] ) );
        }

        // Filter Limit value
        if ( isset( $_REQUEST['number'] ) && ! empty( $_REQUEST['number'] ) ) {
            $args['number'] = sanitize_text_field( wp_unslash( $_REQUEST['number'] ) );
        }

        // Filter offset value
        if ( isset( $_REQUEST['offset'] ) && ! empty( $_REQUEST['offset'] ) ) {
            $args['offset'] = sanitize_text_field( wp_unslash( $_REQUEST['offset'] ) );
        }

        // Filter for serach
        if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
            $args['s'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
        }

        // Filter for order & order by
        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby']  = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
            $args['order']    = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ;
        } else {
            $args['orderby']  = 'created';
            $args['order']    = 'DESC';
        }

        // Filter for customer life stage
        if ( isset( $_REQUEST['status'] ) && ! empty( $_REQUEST['status'] ) ) {
            if ( $_REQUEST['status'] != 'all' ) {
                if ( $_REQUEST['status'] == 'trash' ) {
                    $args['trashed'] = true;
                } else {
                    $args['life_stage'] = sanitize_text_field( wp_unslash( $_REQUEST['status'] ) );
                }
            }
        }

        if ( isset( $_REQUEST['filter_assign_contact']) && !empty( $_REQUEST['filter_assign_contact']) ){
            $args['contact_owner'] = sanitize_text_field( wp_unslash( $_REQUEST['filter_assign_contact'] ) );
        }

        if ( isset( $_REQUEST['erpadvancefilter'] ) && ! empty( $_REQUEST['erpadvancefilter'] ) ) {
            $args['erpadvancefilter'] = sanitize_text_field( wp_unslash( $_REQUEST['erpadvancefilter'] ) );
        }

        if ( isset( $_REQUEST['filter_contact_company'] ) && ! empty( $_REQUEST['filter_contact_company'] ) ) {
            $companies = erp_crm_company_get_customers( array( 'id' => sanitize_text_field( wp_unslash( $_REQUEST['filter_contact_company'] ) ) ) );

            foreach ( $companies as $company ) {
                $contacts['data'][] = $company['contact_details'];
            }

            $total_items = count( $contacts['data'] );
        } else {
            $contacts['data']  = erp_get_peoples( $args );
            $args['count'] = true;
            $total_items = erp_get_peoples( $args );
        }

        foreach ( $contacts['data'] as $key => $contact ) {
            $contact_owner    = [];
            $contact_owner_id = $contact['contact_owner'];

            if ( $contact_owner_id ) {
                $user = \get_user_by( 'id', $contact_owner_id );

                $contact_owner = [
                    'id'           => $user->ID,
                    'avatar'       => get_avatar_url( $user->ID ),
                    'first_name'   => $user->first_name,
                    'last_name'    => $user->last_name,
                    'display_name' => $user->display_name,
                    'email'        => $user->user_email
                ];
            }
            $contacts['data'][$key]['details_url']   = erp_crm_get_details_url( $contact['id'], $contact['types'] );
            $contacts['data'][$key]['avatar']['url'] = erp_crm_get_avatar_url( $contact['id'], $contact['email'], $contact['user_id'] );
            $contacts['data'][$key]['avatar']['img'] = erp_crm_get_avatar( $contact['id'], $contact['email'], $contact['user_id'] );
            $contacts['data'][$key]['life_stage']    = $contact['life_stage'];
            $contacts['data'][$key]['assign_to']     = $contact_owner;
            $contacts['data'][$key]['created']       = erp_format_date( $contact['created'] );
        }

        $contacts['total_items']   = $total_items;
        $this->send_success( $contacts );
    }

    /**
     * Get contact companies relations
     *
     * @since 1.1.0
     *
     * @return josn
     */
    public function get_contact_companies() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        unset( $_POST['_wpnonce'], $_POST['_wp_http_referer'], $_POST['action'] );

        if ( isset( $_POST['type'] ) && empty( $_POST['type'] ) ) {
            $this->send_error( __( 'Type must be required', 'erp' ) );
        }

        if ( 'contact_companies' == $_POST['type'] ) {
            $data = erp_crm_customer_get_company( $_POST );
        } else if ( 'company_contacts' == $_POST['type'] ) {
            $data = erp_crm_company_get_customers( $_POST );
        } else {
            $data = [];
        }

        if ( is_wp_error( $data ) ) {
            $this->send_error( $data->get_error_message() );
        }

        $this->send_success( $data );
    }

    /**
     * Get assignable contact
     *
     * @since 1.1.0
     *
     * @return json
     */
    public function get_assignable_contact() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        unset( $_POST['_wpnonce'], $_POST['_wp_http_referer'], $_POST['action'] );

        if ( ! isset( $_POST['id'] ) ) {
            $this->send_error( __( 'No company or contact found', 'erp' ) );
        }

        $data = erp_crm_get_user_assignable_groups( sanitize_text_field( wp_unslash( $_POST['id'] ) ) );

        if ( is_wp_error( $data ) ) {
            $this->send_error( $data->get_error_message() );
        }

        $this->send_success( $data );
    }

    /**
     * Craete new customer
     *
     * @since 1.0
     *
     * @return json
     */
    public function create_customer() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-customer-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $current_user_id                      = get_current_user_id();
        $posted                               = array_map( 'strip_tags_deep', $_POST );
        $posted['contact']['main']['company'] = stripslashes( $posted['contact']['main']['company'] ); // To remove Apostrophe slash

        $data   = array_merge( $posted['contact']['main'], $posted['contact']['meta'], $posted['contact']['social'] );

        if ( ! $data['id'] && ! current_user_can( 'erp_crm_add_contact' ) ) {
            $this->send_error( __( 'You don\'t have any permission to add new contact', 'erp' ) );
        }

        if ( $data['id'] && ! current_user_can( 'erp_crm_edit_contact', $data['id'] ) && $current_user_id != $data['contact_owner'] ) {
            $this->send_error( __( 'You don\'t have any permission to edit this contact', 'erp' ) );
        }

        $customer_id = erp_insert_people( $data );

        if ( is_wp_error( $customer_id ) ) {
            $this->send_error( $customer_id->get_error_message() );
        }

        if ( $current_user_id != $data['contact_owner'] ) {
            $email = new \WeDevs\ERP\CRM\Emails\New_Contact_Assigned();
            $email->trigger( $customer_id );
        }

        $customer = new Contact( intval( $customer_id ) );

        $group_ids = ( isset( $posted['group_id'] ) && !empty( $posted['group_id'] ) ) ? $posted['group_id'] : [];

        erp_crm_edit_contact_subscriber( $group_ids, $customer_id );

        do_action( 'erp_crm_save_contact_data', $customer, $customer_id, $data );

        $customer_data = $customer->to_array();
        $statuses = erp_crm_customer_get_status_count( $data['type'] );

        $this->send_success( [ 'data' => $customer_data, 'statuses' => $statuses ] );
    }

    /**
     * Get customer details
     *
     * @since 1.0
     *
     * @return array
     */
    public function customer_get() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $customer_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $customer    = new Contact( $customer_id );

        if ( ! $customer_id || ! $customer ) {
            $this->send_error( __( 'Cotact does not exists.', 'erp' ) );
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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $ids         = [];
        $customer_id = ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['id'] ) ): intval( $_REQUEST['id'] );
        $hard        = isset( $_REQUEST['hard'] ) ? intval( $_REQUEST['hard'] ) : 0;
        $type        = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';

        // Check if this contact OR company has relationship with any company OR contact
        $if_customer_has_relations = ( !is_array( $customer_id ) ) ? erp_crm_check_company_contact_relations( $customer_id, $type ) : 0;

        if ( $if_customer_has_relations != 0 ) {
            if ( $type == 'contact') {
                $this->send_error( __( "You can't delete this contact as this contact has a relation with a company. Please make sure this contract is not assigned to any company before deleting.", 'erp' ) );
            }
            if ( $type == 'company') {
                $this->send_error( __( "You can't delete this company as this company has a relation with a contact. Please make sure this company is not assigned to any contact before deleting.", 'erp' ) );
            }
        }

        // Check permission for trashing and permanent deleting contact;
        if ( is_array( $customer_id ) ) {
            foreach ( $customer_id as $contact_id ) {
                if ( ! current_user_can( 'erp_crm_delete_contact', $contact_id, $hard ) ) {
                    continue;
                }
                $ids[] = $contact_id;
            }
        } else {
            if ( ! current_user_can( 'erp_crm_delete_contact', $customer_id, $hard ) && ! current_user_can( 'erp_crm_agent' )  ) {
                $this->send_error( __( 'You don\'t have any permission to delete this contact', 'erp' ) );
            }
            $ids[] = $customer_id;
        }

        if ( empty( $ids ) ) {
            $this->send_error( __( 'Can not delete - You do not own this contact(s)', 'erp' ) );
        }

        $data = [
            'id'   => $ids,
            'hard' => $hard,
            'type' => $type
        ];

        $deleted = erp_delete_people( $data );

        if ( is_wp_error( $deleted ) ) {
            $this->send_error( $deleted->get_error_message() );
        }

        $statuses = erp_crm_customer_get_status_count( $type );

        $this->send_success( [ 'statuses' => $statuses ] );
    }

    /**
     * Restore customer from trash
     *
     * @since 1.0
     *
     * @return json
     */
    public function customer_restore() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $customer_id = ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) ? (array)sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) : intval( $_REQUEST['id'] );
        $type        = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';

        $data = [
            'id'   => $customer_id,
            'type' => $type
        ];

        $restored = erp_restore_people( $data );

        if ( is_wp_error( $restored ) ) {
            $this->send_error( $restored->get_error_message() );
        }

        $statuses = erp_crm_customer_get_status_count( $type );

        $this->send_success( [ 'statuses' => $statuses ] );
    }

    /**
     * Contact bulk assign in contact group
     *
     * @since 1.0
     *
     * @return json
     */
    public function bulk_assign_group() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-bulk-contact-subscriber' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $ids                = [];
        $contact_subscriber = [];
        $user_ids           = ( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] ) ) ? explode(',', sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) ) : [];
        $group_ids          = ( isset( $_POST['group_id'] ) && ! empty( $_POST['group_id'] ) ) ? wp_unslash( $_POST['group_id'] ) : [];

        if ( empty( $user_ids ) ) {
            $this->send_error( __( 'Contact must be required', 'erp' ) );
        }

        if ( empty( $group_ids ) ) {
            $this->send_error( __( 'Atleast one group must be selected', 'erp' ) );
        }

        // Check permission for trashing and permanent deleting contact;
        foreach ( $user_ids as $contact_id ) {
            if ( ! current_user_can( 'erp_crm_edit_contact', $contact_id ) ) {
                continue;
            }
            $ids[] = $contact_id;
        }

        if ( empty( $ids ) ) {
            $this->send_error( __( 'Can not assign any group - You do not own this contact(s)', 'erp' ) );
        }

        foreach ( $ids as $user_key => $user_id ) {
            foreach ( $group_ids as $group_key => $group_id ) {
                $contact_subscriber = [
                    'user_id'  => $user_id,
                    'group_id' => $group_id
                ];

                erp_crm_create_new_contact_subscriber( $contact_subscriber );
            }
        }

        $this->send_success( __( 'Selected contact are successfully subscribed', 'erp' ) );

    }

    /**
     * Convert user to contact or company
     *
     * @since 1.0
     *
     * @return json
     */
    public function convert_user_to_customer() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $id   = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ): 0;
        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ): '';
        $is_wp = isset( $_POST['is_wp'] ) ? true : false;

        if ( ! $id ) {
            $this->send_error( __( 'User not found', 'erp' ) );
        }

        if ( empty( $type ) ) {
            $this->send_error( __( 'Type not found', 'erp' ) );
        }

        $args = [
            'type'       => $type,
            'is_wp_user' => $is_wp,
            'wp_user_id' => $id,
            'people_id'  => $id,
        ];

        $people_id = erp_convert_to_people( $args );

        if ( is_wp_error( $people_id ) ) {
            $this->send_error( $people_id->get_error_message() );
        }

        $statuses = erp_crm_customer_get_status_count( $type );

        $this->send_success( [ 'id' => $people_id, 'statuses' => $statuses ] );
    }

    /**
     * Adds company to customer individual profile
     *
     * @since 1.0
     *
     * @return
     */
    public function customer_add_company() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-assign-customer-company-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }


        $type        = isset( $_REQUEST['assign_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['assign_type'] ) ) : '';
        $id          = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $company_id  = isset( $_REQUEST['erp_assign_company_id'] ) ? intval( $_REQUEST['erp_assign_company_id'] ) : 0;
        $customer_id = isset( $_REQUEST['erp_assign_customer_id'] ) ? intval( $_REQUEST['erp_assign_customer_id'] ) : 0;

        if ( $company_id && erp_crm_check_customer_exist_company( $id, $company_id ) ) {
            $this->send_error( __( 'Company already assigned. Choose another company', 'erp' ) );
        }

        if ( $customer_id && erp_crm_check_customer_exist_company( $customer_id, $id ) ) {
            $this->send_error( __( 'Contact already assigned. Choose another contact', 'erp' ) );
        }

        if ( ! $id ) {
            $this->send_error( __( 'No contact found', 'erp' ) );
        }

        if ( $type == 'assign_customer' ) {
            erp_crm_customer_add_company( $customer_id, $id );
        }

        if ( $type == 'assign_company' ) {
            erp_crm_customer_add_company( $id, $company_id );
        }

        $this->send_success( __( 'Company has been added successfully', 'erp' ) );

    }

    /**
     * Save Company edit field for customer
     */
    public function customer_update_company() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-customer-update-company-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $row_id     = isset( $_REQUEST['row_id'] ) ? intval( $_REQUEST['row_id'] ) : 0;
        $company_id = isset( $_REQUEST['company_id'] ) ? intval( $_REQUEST['company_id'] ) : 0;

        $result = erp_crm_customer_update_company( $row_id, $company_id );

        $this->send_success( __( 'Company has been updated successfully', 'erp' ) );
    }

    /**
     * Remove Company from Customer Single Profile
     */
    public function customer_remove_company() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if( $id ) {
            erp_crm_customer_remove_company( $id );
        }

        $this->send_success( __('hello', 'erp' ) );

    }

    /**
     * Search crm users
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function search_crm_user() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $term = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';

        if ( empty( $term ) ) {
            die();
        }

        $found_crm_user = [];
        $crm_users = erp_crm_get_crm_user( [ 's' => $term ] );

        if ( ! empty( $crm_users ) ) {
            foreach ( $crm_users as $user ) {
                $found_crm_user[ $user->ID ] = $user->display_name;
            }
        }

        $this->send_success( $found_crm_user );
    }

     /**
     * Search crm contact company
     *
     * @since 1.3.7
     *
     * @return void
     */
    public function search_company_contact() {
        $term = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';

        if ( empty( $term ) ) {
            die();
        }

        $found_contact_company = [];
        $crm_companies = erp_get_peoples( [ 's' => $term, 'type' => 'company' ] );

        if ( ! empty( $crm_companies ) ) {
            foreach ( $crm_companies as $company ) {
                $found_contact_company[ $company->id ] = $company->first_name;
            }
        }

        $this->send_success( $found_contact_company );
    }

    /**
     * Search CRM contacts by keywords
     *
     * @since 1.1.0
     *
     * @return json
     */
    public function search_crm_contacts() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $term = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $types = isset( $_REQUEST['types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['types'] ) ) : [];

        if ( empty( $term ) ) {
            die();
        }

        if ( empty( $types ) ) {
            die();
        }

        $found_crm_contact = [];
        $type              = ( count( $types ) > 1 ) ? $types : reset( $types );
        $crm_contacts      = erp_get_peoples( [ 's' => $term, 'type' => $type ] );

        if ( ! empty( $crm_contacts ) ) {
            foreach ( $crm_contacts as $user ) {
                if ( in_array( 'company', $user->types ) ) {
                    $found_crm_contact[ $user->id ] = $user->company;
                } else {
                    $found_crm_contact[ $user->id ] = $user->first_name . ' ' . $user->last_name;
                }
            }
        }

        $this->send_success( $found_crm_contact );
    }

    /**
     * Save assign contact to crm manager
     *
     * @since 1.0
     *
     * @return json [object]
     */
    public function save_assign_contact() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $form_data = isset( $_POST['formData'] ) ? sanitize_text_field( wp_unslash( $_POST['formData'] ) ) : '';
        parse_str( $form_data, $output );

        //contact Owner
        if ( isset( $output['erp_select_assign_contact'] ) && empty( $output['erp_select_assign_contact'] ) ) {
            $this->send_error( __( 'Please select a user', 'erp' ) );
        }

        //contact id
        if ( empty( $output['assign_contact_id'] ) ) {
            $this->send_error( __( 'No contact found', 'erp' ) );
        }

        if ( $output['assign_contact_user_id'] ) {
            erp_crm_update_contact_owner( $output['assign_contact_user_id'], $output['erp_select_assign_contact'], 'user_id' );
        } else {
            erp_crm_update_contact_owner($output['assign_contact_id'], $output['erp_select_assign_contact'], 'id' );
        }

        $this->send_success( __( 'Assign to agent successfully', 'erp' ) );
    }

    /**
    * Make crm contact to wp user
    *
    * @since 1.1.7
    * @since 1.2.4 Check if current user has permission to higher level wp user
     *
    * @return void
    **/
    public function make_wp_user() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-crm-make-wp-user' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $customer_id  = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ): 0;
        $type         = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ): '';
        $email        = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
        $role         = isset( $_POST['customer_role'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_role'] ) ) : '';
        $notify_email = isset( $_POST['send_password_notification'] ) ? true : false;

        if ( ! $customer_id ) {
            $this->send_error( __( 'Contact not found', 'erp' ) );
        }

        if ( ! $type ) {
            $this->send_error( __( 'Contact type not found', 'erp' ) );
        }

        $allowed_roles_to_create = array_keys( erp_get_editable_roles() );
        if ( ! in_array( $role, $allowed_roles_to_create ) ) {
            $this->send_error( __( 'Not allowed to crated user with the selected role', 'erp' ) );
        }

        $data = [
            'email'        => $email,
            'type'         => $type,
            'role'         => $role,
            'notify_email' => $notify_email
        ];

        $data = erp_crm_make_wp_user( $customer_id, $data );

        if ( is_wp_error( $data ) ) {
            $this->send_error( $data->get_error_message() );
        }

        $this->send_success();
    }


    /**
     * Create Contact Group
     *
     * @since 1.0
     * @since 1.2.2 Add `private` column
     *
     * @return json
     */
    public function contact_group_create() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-contact-group' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        // Check permission
        if ( ! current_user_can( 'erp_crm_create_groups' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( empty( $_POST['group_name'] ) ) {
            $this->send_error( __('Contact Group Name must be required', 'erp' ) );
        }

        $form_data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );

        $data = [
            'id'          => ( isset( $_POST['id'] ) && !empty( $form_data['id'] ) ) ? sanitize_text_field( wp_unslash( $form_data['id'] ) ) : '',
            'name'        => sanitize_text_field( wp_unslash( $form_data['group_name'] ) ),
            'description' => sanitize_text_field( wp_unslash( $form_data['group_description'] ) ),
            'private'     => erp_validate_boolean( $form_data['group_private'] ) ? 1 : null,
        ];

        erp_crm_save_contact_group( $data );

        $this->send_success( __( 'Contact group save successfully', 'erp' ) );
    }

    /**
     * Edit Contact Group
     *
     * @since 1.0
     *
     * @return json
     */
    public function contact_group_edit() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        // Check permission
        if ( ! current_user_can( 'erp_crm_delete_groups' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! $query_id ) {
            $this->send_error( __( 'Somthing wrong, Please try later', 'erp' ) );
        }

        erp_crm_contact_group_delete( $query_id );

        $this->send_success( __( 'Contact group delete successfully', 'erp' ) );
    }

    /**
     * Get already assigned contact into subscriber
     *
     * @since 1.0
     *
     * @return json
     */
    public function check_assign_contact() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $data    = [];
        $user_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $user_id ) {
            $this->send_error( __( 'Contact not found. Try again', 'erp' ) );
        }

        $result = erp_crm_get_editable_assign_contact( $user_id );

        foreach ( $result as $key => $value ) {
            $data[ $value['group_id'] ] = [
                'status'         => $value['status'],
                'subscribe_at'   => erp_format_date( $value['subscribe_at'] ),
                'unsubscribe_at' => erp_format_date( $value['unsubscribe_at'] ),
                'subscribe_message' => sprintf( ' ( %s %s )', __( 'Subscribed on', 'erp' ), erp_format_date( $value['subscribe_at'] ) ),
                'unsubscribe_message' => sprintf( ' ( %s %s )', __( 'Unsubscribed on', 'erp' ), erp_format_date( $value['unsubscribe_at'] ) )
            ];
        }

        $this->send_success( ['groups' => wp_list_pluck( $result, 'group_id' ), 'results' => $data ] );
    }

    /**
     * Assign Contact as a subscriber
     *
     * @since 1.0
     *
     * @return json
     */
    public function assign_contact_as_subscriber() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-contact-subscriber' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $data = [];

        $user_id   = ! empty( $_POST['user_id'] )  ? absint( $_POST['user_id'] ) : 0;
        $group_ids = ! empty( $_POST['group_id'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['group_id'] ) ): [];

        if ( ! $user_id ) {
            $this->send_error( __( 'No user data found', 'erp' ) );
        }

        if ( ! current_user_can( 'erp_crm_edit_contact', $user_id ) ) {
            $this->send_error( __( 'You don\'t have any permission to assign this contact in a group', 'erp' ) );
        }

        foreach ( $group_ids as $key => $group_id ) {
            $data = [
                'user_id'  => $user_id,
                'group_id' => $group_id,
            ];
        }

        erp_crm_create_new_contact_subscriber( $data );

        $this->send_success( __( 'Succesfully subscriber for this user', 'erp' ) );
    }

    /**
     * Contact Subscriber delete
     *
     * @since 1.0
     *
     * @return json
     */
    public function assign_contact_delete() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $user_id  = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $group_id = isset( $_REQUEST['group_id'] ) ? intval( $_REQUEST['group_id'] ) : 0;

        if ( ! current_user_can( 'erp_crm_edit_contact', $user_id ) ) {
            $this->send_error( __( 'You don\'t have any permission to remove this contact from a group', 'erp' ) );
        }

        if ( ! $user_id ) {
            $this->send_error( __( 'No subscriber contact found', 'erp' ) );
        }

        if ( ! $group_id ) {
            $this->send_error( __( 'No subscriber group found', 'erp' ) );
        }

        erp_crm_contact_subscriber_delete( $user_id, $group_id );

        $this->send_success( __( 'Contact group delete successfully', 'erp' ) );
    }

    /**
     * Assign contact after edit form submission
     *
     * @since 1.0
     *
     * @return json
     */
    public function edit_assign_contact_submission() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-contact-subscriber' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $user_id = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : 0;
        $group_id = isset( $_POST['group_id'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['group_id'] ) ): [];

        if ( ! current_user_can( 'erp_crm_edit_contact', $user_id ) ) {
            $this->send_error( __( 'You don\'t have any permission to assign this contact', 'erp' ) );
        }

        if ( ! $user_id ) {
            $this->send_error( __( 'No subscriber user found', 'erp' ) );
        }

        erp_crm_edit_contact_subscriber( $group_id, $user_id );

        $this->send_success( __( 'Contact group edit successfully', 'erp' ) );
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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-customer-social-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        // @TODO: check permission
        unset( $_POST['_wp_http_referer'] );
        unset( $_POST['_wpnonce'] );
        unset( $_POST['action'] );

        if ( empty( $_POST['customer_id'] ) ) {
            $this->send_error( __( 'No customer found', 'erp' ) );
        }

        $customer_id = absint( $_POST['customer_id'] ) ;
        unset( $_POST['customer_id'] );

        $customer = new \WeDevs\ERP\CRM\Contact( $customer_id );
        $customer->update_meta( 'crm_social_profile', $_POST );

        $this->send_success( __( 'Succesfully added social profiles', 'erp' ) );
    }

    /**
     * Fetch all feed activities
     *
     * @since 1.0
     *
     * @return json
     */
    public function fetch_all_activity() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            // die();
        }

        $post_data = isset( $_POST ) ? $_POST : [];
        $data = array_map( 'sanitize_text_field', wp_unslash( $post_data ) );
        $feeds = erp_crm_get_feed_activity( $data );
        $this->send_success( $feeds );
    }

    /**
     * Create a new activity feeds
     *
     * @since 1.0
     * @since 1.1.19 Filter the from name to set current user display name
     *
     * @return json success|error
     */
    public function save_activity_feeds() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-customer-feed' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $save_data      = [];
        $postdata       = $_POST;
        $attachments   = ( isset( $postdata['attachments'] ) ) ? $postdata['attachments'] : array();
        if ( ! isset( $postdata['user_id'] ) && empty( $postdata['user_id'] ) ) {
            $this->send_error( __( 'Customer not found', 'erp' ) );
        }

        // Check permission
        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( isset( $postdata['message'] ) && empty( $postdata['message'] ) ) {
            $this->send_error( __( 'Content must be required', 'erp' ) );
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
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'erp' ) );
                }

                $this->send_success( $data );

                break;

            case 'email':
                $message = wp_unslash( $postdata['message'] );

                $extra_data = [
                    'attachments' => $attachments
                ];
                $save_data = [
                    'user_id'       => $postdata['user_id'],
                    'created_by'    => $postdata['created_by'],
                    'message'       => $message,
                    'type'          => $postdata['type'],
                    'email_subject' => $postdata['email_subject'],
                    'extra'         => base64_encode( json_encode( $extra_data ) )
                ];

                $data = erp_crm_save_customer_feed_data( $save_data );

                $contact_id = intval( $postdata['user_id'] );

                $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

                $headers = "";
                $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";

                $erp_is_imap_active = erp_is_imap_active();
                $reply_to_name      = erp_crm_get_email_from_name();

                if ( $erp_is_imap_active ) {
                    $imap_options = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );
                    $reply_to     = $imap_options['username'];
                } else {
                    $reply_to      = erp_crm_get_email_from_address();
                }

                $headers .= "Reply-To: {$reply_to_name} <$reply_to>" . "\r\n";

                $contact_owner_id = $contact->get_contact_owner();

                $server_host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';

                $message_id = md5( uniqid( time() ) ) . '.' . $contact_id . '.' . $contact_owner_id . '.r2@' . $server_host;

                $custom_headers = [
                    "In-Reply-To" => "<{$message_id}>",
                    "References" => "<{$message_id}>",
                ];

                $query = [
                    'action' => 'erp_crm_track_email_opened',
                    'aid'    => $data['id'],
                ];

                $email_url  = add_query_arg( $query, admin_url('admin-ajax.php') );
                $img_url    = '<img src="' . $email_url . '" width="1" height="1" style="display:none;" />';

                $email_body = $message . $img_url;

                add_filter( 'erp_mail_from_name', 'erp_crm_get_email_from_name' );

                $mail_attachments = wp_list_pluck( $attachments, 'path' );

                if ( wperp()->google_auth->is_active() ){
                    //send using gmail api
                    $sent = erp_mail_send_via_gmail( $contact->email, $postdata['email_subject'], $email_body, $headers, $mail_attachments, $custom_headers  );
                } else {
                    // Send email at contact
                    $sent = erp_mail( $contact->email, $postdata['email_subject'], $email_body, $headers, $mail_attachments, $custom_headers );
                }

                do_action( 'erp_crm_save_customer_email_feed', $save_data, $postdata );

                if ( !$sent ) {
                    $this->send_error( __( 'Can not send email, Please try later', 'erp' ) );
                }

                if ( ! $data ) {
                    $this->send_error( __( 'Something went wrong, Please try later', 'erp' ) );
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
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'erp' ) );
                }

                $this->send_success( $data );

                break;

            case 'schedule':

                $save_data = erp_crm_customer_prepare_schedule_postdata( $postdata );

                $data = erp_crm_save_customer_feed_data( $save_data );

                do_action( 'erp_crm_save_customer_schedule_feed', $save_data, $postdata );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'erp' ) );
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
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'erp' ) );
                }

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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-customer-feed' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( empty( $_POST['feed_id'] ) ) {
            $this->send_error( __( 'Feeds Not found', 'erp' ) );
        }

        $feed_id = isset( $_POST['feed_id'] ) ? sanitize_text_field( wp_unslash( $_POST['feed_id'] ) ) : '';

        erp_crm_customer_delete_activity_feed( $feed_id );

        $this->send_success( __( 'Feed Deleted successfully', 'erp' ) );
    }

    /**
     * Create Save Search
     *
     * @since 1.0
     *
     * @return json
     */
    public function create_save_search() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $postdata = isset( $_POST['form_data'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['form_data'] ) )  : array();

        if ( ! $postdata ) {
            $this->send_error( __( 'No data not found', 'erp' ) );
        }

        if ( isset( $postdata['search_name'] ) && empty( $postdata['search_name'] ) ) {
            $this->send_error( __( 'Search name not found', 'erp' ) );
        }

        if ( isset( $postdata['type'] ) && empty( $postdata['type'] ) ) {
            $this->send_error( __( 'Contact Type not found', 'erp' ) );
        }

        if ( isset( $postdata['search_fields'] ) && empty( $postdata['search_fields'] ) ) {
            $this->send_error( __( 'Search filters not found', 'erp' ) );
        }

        $search_fields = ( isset( $postdata['search_fields'] ) && !empty( $postdata['search_fields'] ) ) ? $postdata['search_fields'] : '';

        if ( ! $search_fields ) {
            $this->send_error( __( 'Query not found', 'erp' ) );
        }

        $data = [
            'id'          => $postdata['id'] ? $postdata['id'] : 0,
            'user_id'     => get_current_user_id(),
            'type'        => $postdata['type'],
            'global'      => ( $postdata['search_it_global'] == 'true' ) ? 1 : 0,
            'search_name' => $postdata['search_name'],
            'search_val'  => $search_fields,
        ];


        $exists = erp_crm_check_segment_exists( $data['search_name'] );
        if ( $exists ) {
            $this->send_error( __( 'Segment name alreday exists.', 'erp' ) );
        }

        $result = erp_crm_insert_save_search( $data );
        if ( ! $result ) {
            $this->send_error( __( 'Search does not save', 'erp' ) );
        }

        $this->send_success( $result );
    }

    /**
     * Save contact group
     *
     * @since 1.2.5
     */
    public function create_save_group(){
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $postdata = isset( $_POST['form_data'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['form_data'] ) )  : array();

        if ( ! $postdata ) {
            $this->send_error( __( 'No data not found', 'erp' ) );
        }

        if ( isset( $postdata['group_name'] ) && empty( $postdata['group_name'] ) ) {
            $this->send_error( __( 'Group name not found', 'erp' ) );
        }

        if ( isset( $postdata['search_fields'] ) && empty( $postdata['search_fields'] ) ) {
            $this->send_error( __( 'No search fields found', 'erp' ) );
        }

        $type = isset($postdata['type'])?$postdata['type']:'contact';

        $contacts = [];

        $args = [
            'type'      => $type,
            'offset'    => 0,
            'number'    => '-1',
            'no_object' => false,
            'erpadvancefilter' => $postdata['search_fields'],
        ];

        $group = erp_crm_save_contact_group( array('name' => $postdata['group_name'] ) );

        if( ! $group ){
            $this->send_error( __( 'Could not create group.', 'erp' ) );
        }

        $contacts = erp_get_peoples( $args );

        $imported = 0;
        foreach ( $contacts as $contact){
            $data = [
                'user_id'  => $contact->id,
                'group_id' => $group->id,
            ];

            erp_crm_create_new_contact_subscriber( $data );
            $imported++;
        }

        if( $imported > 0){
            $this->send_success( __( 'Successfully created group and assigned selected contacts to the group.', 'erp' ) );
        }

        $this->send_error( __( 'Could not import contacts to the group.', 'erp' ) );
    }

    /**
     * Get Save Search
     *
     * @since 1.0
     *
     * @return json object
     */
    public function get_save_search() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $id = ( isset( $_POST['search_id'] ) && ! empty( $_POST['search_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['search_id'] ) ) : 0;

        if ( ! $id ) {
            $this->send_error( __( 'Search name not found', 'erp' ) );
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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $id = ( isset( $_POST['filterId'] ) && ! empty( $_POST['filterId'] ) ) ? sanitize_text_field( wp_unslash( $_POST['filterId'] ) ) : 0;

        if ( ! $id ) {
            $this->send_error( __( 'Search segment not found', 'erp' ) );
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
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        $result = erp_crm_customer_get_single_activity_feed( $query_id );

        if ( ! $result ) {
            $this->send_error( __( 'Schedule data no found', 'erp' ) );
        }

        $this->send_success( $result );
    }

    /**
     * Save Templates ajax
     *
     * @since 1.0
     *
     * @return json
     */
    public function save_template_save_replies() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-save-replies' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $data = [
            'id'       => isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : 0,
            'name'     => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ): '',
            'subject'  => isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ): '',
            /*'template' => isset( $_POST['template'] ) ? $_POST['template'] : ''*/
            'template' => isset( $_POST['template'] ) ?  wp_kses_post( $_POST['template'] ) : ''
        ];

        $results = erp_crm_insert_save_replies( $data );

        if ( is_wp_error( $results ) ) {
            $this->send_error( $results->get_error_message() );
        }


        $this->send_success( stripslashes_deep($results) );
    }

    /**
     * Edit save replies
     *
     * @since 1.0
     *
     * @return json
     */
    public function edit_save_replies() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $query_id ) {
            $this->send_error( __( 'Somthing wrong, Please try later', 'erp' ) );
        }

        $result = erp_crm_get_save_replies_by_id( $query_id );

        if ( $result ) {
            $this->send_success( stripslashes_deep($result) );
        }

        $this->send_error( __( 'No results found', 'erp' ) );
    }

    /**
     * Delete Save replies
     *
     * @since 1.0
     *
     * @return json
     */
    public function delete_save_replies() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $query_id ) {
            $this->send_error( __( 'Somthing wrong, Please try later', 'erp' ) );
        }

        $resp = erp_crm_save_replies_delete( $query_id );

        if ( is_wp_error( $resp ) ) {
            $this->send_error( $resp->get_error_message() );
        }

        $this->send_success( __( 'Save reply item delete successfully', 'erp' ) );
    }

    /**
     * Load save replies
     *
     * @since 1.0
     *
     * @return json|object
     */
    public function load_save_replies() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-customer-feed' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        $template_id = isset( $_REQUEST['template_id'] ) ? intval( $_REQUEST['template_id'] ) : 0;
        $contact_id = isset( $_REQUEST['contact_id'] ) ? intval( $_REQUEST['contact_id'] ) : 0;

        $result = erp_crm_render_save_replies( $template_id, $contact_id );

        if ( is_wp_error( $result ) ) {
            $this->send_error( $result->get_error_message() );
        }

        $this->send_success( $result );
    }

    /**
     * Update contact tags
     * @since 1.3.6
     *
     */
    public function update_contact_tags(){
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'wp-erp-crm-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        if( empty(intval($_POST['contact_id']))){
            wp_send_json_error(['message' => __('could not find contact id', 'erp')]);
        }

        $tags = !empty( $_POST['tags'] )? explode(',', sanitize_text_field( wp_unslash( $_POST['tags'] ) ) ) : [];


        $tags = array_map('trim', $tags);
        $tags = array_map('sanitize_text_field', $tags);

        $inserted =  wp_set_object_terms(intval($_POST['contact_id']), $tags, 'erp_crm_tag');

        if( !is_wp_error($inserted) ){
            wp_send_json(['message' => __('tags updated successfully', 'erp')]);
        }else{
            wp_send_json(['message' => __('tags update failed please try again', 'erp')]);
        }
    }

    /**
     * Email Attatchment
     *
     * @return void
     */
    public function email_attachment() {

        $files          =   ( ! empty( $_FILES['files'] ) ) ? $_FILES['files'] : array(); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $wp_upload_dir  =   wp_upload_dir();
        $subdir         =   apply_filters( 'crm_attachmet_directory', 'crm-attachments' );
        $path           =   $wp_upload_dir['basedir'] . '/' . $subdir . '/';
        $attatchments   =   array();
        $file_names     =   array();

        //Create CRM attachments directory
        if ( !file_exists( $path ) ) {
            wp_mkdir_p($path);
        }

        foreach ( $files['name'] as $key => $file ) {
            $extension    = pathinfo( $file, PATHINFO_EXTENSION );
            $new_filename = $file;

            if ( file_exists( $path.$new_filename ) ) {
                $new_filename = uniqid()  . '.' . $extension;
            }

            if ( $files['error'][ $key ] == 0 ) {
                if ( move_uploaded_file( $files['tmp_name'][ $key ], $path.$new_filename ) ) {
                    $file_name      = $path.$new_filename;
                    $attatchments[] = [
                        'name' => $file,
                        'path' => $path . basename( $file_name ),
                        'slug' => $new_filename,
                    ];
                    $file_names[]   = $file;
                }
            }
        }

        wp_send_json_success( array(
            'url'   => $attatchments,
            'files' => $file_names
        ) );
    }

}
