<?php
$advance_search_id =  isset( $_GET['erp_save_search' ] ) ? $_GET['erp_save_search' ] : 0;
$if_advance_search = ( isset( $_GET['erp_save_search' ] ) && $_GET['erp_save_search' ] == 0 ) ? true : false;

if ( isset( $_GET['filter_assign_contact' ] ) && !empty( $_GET['filter_assign_contact' ] ) ) {
    $id = intval( $_GET['filter_assign_contact'] );

    $custom_data = [
        'filter_assign_contact' => [
            'id' => $id,
            'display_name' => get_the_author_meta( 'display_name', $id )
        ]
    ];
} else {
    $custom_data = new stdClass();
}

// var_dump()
?>
<div class="wrap erp-crm-customer" id="wp-erp">

    <h2><?php _e( 'Company', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_add_contact' ) ): ?>
            <a href="#" @click.prevent="addContact( 'company', '<?php _e( 'Add New Company', 'erp' ); ?>' )" id="erp-company-new" class="erp-contact-new add-new-h2" data-type="company" title="<?php _e( 'Add New Company', 'erp' ); ?>"><?php _e( 'Add New Company', 'erp' ); ?></a>
        <?php endif; ?>
    </h2>

    <vtable v-ref:vtable
        wrapper-class="erp-crm-list-table-wrap"
        table-class="customers"
        row-checkbox-id="erp-crm-company-id-checkbox"
        row-checkbox-name="company_id"
        action="erp-crm-get-contacts"
        page = "<?php echo add_query_arg( [ 'page' => 'erp-sales-companies' ], admin_url( 'admin.php' ) ); ?>"
        per-page="20"
        :fields=fields
        :item-row-actions=itemRowActions
        :search="search"
        :top-nav-filter="topNavFilter"
        :bulkactions="bulkactions"
        :extra-bulk-action = "extraBulkAction"
        :additional-params = "additionalParams"
        :custom-data = '<?php echo json_encode( $custom_data, JSON_UNESCAPED_UNICODE ); ?>'
    ></vtable>
</div>
