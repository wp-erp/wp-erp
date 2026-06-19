<?php

namespace WeDevs\ERP\CRM;

/**
 * Subscriber List table class
 */
class ContactSubscriberListTable extends \WP_List_Table {
    private $counts = [];
    private $page_status;

    private $subscription_statuses = [];

    /**
     * Class constructor
     *
     * @since 1.0
     * @since 1.1.17 Add `subscription_statuses`
     *
     * @return void
     */
    public function __construct() {
        global $status, $page;

        parent::__construct( [
            'singular' => 'contactsubscriber',
            'plural'   => 'contactsubscribers',
            'ajax'     => false,
        ] );

        $this->subscription_statuses = erp_crm_get_subscription_statuses();
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0
     *
     * @param string $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $groups          = erp_crm_get_contact_group_dropdown();
        $selected_group  = ( isset( $_GET['filter_contact_group'] ) ) ? sanitize_text_field( wp_unslash( $_GET['filter_contact_group'] ) ) : 0; ?>
            <div class="alignleft actions">

                <label class="screen-reader-text" for="new_role"><?php esc_html_e( 'Filter by Group', 'erp' ); ?></label>
                <select name="filter_contact_group" id="filter_contact_group">
                    <?php foreach ( $groups as $key => $group ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_group, $key ); ?>><?php echo wp_kses_post( $group ); ?></option>
                    <?php } ?>
                </select>
                <?php submit_button( esc_html__( 'Filter', 'erp' ), 'button', 'filter_group', false ); ?>
            </div>
        <?php
    }

    /**
     * Message to show if no contacts found
     *
     * @since 1.0
     *
     * @return void
     */
    public function no_items() {
        esc_html_e( 'No Subscriber contact found', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @since 1.0
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $subscriber_contact, $column_name ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $subscriber_contact->user_id );
        $type    = '';

        switch ( $column_name ) {
            case 'name':

            case 'email':
                return $contact->get_email();

            case 'type':

                if ( in_array( 'company', $contact->types ) ) {
                    $type = esc_html__( 'Company', 'erp' );
                }

                if (  in_array( 'contact', $contact->types ) ) {
                    $type = esc_html__( 'Contact', 'erp' );
                }

                return $type;

            case 'group':

                return $this->prepare_subscriber_group_data( $subscriber_contact->data );

            default:
                return isset( $subscriber_contact->$column_name ) ? $subscriber_contact->$column_name : '';
        }
    }

    /**
     * Prepare data for subscriber group
     *
     * @since 1.0
     *
     * @param array $item
     *
     * @return string
     */
    public function prepare_subscriber_group_data( $item ) {
        $status = wp_list_pluck(  $item, 'status' );
        $res    = wp_list_pluck( $item, 'name' );
        $str    = '';

        foreach ( $res as $key=>$data ) {
            $subscribe_date = sprintf( '%s %s',
                                ( $status[$key] == 'subscribe' )
                                    ? esc_html__( 'Subscribed on ', 'erp' )
                                    : esc_html__( 'Unsubscribed on ', 'erp' ),
                                ( $status[$key] == 'subscribe' )
                                    ? erp_format_date( $item[$key]['subscribe_at'] )
                                    : erp_format_date( $item[$key]['unsubscribe_at'] )
                                );
            $str .= sprintf( '%s<span class="%s-group erp-crm-tips" title="%s">%s</span>', ( $key != 0 ) ? ' , ' : '', $status[$key], $subscribe_date, $data );
        }

        return $str;
    }

    /**
     * Trigger currenct bulk action
     *
     * @since 1.0
     *
     * @return string
     */
    public function current_action() {
        if ( isset( $_REQUEST['filter_group'] ) ) {
            return 'filter_group';
        }

        return parent::current_action();
    }

    /**
     * Get the column names
     *
     * @since 1.0
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'                     => '<input type="checkbox" />',
            'name'                   => esc_html__( 'Name', 'erp' ),
            'email'                  => esc_html__( 'Email', 'erp' ),
            'type'                   => esc_html__( 'Contact Type', 'erp' ),
            'group'                  => esc_html__( 'Group', 'erp' ),
            'subscription_status'    => esc_html__( 'Status', 'erp' ),
        ];

        return apply_filters( 'erp_crm_contact_subscribe_table_cols', $columns );
    }

    /**
     * Render the customer name column
     *
     * @since 1.0
     *
     * @param object $item
     *
     * @return string
     */
    public function column_name( $subscriber_contact ) {
        $contact    = new \WeDevs\ERP\CRM\Contact( $subscriber_contact->user_id );
        $actions    = [];
        $edit_url   = '';
        $group_id   = isset( $_GET['filter_contact_group'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_contact_group'] ) ) : 0;
        $delete_url = add_query_arg( [ 'page'=>'erp-crm', 'section'=> 'contact-groups', 'action' => 'delete', 'group_id' => intval( $group_id ), 'id' => $subscriber_contact->user_id ], admin_url( 'admin.php' ) );

        if ( current_user_can( 'erp_crm_delete_contact', $contact->id ) ) {
            $actions['edit']   = sprintf( '<a href="%s" data-id="%d" data-name="%s" title="%s">%s</a>', $edit_url, $subscriber_contact->user_id, $contact->get_full_name(), esc_html__( 'Edit this item', 'erp' ), esc_html__( 'Edit', 'erp' ) );

            if( apply_filters( 'erp_crm_contact_can_delete_subscriber', true ) ) {
                $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" data-group_id="%d" title="%s">%s</a>', $delete_url, $subscriber_contact->user_id, $group_id, esc_html__( 'Delete this item', 'erp' ), esc_html__( 'Delete', 'erp' ) );
            }
        }

        $full_name = $contact->get_full_name();
        $full_name = ! empty( $full_name ) ? $full_name : '(' . esc_html__( 'No name', 'erp' ) . ')';

        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $full_name, $this->row_actions( $actions ), erp_crm_get_details_url( $contact->id, $contact->types ), $contact->get_avatar() );
    }

    /**
     * Set the bulk actions
     *
     * @since 1.0
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'delete'  => esc_html__( 'Delete', 'erp' ),
        ];

        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @since 1.0
     *
     * @param object $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="suscriber_contact_id[]" value="%s" />', $item->user_id
        );
    }

    /**
     * Subscribed or unsubscribed status for a contact
     *
     * @since 1.1.2
     * @since 1.1.17 Return status based on registered subscription statuses
     *               and return capitalized status by default
     *
     * @param object $item
     *
     * @return string
     */
    public function column_subscription_status( $item ) {
        if ( isset( $this->subscription_statuses[ $item->data[0]['status'] ] ) ) {
            return $this->subscription_statuses[ $item->data[0]['status'] ];
        } else {
            return ucwords( $item->data[0]->status );
        }
    }

    /**
     * Prepare the class items
     *
     * @since 1.0
     *
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';

        // only ncessary because we have sample data
        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        // Filter for serach
        if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
            $args['s'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
        }

        // Filter for order by
        if ( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
        }

        // Filter for order
        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
        }

        // Filter for groups
        if ( isset( $_REQUEST['filter_contact_group'] ) && !empty( $_REQUEST['filter_contact_group'] ) ) {
            $args['group_id'] = sanitize_text_field( wp_unslash( $_REQUEST['filter_contact_group'] ) );
        }

        $this->process_bulk_action();

        // Prepare all item after all filtering
        $this->items  = erp_crm_get_subscriber_contact( $args );

        // Render total customer according to above filter
        $args['count'] = true;
        $total_items   = erp_crm_get_subscriber_contact( $args );

        // Set pagination according to filter
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );
    }

    /**
     * Process bulk action
     */
    public function process_bulk_action() {
        // security check!
        if ( ! isset( $_REQUEST['_wpnonce'] ) || empty( $_REQUEST['_wpnonce'] ) ) {
            return;
        } else {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) ) {
                wp_die( esc_html__(  'Nonce verification failed!', 'erp' ) );
            }
        }

        $action = $this->current_action();

        switch ( $action ) {
            case 'delete':
                erp_crm_contact_subscriber_delete(
                    $_REQUEST['suscriber_contact_id'],
                    $_REQUEST['filter_contact_group']
                );

                break;

            default:
                return;
        }

        return;
    }
}
