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
        add_action( 'admin_head', [ $this, 'handle_canonical_url' ], 10 );
        add_action( 'erp_hr_after_employee_permission_set', [ $this, 'crm_permission_set' ], 10, 2 );

        $crm = sanitize_title( __( 'CRM', 'erp' ) );
        add_action( "load-{$crm}_page_erp-sales-contact-groups", [ $this, 'contact_groups_bulk_action' ] );
    }

    /**
     * CRM Permission set
     *
     * @since 1.0.1
     *
     * @param  array $post
     * @param  object $user
     *
     * @return void
     */
    public function crm_permission_set( $post, $user ) {
        $user_profile = new \WeDevs\ERP\CRM\User_Profile();
        $post['crm_manager'] = isset( $post['crm_manager'] ) && $post['crm_manager'] == 'on' ? erp_crm_get_manager_role() : false;
        $post['crm_agent']   = isset( $post['crm_agent'] ) && $post['crm_agent'] == 'on' ? erp_crm_get_agent_role() : false;
        $user_profile->update_user( $user->ID, $post );
    }

    /**
     * Handle canonical url for contact|company page
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function handle_canonical_url() {
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'erp-sales-customers' || $_GET['page'] == 'erp-sales-companies' ) ) {
            ?>
                <script>
                    window.history.replaceState = false;
                </script>
            <?php
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
                        erp_crm_contact_subscriber_delete( $_GET['suscriber_contact_id'], $_GET['filter_contact_group'] );
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
