<?php
$advance_search_id =  isset( $_GET['erp_save_search' ] ) ? $_GET['erp_save_search' ] : 0;
$if_advance_search = ( isset( $_GET['erp_save_search' ] ) && $_GET['erp_save_search' ] == 0 ) ? true : false;

if ( isset( $_GET['filter_assign_contact' ] ) && !empty( $_GET['filter_assign_contact' ] ) ) {
    $id = intval( $_GET['filter_assign_contact'] );

    $custom_data = [
        'filter_assign_contact' => [
            'id'           => $id,
            'display_name' => get_the_author_meta( 'display_name', $id )
        ]
    ];
} else {
    $custom_data = new stdClass();
}
?>

    <?php //erp_crm_save_search_query_filter(null); ?>

<div class="wrap erp-crm-customer erp-crm-customer-listing" id="wp-erp" v-cloak>

    <h2>
        <?php _e( 'Contact', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_add_contact' ) ): ?>
            <a href="#" @click.prevent="addContact( 'contact', '<?php _e( 'Add New Contact', 'erp' ); ?>' )" id="erp-customer-new" class="erp-contact-new add-new-h2"><?php _e( 'Add New Contact', 'erp' ); ?></a>
        <?php endif ?>
    </h2>

    <div class="erp-advance-search-filter" id="erp-crm-save-search">
        <div class="erp-filter-search-wrapper">
            <button class="button button-primary" @click.prevent="filterAdvanceSearch()">Add Filter</button>
        </div>
    </div>

    <div class="erp-advance-search-filter" id="erp-crm-save-search">
        <div class="erp-filter-search-wrapper">

            <form action="" method="post" id="erp-crm-save-search-form">
                <input type="hidden" name="page" value="erp-sales-customers">

                <div class="erp-save-search-wrapper" id="erp-save-search-wrapper">
                    <div class="erp-save-search-item" v-for="( index, searchFields ) in searchItem">
                        <!-- <save-search :search-fields="searchFields" :index="index" :total-search-item="totalSearchItem"></save-search> -->
                    </div>
                </div>

                <div class="erp-save-serach-action">
                    <input type="hidden" name="erp_crm_http_referer" value="<?php echo add_query_arg( ['page'=>'erp-sales-customers'], admin_url( 'admin.php' ) ); ?>">
                    <?php wp_nonce_field( 'wp-erp-crm-save-search-nonce-action', 'wp-erp-crm-save-search-nonce' ); ?>

                    <div class="save-new-search-wrap" v-if="isNewSave">
                        <input type="text" id="erp_save_search_name" name="erp_save_search_name" placeholder="<?php _e( 'Search name..', 'erp' ); ?>">
                        <input type="hidden" name="erp_save_serach_make_global" value="0">
                        <label for="erp_save_serach_make_global">
                            <input type="checkbox" id="erp_save_serach_make_global" name="erp_save_serach_make_global" value="1">
                            <?php _e( 'Make as global for all', 'erp' ); ?>
                        </label>
                    </div>

                    <div class="save-new-search-wrap" v-if="isUpdateSaveSearch">
                        <input type="text" id="erp_save_search_name" name="erp_save_search_name" placeholder="<?php _e( 'Search name..', 'erp' ); ?>" v-model="updateSearchData.search_name">
                        <input type="hidden" name="erp_save_serach_make_global" value="0">
                        <label for="erp_save_serach_make_global">
                            <input type="checkbox" id="erp_save_serach_make_global" name="erp_save_serach_make_global" value="1" v-model="updateSearchData.global">
                            <?php _e( 'Make as global for all', 'erp' ); ?>
                        </label>
                        <input type="hidden" name="erp_update_save_search_id" value="<?php echo $advance_search_id; ?>">
                    </div>

                    <input v-on:click.prevent="createNewSearch" type="submit" class="button button-primary" name="save_search_action" value="<?php _e( 'Save', 'erp' ); ?>" v-if="isNewSave || isUpdateSaveSearch">
                    <input v-on:click.prevent="cancelSaveSearch" type="submit" class="button" name="save_search_save_cancel" value="<?php _e( 'Cancel', 'erp' ); ?>" v-if="isNewSave || isUpdateSaveSearch">
                    <input type="submit" class="button button-primary" name="save_search_submit" value="<?php _e( 'Search', 'erp' ); ?>" v-if="! ( isNewSave || isUpdateSaveSearch )">
                    <input type="submit" class="button" name="save_search_action" value="<?php _e( 'Save as new', 'erp' ); ?>" v-if="! ( isNewSave || isUpdateSaveSearch )">
                    <input v-on:click.prevent="updateSaveSearch" type="submit" class="button" data-save_search_id="<?php echo $advance_search_id; ?>" name="update_save_search_action" value="<?php _e( 'Update this search', 'erp' ); ?>" v-if="isSaveSearchFilter">

                    <input v-on:click.prevent="deleteSaveSearch" type="submit" class="button erp-right" data-save_search_id="<?php echo $advance_search_id; ?>" name="delete_save_search_action" value="<?php _e( 'Delete this Search', 'erp' ); ?>" v-if="isSaveSearchFilter">
                    <a v-show="!isSaveSearchFilter" href="<?php echo add_query_arg( [ 'page' => 'erp-sales-customers' ], admin_url( 'admin.php' ) ); ?>" class="button erp-right"><?php _e( 'Reset Filter', 'erp' ); ?></a>
                </div>

                <div class="clearfix"></div>
            </form>

        </div>
    </div>

    <!--
    <div class="erp-crm-contact-advance-filter">
        <button class="button button-primary"><i class="fa fa-search" aria-hidden="true"></i> Add Filter</button>
    </div> -->

    <vtable v-ref:vtable
        wrapper-class="erp-crm-list-table-wrap"
        table-class="customers"
        row-checkbox-id="erp-crm-customer-id-checkbox"
        row-checkbox-name="customer_id"
        action="erp-crm-get-contacts"
        page="<?php echo add_query_arg( [ 'page' => 'erp-sales-customers' ], admin_url( 'admin.php' ) ); ?>"
        per-page="20"
        :fields=fields
        :item-row-actions=itemRowActions
        :search="search"
        :top-nav-filter="topNavFilter"
        :bulkactions="bulkactions"
        :extra-bulk-action="extraBulkAction"
        :additional-params="additionalParams"
        :custom-data = '<?php echo json_encode( $custom_data, JSON_UNESCAPED_UNICODE ); ?>'
    ></vtable>

</div>
