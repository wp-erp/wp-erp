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

        // CRM Dashboard
        $this->action( 'wp_ajax_erp-crm-get-single-schedule-details', 'get_single_schedule_details' );

        // Save Replies in Settings page
        $this->action( 'wp_ajax_erp-crm-save-replies', 'save_template_save_replies' );
        $this->action( 'wp_ajax_erp-crm-edit-save-replies', 'edit_save_replies' );
        $this->action( 'wp_ajax_erp-crm-delete-save-replies', 'delete_save_replies' );
        $this->action( 'wp_ajax_erp-crm-load-save-replies-data', 'load_save_replies' );
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
        $this->verify_nonce( 'wp-erp-vue-table' );

        $contacts = [];

        // only ncessary because we have sample data
        $args = [
            'type'      => '',
            'offset'    => 0,
            'number'    => 20,
            'no_object' => true
        ];

        // Set type. By defaul it sets to contact :p
        if ( isset( $_REQUEST['type'] ) && ! empty( $_REQUEST['type'] ) ) {
            $args['type'] = $_REQUEST['type'];
        }

        // Filter Limit value
        if ( isset( $_REQUEST['number'] ) && ! empty( $_REQUEST['number'] ) ) {
            $args['number'] = $_REQUEST['number'];
        }

        // Filter offset value
        if ( isset( $_REQUEST['offset'] ) && ! empty( $_REQUEST['offset'] ) ) {
            $args['offset'] = $_REQUEST['offset'];
        }

        // Filter for serach
        if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
            $args['s'] = $_REQUEST['s'];
        }

        // Filter for order & order by
        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby']  = $_REQUEST['orderby'];
            $args['order']    = $_REQUEST['order'] ;
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
                    $args['meta_query'] = [
                        'meta_key' => 'life_stage',
                        'meta_value' => $_REQUEST['status']
                    ];
                }
            }
        }

        if ( isset( $_REQUEST['filter_assign_contact'] ) && ! empty( $_REQUEST['filter_assign_contact'] ) ) {
            $args['meta_query'] = [
                'meta_key' => '_assign_crm_agent',
                'meta_value' => $_REQUEST['filter_assign_contact']
            ];
        }

        if ( isset( $_REQUEST['erpadvancefilter'] ) && ! empty( $_REQUEST['erpadvancefilter'] ) ) {
            $args['erpadvancefilter'] = $_REQUEST['erpadvancefilter'];
        }

        $contacts['data']  = erp_get_peoples( $args );

        $args['count'] = true;
        $total_items = erp_get_peoples( $args );

        foreach ( $contacts['data'] as $key => $contact ) {
            $contact_owner    = [];
            $contact_owner_id = ( $contact['user_id'] ) ? get_user_meta( $contact['user_id'], '_assign_crm_agent', true ) : erp_people_get_meta( $contact['id'], '_assign_crm_agent', true );

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
            $contacts['data'][$key]['life_stage']    = ( $contact['user_id'] ) ? get_user_meta( $contact['user_id'], 'life_stage', true ) : erp_people_get_meta( $contact['id'], 'life_stage', true );
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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        unset( $_POST['_wpnonce'], $_POST['_wp_http_referer'], $_POST['action'] );

        if ( ! isset( $_POST['id'] ) ) {
            $this->send_error( __( 'No company or contact found', 'erp' ) );
        }

        $data = erp_crm_get_user_assignable_groups( $_POST['id'] );

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
        $this->verify_nonce( 'wp-erp-crm-customer-nonce' );

        unset( $_POST['_wp_http_referer'] );
        unset( $_POST['_wpnonce'] );
        unset( $_POST['action'] );

        $posted      = array_map( 'strip_tags_deep', $_POST );

        if ( ! $posted['id'] && ! current_user_can( 'erp_crm_add_contact' ) ) {
            $this->send_error( __( 'You don\'t have any permission to add new contact', 'erp' ) );
        }

        if ( $posted['id'] && ! current_user_can( 'erp_crm_edit_contact', $posted['id'] ) ) {
            $this->send_error( __( 'You don\'t have any permission to edit this contact', 'erp' ) );
        }

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

        if ( $posted['assign_to'] ) {
            $customer->update_meta( '_assign_crm_agent', $posted['assign_to'] );
        }

        $group_ids = ( isset( $posted['group_id'] ) && !empty( $posted['group_id'] ) ) ? $posted['group_id'] : [];

        erp_crm_edit_contact_subscriber( $group_ids, $customer_id );

        if ( isset( $posted['social'] ) ) {
            foreach ( $posted['social'] as $field => $value ) {
                $customer->update_meta( $field, $value );
            }
        }

        do_action( 'erp_crm_save_contact_data', $customer, $customer_id, $posted );

        $data = $customer->to_array();
        $statuses = erp_crm_customer_get_status_count( $posted['type'] );

        $this->send_success( [ 'data' => $data, 'statuses' => $statuses ] );
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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $ids         = [];
        $customer_id = ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) ? (array)$_REQUEST['id'] : intval( $_REQUEST['id'] );
        $hard        = isset( $_REQUEST['hard'] ) ? intval( $_REQUEST['hard'] ) : 0;
        $type        = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';

        // Check permission for trashing and permanent deleting contact;
        if ( is_array( $customer_id ) ) {
            foreach ( $customer_id as $contact_id ) {
                if ( ! current_user_can( 'erp_crm_delete_contact', $contact_id, $hard ) ) {
                    continue;
                }
                $ids[] = $contact_id;
            }
        } else {
            if ( ! current_user_can( 'erp_crm_delete_contact', $customer_id, $hard ) ) {
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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $customer_id = ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) ? (array)$_REQUEST['id'] : intval( $_REQUEST['id'] );
        $type        = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';

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
        $this->verify_nonce( 'wp-erp-crm-bulk-contact-subscriber' );

        $ids                = [];
        $contact_subscriber = [];
        $user_ids           = ( isset( $_POST['user_id'] ) && ! empty( $_POST['user_id'] ) ) ? explode(',', $_POST['user_id'] ) : [];
        $group_ids          = ( isset( $_POST['group_id'] ) && ! empty( $_POST['group_id'] ) ) ? $_POST['group_id'] : [];

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $id   = isset( $_POST['user_id'] ) ? $_POST['user_id'] : 0;
        $type = isset( $_POST['type'] ) ? $_POST['type'] : '';

        if ( ! $id ) {
            $this->send_error( __( 'User not found', 'erp' ) );
        }

        if ( empty( $type ) ) {
            $this->send_error( __( 'Type not found', 'erp' ) );
        }

        $people_obj = \WeDevs\ERP\Framework\Models\People::find( $id );
        $type_obj   = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $type )->first();
        $people_obj->assignType( $type_obj );

        $this->send_success();
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
        $this->verify_nonce( 'wp-erp-crm-customer-update-company-nonce' );

        $row_id     = isset( $_REQUEST['row_id'] ) ? intval( $_REQUEST['row_id'] ) : 0;
        $company_id = isset( $_REQUEST['company_id'] ) ? intval( $_REQUEST['company_id'] ) : 0;

        $result = erp_crm_customer_update_company( $row_id, $company_id );

        $this->send_success( __( 'Company has been updated successfully', 'erp' ) );
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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $term = isset( $_REQUEST['q'] ) ? stripslashes( $_REQUEST['q'] ) : '';

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
     * Search CRM contacts by keywords
     *
     * @since 1.1.0
     *
     * @return json
     */
    public function search_crm_contacts() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );
        $term = isset( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '';
        $types = isset( $_REQUEST['types'] ) ? $_REQUEST['types'] : '';

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        parse_str( $_POST['formData'], $output );

        if ( isset( $output['erp_select_assign_contact'] ) && empty( $output['erp_select_assign_contact'] ) ) {
            $this->send_error( __( 'Please select a user', 'erp' ) );
        }

        if ( empty( $output['assign_contact_id'] ) ) {
            $this->send_error( __( 'No contact found', 'erp' ) );
        }

        if ( $output['assign_contact_user_id'] ) {
            update_user_meta( $output['assign_contact_user_id'], '_assign_crm_agent', $output['erp_select_assign_contact'] );
        } else {
            erp_people_update_meta( $output['assign_contact_id'], '_assign_crm_agent', $output['erp_select_assign_contact'] );
        }

        $this->send_success( __( 'Assign to agent successfully', 'erp' ) );
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

        // Check permission
        if ( ! current_user_can( 'erp_crm_create_groups' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( empty( $_POST['group_name'] ) ) {
            $this->send_error( __('Contact Group Name must be required', 'erp' ) );
        }

        $data = [
            'id'          => ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) ? $_POST['id'] : '',
            'name'        => $_POST['group_name'],
            'description' => $_POST['group_description']
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
     * Get alreay assigned contact into subscriber
     *
     * @since 1.0
     *
     * @return json
     */
    public function check_assign_contact() {
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
        $this->verify_nonce( 'wp-erp-crm-contact-subscriber' );

        $data = [];

        $user_id = ( isset( $_POST['user_id'] ) && !empty( $_POST['user_id'] ) ) ? (int) $_POST['user_id'] : 0;
        $group_ids = ( isset( $_POST['group_id'] ) && !empty( $_POST['group_id'] ) ) ? (array) $_POST['group_id'] : [];

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $user_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! current_user_can( 'erp_crm_edit_contact', $user_id ) ) {
            $this->send_error( __( 'You don\'t have any permission to remove this contact from a group', 'erp' ) );
        }

        if ( ! $user_id ) {
            $this->send_error( __( 'No subscriber user found', 'erp' ) );
        }

        erp_crm_contact_subscriber_delete( $user_id );

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
        $this->verify_nonce( 'wp-erp-crm-contact-subscriber' );

        $user_id = isset( $_REQUEST['user_id'] ) ? intval( $_REQUEST['user_id'] ) : 0;
        $group_id = isset( $_POST['group_id'] ) ? $_POST['group_id'] : [];

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
        $this->verify_nonce( 'wp-erp-crm-customer-social-nonce' );

        // @TODO: check permission
        unset( $_POST['_wp_http_referer'] );
        unset( $_POST['_wpnonce'] );
        unset( $_POST['action'] );

        if ( ! $_POST['customer_id'] ) {
            $this->send_error( __( 'No customer found', 'erp' ) );
        }

        $customer_id = (int) $_POST['customer_id'];
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

                $erp_is_imap_active = erp_is_imap_active();

                if ( $erp_is_imap_active ) {
                    $imap_options = get_option( 'erp_settings_erp-email_imap', [] );

                    $reply_to = $imap_options['username'];
                    $headers .= "Reply-To: WP ERP <$reply_to>" . "\r\n";
                } else {
                    $from_name = erp_crm_get_email_from_name();
                    $reply_to  = erp_crm_get_email_from_address();

                    $headers .= "Reply-To: {$from_name} <$reply_to>" . "\r\n";
                }

                $query = [
                    'action' => 'erp_crm_track_email_opened',
                    'aid'    => $data['id'],
                ];
                $email_url  = add_query_arg( $query, admin_url('admin-ajax.php') );
                $img_url    = '<img src="' . $email_url . '" width="1" height="1" style="display:none;" />';

                $email_body = $postdata['message'] . $img_url;

                $message_id = md5( uniqid( time() ) ) . '.' . $postdata['user_id'] . '.' . $postdata['created_by'] . '.r1@' . $_SERVER['HTTP_HOST'];

                $custom_headers = [
                    "Message-ID" => "<{$message_id}>",
                    "In-Reply-To" => "<{$message_id}>",
                    "References" => "<{$message_id}>",
                ];

                // Send email a contact
                erp_mail( $contact->email, $postdata['email_subject'], $email_body, $headers, [], $custom_headers );

                do_action( 'erp_crm_save_customer_email_feed', $save_data, $postdata );

                if ( ! $data ) {
                    $this->send_error( __( 'Somthing is wrong, Please try later', 'erp' ) );
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
        $this->verify_nonce( 'wp-erp-crm-customer-feed' );

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        if ( ! $_POST['feed_id'] ) {
            $this->send_error( __( 'Feeds Not found', 'erp' ) );
        }

        erp_crm_customer_delete_activity_feed( $_POST['feed_id'] );

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $postdata = $_POST['form_data'];

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

        $result = erp_crm_insert_save_search( $data );

        if ( ! $result ) {
            $this->send_error( __( 'Search does not save', 'erp' ) );
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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $id = ( isset( $_POST['search_id'] ) && ! empty( $_POST['search_id'] ) ) ? $_POST['search_id'] : 0;

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        if ( ! ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }

        $id = ( isset( $_POST['filterId'] ) && ! empty( $_POST['filterId'] ) ) ? $_POST['filterId'] : 0;

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
        $this->verify_nonce( 'wp-erp-crm-nonce' );

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
        $this->verify_nonce( 'wp-erp-crm-save-replies' );

        $data = [
            'id'       => isset( $_POST['id'] ) ? $_POST['id'] : 0,
            'name'     => isset( $_POST['name'] ) ? $_POST['name'] : '',
            'subject'  => isset( $_POST['subject'] ) ? $_POST['subject'] : '',
            'template' => isset( $_POST['template'] ) ? $_POST['template'] : ''
        ];

        $results = erp_crm_insert_save_replies( $data );

        if ( is_wp_error( $results ) ) {
            $this->send_error( $results->get_error_message() );
        }

        $this->send_success( $results );
    }

    /**
     * Edit save replies
     *
     * @since 1.0
     *
     * @return json
     */
    public function edit_save_replies() {
        $this->verify_nonce( 'wp-erp-crm-nonce' );

        $query_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $query_id ) {
            $this->send_error( __( 'Somthing wrong, Please try later', 'erp' ) );
        }

        $result = erp_crm_get_save_replies_by_id( $query_id );

        if ( $result ) {
            $this->send_success( $result );
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

        $this->verify_nonce( 'wp-erp-crm-nonce' );

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
        $this->verify_nonce( 'wp-erp-crm-customer-feed' );

        $template_id = isset( $_REQUEST['template_id'] ) ? intval( $_REQUEST['template_id'] ) : 0;
        $contact_id = isset( $_REQUEST['contact_id'] ) ? intval( $_REQUEST['contact_id'] ) : 0;

        $result = erp_crm_render_save_replies( $template_id, $contact_id );

        if ( is_wp_error( $result ) ) {
            $this->send_error( $result->get_error_message() );
        }

        $this->send_success( $result );
    }

}
