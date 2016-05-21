<?php
$advance_search_id =  isset( $_GET['erp_save_search' ] ) ? $_GET['erp_save_search' ] : 0;
$if_advance_search = ( isset( $_GET['erp_save_search' ] ) && $_GET['erp_save_search' ] == 0 ) ? true : false;
?>
<div class="wrap erp-crm-customer" id="wp-erp">

    <h2>
        <?php _e( 'Contact', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_add_contact' ) ): ?>
            <a href="#" @click.prevent="addContact('contact', '<?php _e( 'Add New Contact', 'erp' ); ?>' )" id="erp-customer-new" class="erp-contact-new add-new-h2"><?php _e( 'Add New Contact', 'erp' ); ?></a>
        <?php endif ?>
    </h2>

    <?php
    // $contact = new \WeDevs\ERP\CRM\Contact( 70 );
    // var_dump( $contact->to_array() );
    ?>

    <vtable v-ref:vtable
        wrapper-class="erp-crm-list-table-wrap"
        table-class="customers"
        row-checkbox-id="erp-crm-customer-id-checkbox"
        row-checkbox-name="customer_id"
        action="erp-crm-get-contacts"
        per-page="20"
        :fields=fields
        :item-row-actions=itemRowActions
        :search="search"
        :top-nav-filter="topNavFilter"
        :bulkactions="bulkactions"
        :extra-bulk-action = "extraBulkAction"
        :additional-params = "additionalParams"
    ></vtable>

</div>
