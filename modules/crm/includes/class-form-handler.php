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
        add_action( 'load-crm_page_erp-sales-contact-groups', [ $this, 'contact_groups_bulk_action' ] );
        // add_action( 'admin_init', array( $this, 'handle_save_search_submit' ), 10 );
        add_action( 'admin_head', [ $this, 'handle_canonical_url' ], 10 );
        add_action( 'erp_hr_after_employee_permission_set', [ $this, 'employee_permission_set' ], 10, 2 );
        add_filter( 'erp_get_people_pre_query', [ $this, 'contact_advance_filter' ], 10, 2 );
    }

    function contact_advance_filter( $sql, $args ) {
        $postdata = $_REQUEST;
        $allowed    = array_flip( array_keys( erp_crm_get_serach_key( 'crm_page_erp-sales-customers' ) ) ) + ['or'=>''];
        $serach_array = [];

        $serach_array = array_intersect_key( $postdata, $allowed );

        echo '<pre>';
        print_r( $postdata );
        echo '</pre>';

        $pep_fileds  = [ 'first_name', 'last_name', 'company', 'phone', 'mobile', 'other', 'fax', 'notes', 'street_1', 'street_2', 'city', 'postal_code', 'currency' ];

        // $filters_array = [];
        // foreach ( $serach_array as $filter_key => $filter_val ) {
        //     if ( $filter_key == 'or' ) {
        //         continue;
        //     }
        //     $filters_array[][$filter_key] = $filter_val;
        // }

        var_dump( $serach_array, $filters_array ); die();

        if ( $serach_array ) {
            $id=0;
            $sql['where'][] = "AND (";
            foreach ( $serach_array as $field => $value ) {
                if ( in_array( $field, $pep_fileds ) ) {
                    if ( $value ) {
                        $val = erp_crm_get_save_search_regx( $value );
                        $sql['where'][] = "(";
                        $j=0;
                        foreach ( $val as $search_val => $search_condition ) {
                            $addOr = ( $j == count( $val )-1 ) ? '' : " OR ";
                            $sql['where'][] = "people.$field $search_condition '$search_val' OR $field.meta_value $search_condition '$search_val'$addOr";
                            $j++;
                        }
                        $sql['where'][] = ( $i == count( $serach_array )-1 ) ? ")" : " ) AND";
                    }
                }
                $i++;
            }
            $sql['where'][] = ")";
        }

        echo( implode( ' ', $sql['where'] ) );

        die();

        // and ( people.first_name LIKE 's%' OR first_name.meta_value LIKE 's%' or people.first_name LIKE 'r%' OR first_name.meta_value LIKE 'r%' )

        // die();
    }

    function employee_permission_set( $post, $user ) {
        $user_profile = new \WeDevs\ERP\CRM\User_Profile();
        $post['crm_manager'] = isset( $post['crm_manager'] ) && $post['crm_manager'] == 'on' ? erp_crm_get_manager_role() : false;
        $post['crm_agent']   = isset( $post['crm_agent'] ) && $post['crm_agent'] == 'on' ? erp_crm_get_agent_role() : false;
        $user_profile->update_user( $user->ID, $post );
    }

    public function handle_canonical_url() {
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'erp-sales-customers' || $_GET['page'] == 'erp-sales-companies' ) ) {
            ?>
                <script>
                    window.history.replaceState = false;
                </script>
            <?php
        }
    }

    public function handle_save_search_submit() {

        if ( isset( $_POST['save_search_submit'] ) && wp_verify_nonce( $_POST['wp-erp-crm-save-search-nonce'], 'wp-erp-crm-save-search-nonce-action' ) ) {

            $query_args = [];
            $request_uri = $_POST['erp_crm_http_referer'];
            $search_string = erp_crm_get_save_search_query_string( $_POST );


            if ( ! empty( $_REQUEST['orderby'] ) ) {
                $query_args['orderby'] = esc_attr( $_REQUEST['orderby'] );
            }

            if ( ! empty( $_REQUEST['order'] ) ) {
                $query_args['order'] = esc_attr( $_REQUEST['order'] );
            }

            if ( ! empty( $_REQUEST['status'] ) ) {
                $query_args['status'] = esc_attr( $_REQUEST['status'] );
            }

            if ( ! empty( $_REQUEST['s'] ) ) {
                $query_args['s'] = esc_attr( $_REQUEST['s'] );
            }

            if ( $query_args ) {
                $request_uri = add_query_arg( $query_args, $request_uri );
            }

            wp_redirect( $request_uri .'&erp_save_search=0&'. $search_string );
            exit();
        }
    }

    /**
     * Handle contact subscriber bulk actions
     *
     * @since 1.0
     *
     * @return void
     */
    public function contact_groups_bulk_action() {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] != 'erp-sales-contact-groups' ) {
            return;
        }

        if ( isset( $_GET['groupaction'] ) && $_GET['groupaction'] == 'view-subscriber' ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-contactsubscribers' ) ) {
                return;
            }
        } else {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-contactgroups' ) ) {
                return;
            }
        }


        $customer_table = new \WeDevs\ERP\CRM\Contact_Subscriber_List_Table();
        $action         = $customer_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'filter_group' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'filter_group':
                    wp_redirect( $redirect );
                    exit();

                case 'contact_group_delete':
                    if ( isset( $_GET['contact_group'] ) && !empty( $_GET['contact_group'] ) ) {
                        erp_crm_contact_group_delete( $_GET['contact_group'] );
                    }
                    wp_redirect( $redirect );
                    exit();

                case 'delete':

                    if ( isset( $_GET['suscriber_contact_id'] ) && !empty( $_GET['suscriber_contact_id'] ) ) {
                        erp_crm_contact_subscriber_delete( $_GET['suscriber_contact_id'] );
                    }

                    wp_redirect( $redirect );
                    exit();

                default:
                    wp_redirect( $redirect );
                    exit();

            }
        }

    }

}
