<?php
$employee_table = new \WeDevs\ERP\HRM\EmployeeListTable();
$employee_table->prepare_items();
$employee_name = ! empty( $_GET['employee_name'] ) ? sanitize_text_field( wp_unslash( $_GET['employee_name'] ) ) : '';
?>

<div class="wrap erp-hr-employees" id="wp-erp">

    <h2>
        <?php esc_html_e( 'People > Employees', 'erp' ); ?>

        <?php if ( current_user_can( 'erp_create_employee' ) ) : ?>
            <a href="#" id="erp-employee-new" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>

            <div class="erp-btn-group">
                <button id="erp-hr-employee-import-csv"
                    data-title="<?php esc_attr_e( 'Import Employee', 'erp' ); ?>"
                    data-btn="<?php esc_attr_e( 'Import', 'erp' ); ?>">
                    <?php esc_html_e( 'Import', 'erp' ); ?>
                </button>

                <button id="erp-hr-employee-export-csv"
                    data-title="<?php esc_attr_e( 'Export Employee', 'erp' ); ?>"
                    data-btn="<?php esc_attr_e( 'Export', 'erp' ); ?>">
                    <?php esc_html_e( 'Export', 'erp' ); ?>
                </button>
            </div>
        <?php endif; ?>

        <form method="get">
            <div class='wperp-filter-panel-body'>
                <div class='input-component people_live_search'>
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="people">
                    <input type="hidden" name="sub-section" value="employee">
                    <?php $employee_table->search_box( __( 'Search', 'erp' ), 'erp-employee-search' ); ?>
                    <span id='live-employee-search'></span>
                </div>
            </div>
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
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;

        return;
    }
    ?>

</div>

<style>
    .tablenav {
        clear: none;
    }

    .wperp-filter-dropdown .wperp-btn.btn--cancel:hover {
        background-color: transparent !important;
        border: 1px solid #e2e2e2 !important;
    }
    .wperp-filter-dropdown .wperp-btn.btn--reset {
        color: #3c9fd4;
        margin-right: 5px
    }

    .wperp-filter-dropdown .wperp-btn.btn--reset:hover {
        color: #135e96 !important;
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
