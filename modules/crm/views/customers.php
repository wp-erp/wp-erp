<div class="wrap erp-crm-customer" id="wp-erp">
    <?php

    $data = new \WeDevs\ERP\CRM\Customer();

    ?>
    <h2><?php _e( 'Customer', 'wp-erp' ); ?> <a href="#" id="erp-contact-new" class="add-new-h2">Add New</a></h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-sales-customers">
                <?php
                $employee_table = new \WeDevs\ERP\CRM\Customer_List_Table();
                $employee_table->prepare_items();
                $employee_table->search_box( __( 'Search Customer', 'wp-erp' ), 'erp-customer-search' );
                $employee_table->views();

                $employee_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>