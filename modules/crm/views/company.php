<?php
$advance_search_id =  isset( $_GET['erp_save_search' ] ) ? $_GET['erp_save_search' ] : 0;
$if_advance_search = ( isset( $_GET['erp_save_search' ] ) && $_GET['erp_save_search' ] == 0 ) ? true : false;
?>
<div class="wrap erp-crm-customer" id="wp-erp">

    <h2><?php _e( 'Company', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_add_contact' ) ): ?>
            <a href="#" id="erp-company-new" class="erp-contact-new add-new-h2" data-type="company" title="<?php _e( 'Add New Company', 'erp' ); ?>"><?php _e( 'Add New Company', 'erp' ); ?></a>
        <?php endif; ?>
    </h2>

    <div class="erp-advance-search-filter <?php echo !$if_advance_search ? 'erp-hide' : ''; ?>" id="erp-crm-save-search" v-cloak>
        <div class="erp-filter-search-wrapper">

            <form action="" method="post" id="erp-crm-save-search-form">
                <input type="hidden" name="page" value="erp-sales-customers">

                <div class="erp-save-search-wrapper" id="erp-save-search-wrapper">
                    <div class="erp-save-search-item" v-for="( index, searchFields ) in searchItem">
                        <save-search :search-fields="searchFields" :index="index" :total-search-item="totalSearchItem"></save-search>
                    </div>
                </div>

                <div class="erp-save-serach-action">
                    <input type="hidden" name="erp_crm_http_referer" value="<?php echo add_query_arg( ['page'=>'erp-sales-companies'], admin_url( 'admin.php' ) ); ?>">
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
                    <input v-on:click.prevent="saveSearch" type="submit" class="button" name="save_search_action" value="<?php _e( 'Save as new', 'erp' ); ?>" v-if="! ( isNewSave || isUpdateSaveSearch )">
                    <input v-on:click.prevent="updateSaveSearch" type="submit" class="button" data-save_search_id="<?php echo $advance_search_id; ?>" name="update_save_search_action" value="<?php _e( 'Update this search', 'erp' ); ?>" v-if="isSaveSearchFilter">

                    <input v-on:click.prevent="deleteSaveSearch" type="submit" class="button erp-right" data-save_search_id="<?php echo $advance_search_id; ?>" name="delete_save_search_action" value="<?php _e( 'Delete this Search', 'erp' ); ?>" v-if="isSaveSearchFilter">

                    <a v-show="!isSaveSearchFilter" href="<?php echo add_query_arg( [ 'page' => 'erp-sales-companies' ], admin_url( 'admin.php' ) ); ?>" class="button erp-right"><?php _e( 'Reset Filter', 'erp' ); ?></a>
                </div>

                <div class="clearfix"></div>

            </form>

        </div>
    </div>

    <div class="list-table-wrap erp-crm-list-table-wrap">
        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-sales-companies">
                <?php
                $customer_table = new \WeDevs\ERP\CRM\Contact_List_Table( 'company' );
                $customer_table->prepare_items();
                $customer_table->search_box( __( 'Search Company', 'erp' ), 'erp-customer-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
