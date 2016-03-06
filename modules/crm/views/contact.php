<?php $search_keys = erp_crm_get_serach_key(); ?>
<div class="wrap erp-crm-customer" id="wp-erp">

    <h2><?php _e( 'Contact', 'wp-erp' ); ?>
        <a href="#" id="erp-customer-new" class="erp-contact-new add-new-h2" data-type="contact" title="<?php _e( 'Add New Contact', 'wp-erp' ); ?>"><?php _e( 'Add New Contact', 'wp-erp' ); ?></a>
    </h2>

    <form action="" method="post" id="erp-crm-save-search-form">
        <input type="hidden" name="page" value="erp-sales-customers">

        <div id="erp-crm-save-search">

            <div class="erp-save-search-wrapper" id="erp-save-search-wrapper">
                <div class="erp-save-search-item" v-for="( index, searchFields ) in searchItem">
                    <save-search :search-fields="searchFields" :index="index" :total-search-item="totalSearchItem"></save-search>
                </div>
            </div>

            <div class="erp-save-serach-action">
                <input type="hidden" name="erp_crm_http_referer" value="<?php echo add_query_arg( ['page'=>'erp-sales-customers'], admin_url( 'admin.php' ) ); ?>">
                <?php wp_nonce_field( 'wp-erp-crm-save-search-nonce-action', 'wp-erp-crm-save-search-nonce' ); ?>

                <div class="saved-search-lists" v-show="!isNewSave">
                    <select name="" id="" class="select2">
                        <option value=""><?php _e( '--Select--', 'wp-erp' ) ?></option>
                        <optgroup label="Swedish Cars">
                            <option value="volvo">Volvo</option>
                            <option value="saab">Saab</option>
                        </optgroup>
                        <optgroup label="German Cars">
                            <option value="mercedes">Mercedes</option>
                            <option value="audi">Audi</option>
                        </optgroup>
                    </select>
                </div>
                <input type="submit" class="button" name="save_search_submit" value="<?php _e( 'Search', 'wp-erp' ); ?>" v-if="!isNewSave">

                <input type="text" name="erp_save_search_name" v-if="isNewSave">
                <input v-on:click.prevent="createNewSearch" type="submit" class="button button-primary" name="save_search_action" value="<?php _e( 'Save', 'wp-erp' ); ?>" v-if="isNewSave">
                <input v-on:click.prevent="cancelSaveSearch" type="submit" class="button button-primary" name="save_search_save_cancel" value="<?php _e( 'Cancel', 'wp-erp' ); ?>" v-if="isNewSave">
                <input v-on:click.prevent="saveSearch" type="submit" class="button button-primary" name="save_search_action" value="<?php _e( 'Save as new', 'wp-erp' ); ?>" v-if="!isNewSave">
            </div>

        </div>
    </form>

    <div class="list-table-wrap erp-crm-list-table-wrap">
        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-sales-customers">
                <?php
                $customer_table = new \WeDevs\ERP\CRM\Contact_List_Table( 'contact' );
                $customer_table->prepare_items();
                $customer_table->search_box( __( 'Search Contact', 'wp-erp' ), 'erp-customer-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
<?php
    //echo '<pre>'; var_dump( $_SERVER["QUERY_STRING"] ); echo '</pre>';

?>
