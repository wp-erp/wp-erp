<?php
namespace WeDevs\ERP\CRM;

/**
 * Customer List table class
 *
 * @package weDevs|wperp
 */
class Contact_Group_List_Table extends \WP_List_Table {

    private $counts = array();

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'contactgroup',
            'plural'   => 'contactgroups',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'contact-group-list-table', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        esc_attr_e( 'No contact group found.', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @since 1.0.0
     * @since 1.2.0 Add unconfirmed column value
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $contact_group, $column_name ) {
        $data = '';

        switch ( $column_name ) {
            case 'subscribed':
                $data = $contact_group->subscriber;
                break;

            case 'unconfirmed':
                $data = $contact_group->unconfirmed;
                break;

            case 'unsubscribed':
                $data = $contact_group->unsubscriber;
                break;

            case 'created_at':
                $data = erp_format_date( $contact_group->created_at );
                break;

            default:
                $data = isset( $contact_group->$column_name ) ? $contact_group->$column_name : '';
                break;
        }

        return $data;
    }

    /**
     * Get the column names
     *
     * @since 1.0.0
     * @since 1.2.0 Add unconfirmed column
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'           => '<input type="checkbox" />',
            'name'         => __( 'Name', 'erp' ),
            'subscribed'   => __( 'Subscribed', 'erp' ),
            'unconfirmed'  => __( 'Unconfirmed', 'erp' ),
            'unsubscribed' => __( 'Unsubscribed', 'erp' ),
            'created_at'   => __( 'Created At', 'erp' )
        );

        if ( current_user_can( 'erp_crm_agent' ) ) {
            unset( $columns['cb'] );
        }


        return apply_filters( 'erp_crm_contact_group_table_cols', $columns );
    }

    /**
     * Render the designation name column
     *
     * @since 1.0.0
     * @since 1.2.2 Add private icon
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_name( $contact_group ) {

        $actions             = array();
        $delete_url          = '';
        $view_subscriber_url = add_query_arg( [ 'page'=>'erp-crm', 'section'=> 'contact-groups', 'groupaction' => 'view-subscriber', 'filter_contact_group' => $contact_group->id ], admin_url( 'admin.php' ) );

        if ( current_user_can( 'erp_crm_edit_groups' ) ) {
            $actions['edit'] = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $contact_group->id, __( 'Edit this Contact Group', 'erp' ), __( 'Edit', 'erp' ) );
        }

        $actions['view-subscriber'] = sprintf( '<a href="%s" title="%s">%s</a>', $view_subscriber_url, __( 'View Subscriber in this group', 'erp' ), __( 'View Subscriber', 'erp' ) );

        if ( current_user_can( 'erp_crm_edit_groups' ) ) {
            $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $contact_group->id, __( 'Delete this Contact Group', 'erp' ), __( 'Delete', 'erp' ) );
        }

        if ( $contact_group->private ) {
            $private = '<span title="' . __( 'This group is private', 'erp' ) . '" class="dashicons dashicons-hidden"></span>';
        } else {
            $private = '';
        }

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %4$s %2$s', $contact_group->name, $this->row_actions( $actions ), $view_subscriber_url, $private );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'       => array( 'name', true ),
            'created_at' => array( 'created_at', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        if ( current_user_can( 'erp_crm_agent' ) ) {
            return;
        }
        $actions = array(
            'contact_group_delete'  => __( 'Delete', 'erp' ),
        );
        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="contact_group[]" value="%s" />', $item->id
        );
    }

    /**
     * Prepare the class items
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
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';

        // only ncessary because we have sample data
        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        // Filter for order by
        if ( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
        }

        // Filter for order
        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
        }

        // Filter for search
        if ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) ) {
            $args['s'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
        }

        // Prepare all item after all filtering
        $this->items  = erp_crm_get_contact_groups( $args );

        // Render total customer according to above filter
        $args['count'] = true;
        $total_items = erp_crm_get_contact_groups( $args );

        // Set pagination according to filter
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );
    }

}
