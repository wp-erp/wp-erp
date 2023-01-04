<?php

namespace WeDevs\ERP\CRM;

use WP_List_Table;

/**
 * Campaign List table class
 */
class CampaignListTable extends WP_List_Table {
    private $counts = [];

    public function __construct() {
        global $status, $page;

        parent::__construct( [
            'singular' => 'campaign',
            'plural'   => 'campaigns',
            'ajax'     => false,
        ] );
    }

    public function get_table_classes() {
        return [ 'widefat', 'fixed', 'striped', 'campaign-list-table', $this->_args['plural'] ];
    }

    /**
     * Message to show if no campaign found
     *
     * @return void
     */
    public function no_items() {
        esc_attr_e( 'No campaign found.', 'erp' );
    }

    /**
     * Default column values if no callback found
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $campaign, $column_name ) {
        switch ( $column_name ) {
            case 'name':

            case 'assigned_group':
                $groups = wp_list_pluck( $campaign->groups, 'name', 'id' );

                return implode( ', ', $groups );

            case 'created_at':
                return erp_format_date( $campaign->created_at );

            default:
                return isset( $campaign->$column_name ) ? $campaign->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'             => '<input type="checkbox" />',
            'name'           => __( 'Title', 'erp' ),
            'assigned_group' => __( 'Lists', 'erp' ),
            'created_at'     => __( 'Created At', 'erp' ),
        ];

        return apply_filters( 'erp_crm_campaign_table_cols', $columns );
    }

    /**
     * Render the campaign name column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_name( $campaign ) {
        $actions             = [];
        $delete_url          = '';

        $actions['edit']     = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', $delete_url, $campaign->id, __( 'Edit this Contact Group', 'erp' ), __( 'Edit', 'erp' ) );
        $actions['delete']   = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $delete_url, $campaign->id, __( 'Delete this Contact Group', 'erp' ), __( 'Delete', 'erp' ) );

        return sprintf( '<a href="%3$s"><strong>%1$s</strong></a> %2$s', $campaign->title, $this->row_actions( $actions ), '#' );
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = [
            'name'       => [ 'title', true ],
            'created_at' => [ 'created_at', true ],
        ];

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'campaign_delete'  => __( 'Delete', 'erp' ),
        ];

        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="campaign_id[]" value="%s" />', $item->id
        );
    }

    /**
     * Prepare the class items
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

        // Filter for order by
        if ( isset( $_REQUEST['orderby'] ) && ! empty( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
        }

        // Filter for order
        if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
            $args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
        }

        // Prepare all item after all filtering
        $this->items  = erp_crm_get_campaigns( $args );

        // Render total customer according to above filter
        $args['count'] = true;
        $total_items   = erp_crm_get_campaigns( $args );

        // Set pagination according to filter
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );
    }
}
