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
        add_action( 'admin_head', [ $this, 'handle_canonical_url' ], 10 );
        add_action( 'erp_hr_after_employee_permission_set', [ $this, 'crm_permission_set' ], 10, 2 );
        add_filter( 'erp_get_people_pre_where_join', [ $this, 'contact_advance_filter' ], 10, 2 );
    }

    /**
     * Advance filter for contact and company
     *
     * @since 1.1.0
     *
     * @param  array $custom_sql
     * @param  array $args
     *
     * @return array
     */
    function contact_advance_filter( $custom_sql, $args ) {
        $postdata = $_REQUEST;
        $pep_fileds  = [ 'first_name', 'last_name', 'email', 'company', 'phone', 'mobile', 'other', 'fax', 'notes', 'street_1', 'street_2', 'city', 'postal_code', 'currency' ];

        if ( !isset( $postdata['erpadvancefilter'] ) || empty( $postdata['erpadvancefilter'] ) ) {
            return $custom_sql;
        }

        $or_query   = explode( '&or&', $postdata['erpadvancefilter'] );
        $allowed    = erp_crm_get_serach_key( $postdata['type'] );
        $query_data = [];

        if ( $or_query ) {
            foreach( $or_query as $or_q ) {
                parse_str( $or_q, $output );
                $serach_array = array_intersect_key( $output, array_flip( array_keys( $allowed ) ) );
                $query_data[] = $serach_array;
            }
        }

        if ( $query_data ) {

            foreach ( $query_data as $key=>$or_query ) {
                if ( $or_query ) {
                    $i=0;
                    $custom_sql['where'][] = ( $key == 0 ) ? "AND (" : 'OR (';
                    foreach ( $or_query as $field => $value ) {
                        if ( in_array( $field, $pep_fileds ) ) {
                            if ( $value ) {
                                $val = erp_crm_get_save_search_regx( $value );
                                $custom_sql['where'][] = "(";
                                $j=0;
                                foreach ( $val as $search_val => $search_condition ) {
                                    $addOr = ( $j == count( $val )-1 ) ? '' : " OR ";

                                    if ( 'has_not' == $search_val ) {
                                        $custom_sql['where'][] = "( $field is null OR $field = '' ) $addOr";
                                    } else if ( 'if_has' == $search_val ) {
                                        $custom_sql['where'][] = "( $field is not null AND $field != '' ) $addOr";
                                    } else {
                                        $custom_sql['where'][] = "$field $search_condition '$search_val' $addOr";
                                    }

                                    $j++;
                                }
                                $custom_sql['where'][] = ( $i == count( $or_query )-1 ) ? ")" : " ) AND";
                            }
                        } else if ( $field == 'country_state' ) {
                            $custom_sql['where'][] = "(";
                            $j=0;

                            foreach ( $value as $key => $search_value ) {
                                $search_condition_regx = erp_crm_get_save_search_regx( $search_value );
                                $condition = array_shift( $search_condition_regx );
                                $key_value = explode( ':', $search_value ); // seperate BAN:DHA to an array [ 0=>BAN, 1=>DHA]
                                $addOr = ( $j == count( $value )-1 ) ? '' : " OR ";

                                if ( count( $key_value ) > 1 ) {
                                    $custom_sql['where'][] = "( country $condition '$key_value[0]' AND state $condition '$key_value[1]')$addOr";
                                } else {
                                    $custom_sql['where'][] = "(country $condition '$key_value[0]')$addOr";
                                }

                                $j++;
                            }
                            $custom_sql['where'][] = ( $i == count( $or_query )-1 ) ? ")" : " ) AND";
                        }
                        $i++;
                    }
                    $custom_sql['where'][] = ")";
                }
            }
        }

        return $custom_sql;
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
