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
        add_action( 'load-crm_page_erp-sales-companies', array( $this, 'companies_bulk_action') );
        add_action( 'load-crm_page_erp-sales-contact-groups', array( $this, 'contact_groups_bulk_action') );
        add_action( 'admin_init', array( $this, 'handle_save_search_submit' ), 10 );
        add_action( 'admin_head', array( $this, 'handle_canonical_url' ), 10 );
        add_action( 'erp_hr_after_employee_permission_set', array( $this, 'employee_permission_set'), 10, 2 );
    }

    function employee_permission_set( $post, $user ) {
        $user_profile = new \WeDevs\ERP\CRM\User_Profile();
        $post['crm_manager'] = isset( $post['crm_manager'] ) && $post['crm_manager'] == 'on' ? erp_crm_get_manager_role() : false;
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

        if ( $_GET['page'] != 'erp-sales-customers'  ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-customers' ) ) {
            return;
        }

        $customer_table = new \WeDevs\ERP\CRM\Contact_List_Table('contact');
        $action         = $customer_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'customer_search', 'filter_by_save_searches', 'filter_by_crm_assign_agent' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'delete' :

                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        $data = [
                            'id' => $_GET['customer_id'],
                            'hard' => false,
                            'type' => 'contact'
                        ];

                        erp_delete_people( $data );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'permanent_delete' :
                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        $data = [
                            'id' => $_GET['customer_id'],
                            'hard' => true,
                            'type' => 'contact'
                        ];

                        erp_delete_people( $data );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'restore' :
                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        $data = [
                            'id' => $_GET['customer_id'],
                            'type' => 'contact'
                        ];

                        erp_restore_people( $data );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'filter_by_save_searches':
                    if ( isset( $_GET['filter_by_save_searches'] ) && !empty( $_GET['filter_by_save_searches'] ) ) {
                        $string   = erp_crm_get_search_by_already_saved( $_GET['filter_by_save_searches'] );
                        $redirect = add_query_arg( [ 'erp_save_search' => $_GET['filter_by_save_searches'] ], $redirect );
                        $redirect = $redirect. '&'. $string;
                    }

                    wp_redirect( $redirect );
                    exit();

                default:
                    wp_redirect( $redirect );
                    exit();

            }
        }
    }

    /**
     * Perform compnay bulk actions
     *
     * @since 1.0
     *
     * @return void
     */
    public function companies_bulk_action() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] != 'erp-sales-companies'  ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-customers' ) ) {
            return;
        }

        $customer_table = new \WeDevs\ERP\CRM\Contact_List_Table( 'company' );
        $action         = $customer_table->current_action();

        if ( $action ) {

            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'customer_search', 'filter_by_save_searches' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );

            switch ( $action ) {

                case 'delete' :

                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        $data = [
                            'id' => $_GET['customer_id'],
                            'hard' => false,
                            'type' => 'company'
                        ];

                        erp_delete_people( $data );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'permanent_delete' :
                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        $data = [
                            'id' => $_GET['customer_id'],
                            'hard' => true,
                            'type' => 'company'
                        ];

                        erp_delete_people( $data );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'restore' :
                    if ( isset( $_GET['customer_id'] ) && !empty( $_GET['customer_id'] ) ) {
                        $data = [
                            'id' => $_GET['customer_id'],
                            'type' => 'company'
                        ];

                        erp_restore_people( $data );
                    }

                    wp_redirect( $redirect );
                    exit();

                case 'filter_by_save_searches':
                    if ( isset( $_GET['filter_by_save_searches'] ) && !empty( $_GET['filter_by_save_searches'] ) ) {
                        $string   = erp_crm_get_search_by_already_saved( $_GET['filter_by_save_searches'] );
                        $redirect = add_query_arg( [ 'erp_save_search' => $_GET['filter_by_save_searches'] ], $redirect );
                        $redirect = $redirect. '&'. $string;
                    }

                    wp_redirect( $redirect );
                    exit();

                default:
                    wp_redirect( $redirect );
                    exit();

            }
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
