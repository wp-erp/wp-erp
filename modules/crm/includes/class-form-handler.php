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

        $crm = sanitize_title( esc_html__( 'CRM', 'erp' ) );
        add_action( "admin_init", [ $this, 'contact_groups_bulk_action' ] );
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
    public static function crm_permission_set( $post, $user ) {
        $enable_crm_manager = isset( $post['crm_manager'] ) ? filter_var( $post['crm_manager'], FILTER_VALIDATE_BOOLEAN ) : false;
        $enable_crm_agent   = isset( $post['crm_agent'] ) ? filter_var( $post['crm_agent'], FILTER_VALIDATE_BOOLEAN ) : false;

        $crm_manager_role = erp_crm_get_manager_role();
        $crm_agent_role = erp_crm_get_agent_role();

        // TODO::We are duplicating \WeDevs\ERP\CRM\User_Profile->update_user() process here,
        // which we shouldn't. We should update above method and use that.
        if ( current_user_can( $crm_manager_role ) ) {
            if ( $enable_crm_manager ) {
                $user->add_role( $crm_manager_role );
            } else {
                $user->remove_role( $crm_manager_role );
            }

            if ( $enable_crm_agent ) {
                $user->add_role( $crm_agent_role );
            } else {
                $user->remove_role( $crm_agent_role );
            }

        }
    }

    /**
     * Handle canonical url for contact|company page
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function handle_canonical_url() {
        if ( erp_is_contacts_page() ) {
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

        if ( current_user_can( 'erp_crm_agent' ) ) {
            return;
        }

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( empty( $_GET['section'] ) ||  $_GET['section'] != 'contact-groups' ) {
            return;
        }

        if ( isset( $_GET['groupaction'] ) && $_GET['groupaction'] == 'view-subscriber' ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-contactsubscribers' ) ) {
                return;
            }
        } else {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-contactgroups' ) ) {
                return;
            }
        }


        $customer_table = new \WeDevs\ERP\CRM\Contact_Subscriber_List_Table();
        $action         = $customer_table->current_action();

        if ( $action ) {

            $redirect_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
            $redirect = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'filter_group' ),  $redirect_uri );

            switch ( $action ) {

                case 'filter_group':
                    wp_redirect( $redirect );
                    exit();

                case 'contact_group_delete':
                    if ( isset( $_GET['contact_group'] ) && !empty( $_GET['contact_group'] ) ) {
                        $groups = array_map( 'sanitize_text_field', wp_unslash( $_GET['contact_group'] ) );
                        erp_crm_contact_group_delete( $groups );
                    }
                    wp_redirect( $redirect );
                    exit();

                case 'delete':

                    if ( isset( $_GET['suscriber_contact_id'] ) && !empty( $_GET['filter_contact_group'] ) ) {
                        erp_crm_contact_subscriber_delete( sanitize_text_field( wp_unslash( $_GET['suscriber_contact_id'] ) ), sanitize_text_field( wp_unslash( $_GET['filter_contact_group'] ) ) );
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
