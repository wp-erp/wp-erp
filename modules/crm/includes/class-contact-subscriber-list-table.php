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
            'singular' => 'contactsubscriber',
            'plural'   => 'contactsubscribers',
            'ajax'     => false
        ) );
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {
        if ( $which != 'top' ) {
            return;
        }

        $groups         = erp_crm_get_contact_group_dropdown();
        $selected_group = ( isset( $_GET['filter_contact_group'] ) ) ? $_GET['filter_contact_group'] : 0;
        ?>
        <div class="alignleft actions">

            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Group', 'erp' ) ?></label>
            <select name="filter_contact_group" id="filter_contact_group">
                <?php foreach ( $groups as $key => $group ) : ?>
                    <option value="<?php echo $key; ?>" <?php selected( $selected_group, $key ); ?>><?php echo $group; ?></option>
                <?php endforeach ?>
            </select>

            <?php
            submit_button( __( 'Filter', 'erp' ), 'button', 'filter_group', false );
        echo '</div>';
    }

    /**
     * Message to show if no contacts found
     *
     * @since 1.0
     *
     * @return void
     */
    function no_items() {
        _e( 'No Subscriber contact found', 'erp' );
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
        $type = '';

        switch ( $column_name ) {
            case 'name':

            case 'email':
                return $contact->get_email();

            case 'type':

                if ( in_array( 'company', $contact->types ) ) {
                    $type = __( 'Company', 'erp' );
                }

                if(  in_array( 'contact', $contact->types ) ) {
                    $type = __( 'Contact', 'erp' );
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
                                    ? __( 'Subscribed on ', 'erp' )
                                    : __( 'Unsubscribed on ', 'erp' ),
                                ( $status[$key] == 'subscribe' )
                                    ? erp_format_date( $item[$key]['subscribe_at'] )
                                    : erp_format_date( $item[$key]['unsubscribe_at'] )
                                );
            $str .= sprintf( '%s<span class="%s-group erp-crm-tips" title="%s">%s</span>', ( $key != 0 ) ? ' , ': '', $status[$key], $subscribe_date, $data );
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
    function get_columns() {
        $columns = array(
            'cb'                     => '<input type="checkbox" />',
            'name'                   => __( 'Name', 'erp' ),
            'email'                  => __( 'Email', 'erp' ),
            'type'                   => __( 'Contact Type', 'erp' ),
            'group'                  => __( 'Group', 'erp' ),
            'subscription_status'    => __( 'Status', 'erp' )
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
        $contact    = new \WeDevs\ERP\CRM\Contact( $subscriber_contact->user_id );
        $actions    = array();
        $delete_url = '';
        $edit_url   = '';

        if ( current_user_can( 'erp_crm_delete_contact', $contact->id ) ) {
            $actions['edit']   = sprintf( '<a href="%s" data-id="%d" data-name="%s" title="%s">%s</a>', $edit_url, $subscriber_contact->user_id, $contact->get_full_name(), __( 'Edit this item', 'erp' ), __( 'Edit', 'erp' ) );
            $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $subscriber_contact->user_id, __( 'Delete this item', 'erp' ), __( 'Delete', 'erp' ) );
        }

        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $contact->get_full_name(), $this->row_actions( $actions ), erp_crm_get_details_url( $contact->id, $contact->types ) , $contact->get_avatar() );
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
            'delete'  => __( 'Delete', 'erp' )
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
        return sprintf(
            '<input type="checkbox" name="suscriber_contact_id[]" value="%s" />', $item->user_id
        );
    }

    /**
     * Subscribed or unsubscribed status for a contact
     *
     * @since 1.1.2
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_subscription_status( $item ) {
        if ( !empty( $item->data[0]->unsubscribe_at ) ) {
            return __( 'Unsubscribed', 'erp' );
        } else {
            return __( 'Subscribed', 'erp' );
        }
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

        // Filter for groups
        if ( isset( $_REQUEST['filter_contact_group'] ) && !empty( $_REQUEST['filter_contact_group'] ) ) {
            $args['group_id'] = $_REQUEST['filter_contact_group'];
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
