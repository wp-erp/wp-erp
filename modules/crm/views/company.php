<?php
if ( isset( $_GET['filter_assign_contact' ] ) && !empty( $_GET['filter_assign_contact' ] ) ) {
    $id = intval( $_GET['filter_assign_contact'] );

    $custom_data = [
        'filter_assign_contact' => [
            'id' => $id,
            'display_name' => get_the_author_meta( 'display_name', $id )
        ],
        'searchFields' => array_keys( erp_crm_get_serach_key( 'company' ) )
    ];
} else {
    $custom_data = [
        'searchFields' => array_keys( erp_crm_get_serach_key( 'company' ) )
    ];
}
?>
<div class="wrap erp-crm-customer erp-crm-customer-listing" id="wp-erp">

    <h2><?php esc_attr_e( 'Company', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_add_contact' ) ): ?>
            <a href="#" @click.prevent="addContact( 'company', '<?php esc_attr_e( 'Add New Company', 'erp' ); ?>' )" id="erp-company-new" class="erp-contact-new add-new-h2" data-type="company" title="<?php esc_attr_e( 'Add New Company', 'erp' ); ?>"><?php esc_attr_e( 'Add New Company', 'erp' ); ?></a>
        <?php endif; ?>

        <a href="#" @click.prevent="addSearchSegment()" id="erp-contact-search-segmen" class="erp-search-segment add-new-h2" v-text="( showHideSegment ) ? '<?php esc_attr_e( 'Hide Search Segment', 'erp' ); ?>' : '<?php esc_attr_e( 'Add Search Segment', 'erp' ); ?>'"></a>
    </h2>

    <!-- Advance search filter vue component -->
    <advance-search :show-hide-segment="showHideSegment"></advance-search>

    <!-- vue table for displaying contact list -->
    <vtable v-ref:vtable
        wrapper-class="erp-crm-list-table-wrap"
        table-class="customers"
        row-checkbox-id="erp-crm-company-id-checkbox"
        row-checkbox-name="company_id"
        action="erp-crm-get-contacts"
        :wpnonce="wpnonce"
        page = "<?php echo esc_url_raw( add_query_arg( [ 'page' => 'erp-crm', 'section' => 'companies' ], admin_url( 'admin.php' ) ) ); ?>"
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
