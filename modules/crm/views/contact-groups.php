<div class="wrap erp-crm-contact-group" id="wp-erp">

    <h2><?php _e( 'Contact Groups', 'wp-erp' ); ?>
        <a href="#" id="erp-new-contact-group" class="erp-new-contact-group add-new-h2" title="<?php _e( 'Add New Contact Group ', 'wp-erp' ); ?>"><?php _e( 'Add New Contact Group', 'wp-erp' ); ?></a>
        <a href="<?php echo add_query_arg( [ 'page'=>'erp-sales-contact-groups', 'groupaction' => 'view-subscriber' ], admin_url('admin.php') ); ?>" class="add-new-h2" title="<?php _e( 'View all subscriber contact', 'wp-erp' ); ?>"><?php _e( 'View all subscriber', 'wp-erp' ); ?></a>
    </h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-sales-contact-groups">
                <?php
                $customer_table = new \WeDevs\ERP\CRM\Contact_Group_List_Table();
                $customer_table->prepare_items();
                $customer_table->search_box( __( 'Search Contact Group', 'wp-erp' ), 'erp-crm-contact-group-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>