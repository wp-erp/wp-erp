<?php
$employee_table = new \WeDevs\ERP\HRM\Employee_List_Table();
$employee_table->prepare_items();
?>

<div class="wrap erp-hr-employees" id="wp-erp">

    <h2>
        <?php esc_html_e( 'People', 'erp' ); ?>

        <?php if ( current_user_can( 'erp_create_employee' ) ) : ?>
            <a href="#" id="erp-employee-new" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
        <?php endif; ?>

        <form method="get">
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="people">
            <input type="hidden" name="sub-section" value="employee">
            <?php $employee_table->search_box( __( 'Search', 'erp' ), 'erp-employee-search' ); ?>
        </form>
    </h2>

    <?php do_action( 'erp_hr_people_menu', 'employee' ); ?>

    <div class="list-table-wrap erp-hr-employees-wrap">
        <div class="list-table-inner erp-hr-employees-wrap-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="people">
                <input type="hidden" name="sub-section" value="employee">
                <?php

                if ( current_user_can( erp_hr_get_manager_role() ) ) {
                    $employee_table->views();
                }

                $employee_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

    <?php

    $employee_count = \WeDevs\ERP\HRM\Models\Employee::withTrashed()->count();

    if ( empty( $employee_count ) ) {
        ob_start();
        echo '<style>.erp-hr-employees-wrap{display: none;}</style>';
        include WPERP_HRM_VIEWS . '/employee/empty-employee.php';
        $output = ob_get_contents();
        ob_get_clean();
        echo $output;

        return;
    }
    ?>

</div>

<style>
    .tablenav {
        clear: none;
    }

    @media screen and (max-width: 782px) and (min-width: 326px) {
        .wperp-filter-dropdown {
            margin-top: -2px !important;
        }

        .wperp-filter-dropdown .wperp-btn {
            padding: 0 !important;
            font-size: 12px !important;
        }
    }
</style>