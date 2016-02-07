<?php
namespace WeDevs\ERP\CRM;

/**
 * Subscriber List table class
 *
 * @package weDevs|wperp
 */
class Contact_Subscriber_List_Table extends \WP_List_Table {

    private $counts = array();

    function __construct( $type = null ) {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'contactSubscriber',
            'plural'   => 'contactSubscribers',
            'ajax'     => false
        ) );
    }

    /**
     * Message to show if no contacts found
     *
     * @since 1.0
     *
     * @return void
     */
    function no_items() {
        _e( 'No Subscriber contact found', 'wp-erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @since 1.0
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $subscriber_contact, $column_name ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $subscriber_contact->user_id );

        switch ( $column_name ) {
            case 'name':

            case 'email':
                return $contact->get_email();

            case 'type':
                return ucfirst( $contact->type );

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
     * @param  array $item
     *
     * @return string
     */
    public function prepare_subscriber_group_data( $item ) {
        $status = wp_list_pluck(  $item, 'status' );
        $res = wp_list_pluck( $item, 'name' );
        $str = '';

        foreach( $res as $key=>$data ) {
            $subscribe_date = sprintf( '%s %s',
                                ( $status[$key] == 'subscribe' )
                                    ? __( 'Subscribed on ', 'wp-erp' )
                                    : __( 'Unsubscribed on ', 'wp-erp' ),
                                ( $status[$key] == 'subscribe' )
                                    ? erp_format_date( $item[$key]['subscribe_at'] )
                                    : erp_format_date( $item[$key]['unsubscribe_at'] )
                                );
            $str .= sprintf( '%s<span class="%s-group erp-crm-tips" title="%s">%s</span>', ( $key != 0 ) ? ' , ': '', $status[$key], $subscribe_date, $data );
        }

        return $str;
    }

    /**
     * Render current trggier bulk action
     *
     * @since 1.0
     *
     * @return string [type of filter]
     */
    public function current_action() {

        if ( isset( $_REQUEST['customer_search'] ) ) {
            return 'customer_search';
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
    function get_columns() {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'name'           => __( 'Name', 'wp-erp' ),
            'email'          => __( 'Email', 'wp-erp' ),
            'type'           => __( 'Contact Type', 'wp-erp' ),
            'group'          => __( 'Group', 'wp-erp' ),
        );

        return apply_filters( 'erp_crm_contact_subscribe_table_cols', $columns );
    }

    /**
     * Render the customer name column
     *
     * @since 1.0
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $subscriber_contact ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $subscriber_contact->user_id );
        $actions           = array();
        $delete_url        = '';
        $edit_url        = '';

        $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $edit_url, $subscriber_contact->user_id, __( 'Edit this item', 'wp-erp' ), __( 'Edit', 'wp-erp' ) );
        $actions['delete']   = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $subscriber_contact->user_id, __( 'Delete this item', 'wp-erp' ), __( 'Delete', 'wp-erp' ) );

        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $contact->get_full_name(), $this->row_actions( $actions ), erp_crm_get_details_url( $contact->id, $contact->type ) , $contact->get_avatar() );
    }

    /**
     * Set the bulk actions
     *
     * @since 1.0
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete'  => __( 'Move to Trash', 'wp-erp' )
        );

        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @since 1.0
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        // var_dump( $item );
        return sprintf(
            '<input type="checkbox" name="suscriber_contact_id[]" value="%s" />', $item->user_id
        );
    }

    /**
     * Prepare the class items
     *
     * @since 1.0
     *
     * @return void
     */
    function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        // only ncessary because we have sample data
        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        // Filter for serach
        if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
            $args['s'] = $_REQUEST['s'];
        }

        // Filter for order by
        if ( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
        }

        // Filter for order
        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = $_REQUEST['order'];
        }

        // Prepare all item after all filtering
        $this->items  = erp_crm_get_subscriber_contact( $args );

        // Render total customer according to above filter
        $args['count'] = true;
        $total_items = erp_crm_get_subscriber_contact( $args );

        // Set pagination according to filter
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );
    }

}