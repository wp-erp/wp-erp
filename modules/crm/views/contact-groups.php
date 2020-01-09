<div class="wrap erp-crm-contact-group" id="wp-erp">

    <h2><?php esc_attr_e( 'Contact Groups', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_create_groups' ) ): ?>
            <a href="#" id="erp-new-contact-group" class="erp-new-contact-group add-new-h2" title="<?php esc_attr_e( 'Add New Contact Group ', 'erp' ); ?>"><?php esc_attr_e( 'Add New Contact Group', 'erp' ); ?></a>
        <?php endif ?>

        <a href="<?php echo esc_url_raw( add_query_arg( [ 'page'=>'erp-crm', 'section' => 'contact-groups', 'groupaction' => 'view-subscriber' ], admin_url('admin.php') ) ); ?>" class="add-new-h2" title="<?php esc_attr_e( 'View all subscriber contact', 'erp' ); ?>"><?php esc_attr_e( 'View all subscriber', 'erp' ); ?></a>
    </h2>

    <div class="list-table-wrap erp-crm-contact-group-list-table-wrap">
        <div class="list-table-inner erp-crm-contact-group-list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-crm">
                <input type="hidden" name="section" value="contact-groups">
                <?php
                $customer_table = new \WeDevs\ERP\CRM\Contact_Group_List_Table();
                $customer_table->prepare_items();
                $customer_table->search_box( __( 'Search Contact Group', 'erp' ), 'erp-crm-contact-group-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
