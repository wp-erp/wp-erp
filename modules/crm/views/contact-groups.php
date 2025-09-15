<?php
$customer_table = new \WeDevs\ERP\CRM\ContactGroupListTable();
$customer_table->prepare_items();
?>

<div class="wrap erp-crm-contact-group" id="wp-erp">

    <h2>
        <?php esc_html_e( 'Contacts', 'erp' ); ?>
        <?php if ( current_user_can( 'erp_crm_create_groups' ) && apply_filters( 'erp_crm_user_can_create_groups', true ) ) { ?>
            <a href="#" id="erp-new-contact-group" class="erp-new-contact-group add-new-h2" title="<?php esc_attr_e( 'Add New Contact Group ', 'erp' ); ?>"><?php esc_html_e( 'Add New Contact Group', 'erp' ); ?></a>
        <?php } ?>

        <a href="<?php echo esc_url_raw( add_query_arg( [ 'page'=>'erp-crm', 'section' => 'contact', 'sub-section' => 'contact-groups', 'groupaction' => 'view-subscriber' ], admin_url( 'admin.php' ) ) ); ?>" class="add-new-h2" title="<?php esc_attr_e( 'View all subscriber contact', 'erp' ); ?>"><?php esc_html_e( 'View all subscriber', 'erp' ); ?></a>

        <form method="get">
            <input type="hidden" name="page" value="erp-crm">
            <input type="hidden" name="section" value="contact">
            <input type="hidden" name="sub-section" value="contact-groups">
            <?php $customer_table->search_box( __( 'Search', 'erp' ), 'erp-crm-contact-group-search' ); ?>
        </form>
    </h2>

    <?php do_action( 'erp_crm_contact_menu', 'contact-groups' ); ?>

    <div class="list-table-wrap erp-crm-contact-group-list-table-wrap">
        <div class="list-table-inner erp-crm-contact-group-list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-crm">
                <input type="hidden" name="section" value="contact">
                <input type="hidden" name="sub-section" value="contact-groups">
                <?php
                // $customer_table = new \WeDevs\ERP\CRM\ContactGroupListTable();
                // $customer_table->prepare_items();
                // $customer_table->search_box( __( 'Search Contact Group', 'erp' ), 'erp-crm-contact-group-search' );
                $customer_table->views();

                $customer_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
