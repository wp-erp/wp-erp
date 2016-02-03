<div class="wrap erp-crm-subscriber-contact" id="wp-erp">

    <h2><?php _e( 'Subscribed Contacts', 'wp-erp' ); ?>
        <a href="#" id="erp-new-subscriber-contact" class="erp-new-subscriber-contact add-new-h2" title="<?php _e( 'Add New Contact', 'wp-erp' ); ?>"><?php _e( 'Add New Contact', 'wp-erp' ); ?></a>
    </h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-sales-contact-groups">
                <?php
                $customer_table = new \WeDevs\ERP\CRM\Contact_Subscriber_List_Table();
                $customer_table->prepare_items();
                $customer_table->search_box( __( 'Search Contact Group', 'wp-erp' ), 'erp-crm-contact-group-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>