<div class="wrap erp-crm-subscriber-contact" id="wp-erp">

    <h2><?php esc_attr_e( 'Contacts', 'erp' ); ?>
        <a href="#" id="erp-new-subscriber-contact" class="erp-new-subscriber-contact add-new-h2" title="<?php esc_attr_e( 'Assign a Contact', 'erp' ); ?>"><?php esc_attr_e( 'Assign a Contact', 'erp' ); ?></a>
        <?php $contact_group_url = add_query_arg( [ 'page' => 'erp-crm', 'section' => 'contact-groups' ], admin_url( 'admin.php' ) ); ?>
        <a href="<?php echo  esc_url_raw( $contact_group_url ); ?>" class="add-new-h2" title="<?php esc_attr_e( 'Back to Contact Group', 'erp' ); ?>"><?php esc_attr_e( 'Back to Contact Group', 'erp' ); ?></a>
    </h2>

    <div class="list-table-wrap erp-crm-subscriber-contact-list-table-wrap">
        <div class="list-table-inner erp-crm-subscriber-contact-list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-crm">
                <input type="hidden" name="section" value="contact-groups">
                <input type="hidden" name="groupaction" value="view-subscriber">
                <?php
                $customer_table = new \WeDevs\ERP\CRM\Tag_Contacts_List_Table();
                $customer_table->prepare_items();
                // $customer_table->search_box( __( 'Search Contact Group', 'erp' ), 'erp-crm-contact-group-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
