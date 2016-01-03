<div class="wrap erp-crm-customer" id="wp-erp">

    <h2><?php _e( 'Contact', 'wp-erp' ); ?>
        <a href="#" id="erp-customer-new" class="erp-contact-new add-new-h2" data-type="contact" title="<?php _e( 'Add New Contact', 'wp-erp' ); ?>"><?php _e( 'Add New Contact', 'wp-erp' ); ?></a>
        <!-- <a href="#" id="erp-company-new" class="erp-contact-new add-new-h2" data-type="company" title="<?php _e( 'Add New Company', 'wp-erp' ); ?>"><?php _e( 'Add New Company', 'wp-erp' ); ?></a> -->
    </h2>

    <div class="list-table-wrap">
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