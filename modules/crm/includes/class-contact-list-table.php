<?php
namespace WeDevs\ERP\CRM;

/**
 * Customer List table class
 *
 * @package weDevs|wperp
 */
class Contact_List_Table extends \WP_List_Table {

    private $counts = array();
    private $page_status = '';
    private $contact_type;
    private $page_type;

    function __construct( $type = null ) {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'customer',
            'plural'   => 'customers',
            'ajax'     => false
        ) );

        if ( $type ) {
            $this->contact_type = $type;
        }

        if ( $this->contact_type == 'contact' ) {
            $this->page_type = 'erp-sales-customers';
        }

        if ( $this->contact_type == 'company' ) {
            $this->page_type = 'erp-sales-companies';
        }
    }

    /**
     * Message to show if no contacts found
     *
     * @since 1.0
     *
     * @return void
     */
    function no_items() {
        echo sprintf( __( 'No %s found.', 'erp' ), $this->contact_type );
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

        $save_searches        = erp_crm_get_save_search_item();
        $crm_user_id          = ( isset( $_GET['filter_assign_contact'] ) ) ? $_GET['filter_assign_contact'] : '';
        $selected_save_search = ( isset( $_GET['erp_save_search'] ) ) ? $_GET['erp_save_search'] : '';

        if ( !empty( $crm_user_id ) ) {
            $user        = get_user_by( 'id', $crm_user_id );
            $user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')';
        }

        ?>

        <div class="alignleft actions">

            <label class="screen-reader-text" for="filter_by_save_searches"><?php _e( 'Filter By Saved Searches', 'erp' ) ?></label>
            <select style="width:250px;" name="filter_by_save_searches" class="selecttwo select2" id="erp_customer_filter_by_save_searches" data-placeholder="<?php _e( 'Select from saved searches', 'erp' ); ?>">
                <?php foreach ( $save_searches as $key => $searches ) : ?>
                    <option value=""></option>
                    <optgroup label="<?php echo $searches['name']; ?>" id="<?php echo strtolower( str_replace(' ', '-', $searches['name'] ) ); ?>">

                        <?php foreach ( $searches['options'] as $option_key => $option_value ) : ?>
                            <option value="<?php echo $option_value['id']; ?>" <?php selected( $selected_save_search, $option_value['id']); ?>><?php echo $option_value['text']; ?></option>
                        <?php endforeach ?>

                    </optgroup>

                <?php endforeach ?>
            </select>

            <select name="filter_assign_contact" id="erp-select-user-for-assign-contact" style="width: 250px; margin-bottom: 20px;" data-placeholder="<?php _e( 'Search a crm agent', 'erp' ) ?>">
                <option value=""><?php _e( 'Select a agent', 'erp' ); ?></option>
                <?php if ( $crm_user_id ): ?>
                    <option value="<?php echo $crm_user_id ?>" selected><?php echo $user_string; ?></option>
                <?php endif ?>
            </select>

            <?php
            submit_button( __( 'Filter', 'erp' ), 'secondary', 'filter_advance_search_contact', false, [ 'id' => 'erp-advance-filter-contact-btn'] );

            if ( $selected_save_search ) {
                $base_link = add_query_arg( [ 'page' => $this->page_type ], admin_url( 'admin.php' ) );
                echo '<a href="' . $base_link . '" class="button erp-reset-save-search-field" id="erp-reset-save-search-field">' . __( 'Reset', 'erp' ) . '</a>';
                echo '<a href="#" class="button erp-show-save-search-field" id="erp-show-save-search-field">' . __( 'Show Fields', 'erp' ) . '</a>';
            }

        echo '</div>';
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
    function column_default( $customer, $column_name ) {
        $customer_data = new \WeDevs\ERP\CRM\Contact( intval( $customer->id ) );

        $assign_contact_id = $customer_data->get_contact_owner();

        switch ( $column_name ) {
            case 'email':
                return erp_get_clickable( 'email', $customer_data->get_email() );

            case 'phone_number':
                return $customer_data->get_phone();

            case 'life_stages':
                return $customer_data->get_life_stage();

            case 'crm_owner':
                $base_link   = add_query_arg( [ 'filter_assign_contact' => $assign_contact_id ], admin_url( 'admin.php?page=' . $this->page_type ) );
                return !empty( $assign_contact_id ) ? '<a href="' . $base_link . '">' . get_the_author_meta( 'display_name', $assign_contact_id ) . '</a>' : '—';

            case 'created':
                return erp_format_date( $customer->created );

            default:
                return isset( $customer->$column_name ) ? $customer->$column_name : '';
        }
    }

    /**
     * Render current trggier bulk action
     *
     * @since 1.0
     *
     * @return string [type of filter]
     */
    public function current_action() {

        if ( isset( $_REQUEST['filter_advance_search_contact'] ) ) {
            return 'filter_by_save_searches';
        }

        if ( isset( $_REQUEST['customer_search'] ) ) {
            return 'customer_search';
        }

        return parent::current_action();
    }

    /**
     * Get sortable columns
     *
     * @since 1.0
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'created' => array( 'created', false ),
        );

        return $sortable_columns;
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
            'cb'           => '<input type="checkbox" />',
            'name'         => sprintf( '%s %s', ucfirst( $this->contact_type ), __( 'Name', 'erp' ) ),
            'email'        => __( 'Email', 'erp' ),
            'phone_number' => __( 'Phone', 'erp' ),
            'life_stages'  => __( 'Life Stage', 'erp' ),
            'crm_owner'    => __( 'Owner', 'erp' ),
            'created'      => __( 'Created at', 'erp' ),
        );

        return apply_filters( 'erp_hr_customer_table_cols', $columns );
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
    function column_name( $customer ) {
        $customer          = new \WeDevs\ERP\CRM\Contact( intval( $customer->id ), $this->contact_type );
        $actions           = array();
        $delete_url        = '';
        $view_url          = $customer->get_details_url();
        $data_hard         = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? 1 : 0;
        $delete_text       = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) ? __( 'Permanent Delete', 'erp' ) : __( 'Delete', 'erp' );
        $customer_name     = $customer->first_name .' '. $customer->last_name;
        $edit_title        = ( $customer->type == 'company' ) ? __( 'Edit this Company', 'erp' ) : __( 'Edit this customer', 'erp' );
        $view_title        = ( $customer->type == 'company' ) ? __( 'View this Company', 'erp' ) : __( 'View this customer', 'erp' );

        if ( current_user_can( 'erp_crm_edit_contact', $customer->id ) ) {
            $actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $customer->id, $edit_title, __( 'Edit', 'erp' ) );
        }

        $actions['view']   = sprintf( '<a href="%s" title="%s">%s</a>', $view_url, $view_title, __( 'View', 'erp' ) );

        if ( current_user_can( 'erp_crm_delete_contact', $customer->id, $data_hard ) ) {
            $actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" data-type="%s" data-hard=%d title="%s">%s</a>', $delete_url, $customer->id, $this->contact_type, $data_hard, __( 'Delete this item', 'erp' ), $delete_text );
        }

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            $actions['restore'] = sprintf( '<a href="%s" class="restoreCustomer" data-id="%d" data-type="%s" title="%s">%s</a>', $delete_url, $customer->id, $this->contact_type, __( 'Restore this item', 'erp' ), __( 'Restore', 'erp' ) );
        }

        return sprintf( '%4$s <a href="%3$s"><strong>%1$s</strong></a> %2$s', $customer->get_full_name(), $this->row_actions( $actions ), $customer->get_details_url(), $customer->get_avatar() );
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
            'delete'       => __( 'Move to Trash', 'erp' ),
            'assign_group' => __( 'Add to Contact group', 'erp' )
        );

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'trash' ) {
            unset( $actions['delete'] );

            $actions['permanent_delete'] = __( 'Permanent Delete', 'erp' );
            $actions['restore'] = __( 'Restore', 'erp' );
        }

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
            '<input type="checkbox" class="erp-crm-customer-id-checkbox" name="customer_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Set the filter listing views
     *
     * @since 1.0
     *
     * @return array
     */
    public function get_views() {
        $status_links  = array();
        $base_link     = remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'customer_search', 'filter_by_save_searches' ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $all_base_link = admin_url( 'admin.php?page=' . $this->page_type );

        foreach ( $this->counts as $key => $value ) {
            $class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', ( $key == 'all' ) ? add_query_arg( array( 'status' => $key ), $all_base_link ) : add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        // $status_links[ 'trash' ] = sprintf( '<a href="%s" class="status-trash">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'trash' ), $base_link ), __( 'Trash', 'erp' ), erp_crm_count_trashed_customers( $this->contact_type ) );

        return $status_links;
    }

    /**
     * Search form for lsit table
     *
     * @since 1.0
     *
     * @param  string $text
     * @param  string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && !$this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['status'] ) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr( $_REQUEST['status'] ) . '" />';
        }

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button( $text, 'button', 'customer_search', false, array( 'id' => 'search-submit' ) ); ?>
            <a href="#" class="button button-primary erp-advance-search-button" id="erp-advance-search-button"><span class="dashicons dashicons-admin-generic"></span><?php _e( 'Advanced Search', 'erp' ) ?></a>
        </p>
        <?php
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

        $per_page              = 2;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';

        // only ncessary because we have sample data
        $args = [
            'type'   => $this->contact_type,
            'offset' => $offset,
            'number' => $per_page,
        ];

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
                        'meta_key'   => 'life_stage',
                        'meta_value' => $_REQUEST['status']
                    ];
                }
            }
        }

        // Filter by assign contact ( contact owner )
        if ( isset( $_REQUEST['filter_assign_contact'] ) && ! empty( $_REQUEST['filter_assign_contact'] ) ) {
            $args['meta_query'] = [
                'meta_key' => '_assign_crm_agent',
                'meta_value' => $_REQUEST['filter_assign_contact']
            ];
        }

        // Total counting for customer type filter
        $this->counts = erp_crm_customer_get_status_count( $this->contact_type );

        // Prepare all item after all filtering
        $this->items  = erp_get_peoples( $args );

        // Render total customer according to above filter
        $args['count'] = true;
        $total_items = erp_get_peoples( $args );

        // Set pagination according to filter
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );
    }

}
