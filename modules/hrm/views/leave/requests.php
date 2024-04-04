<div class="wrap erp-hr-leave-requests">
    <h2>
    <?php esc_html_e( 'Leave Requests', 'erp' ); ?>
    <?php if ( current_user_can( 'erp_leave_create_request' ) ) { ?>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo erp_help_tip( esc_html__( 'To submit a new leave request you have to first create leave policy and leave entitlement for your employee.', 'erp' ) ); ?>
        <a href="<?php echo esc_url( add_query_arg( [ 'view' => 'new' ] ) ); ?>" class="add-new-h2"><?php esc_html_e( 'New Request', 'erp' ); ?></a>
    <?php } ?>
    </h2>

    <?php
    if ( ! empty( $_GET['error'] ) ) {
        $errors = new \WeDevs\ERP\ErpErrors( sanitize_text_field( wp_unslash( $_GET['error'] ) ) );
        echo wp_kses_post( $errors->display() );
    }
    ?>
    <div class="erp-hr-leave-requests-inner">
        <div class="list-table-wrap">
            <div class="list-table-inner">

                <form method="get">
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="leave">
                    <input type="hidden" name="sub-section" value="leave-requests">
                    <?php
                    $requests_table = new \WeDevs\ERP\HRM\LeaveRequestsListTable();
                    $requests_table->prepare_items();
                    if ( $requests_table->empty_list ) {
                        $requests_table->filter_option();
                        include WPERP_HRM_VIEWS . '/leave/zero_request.php';
                        return;
                    }
                    // $requests_table->search_box( __( 'Search Employee', 'erp' ), 'employee_search' );
                    $requests_table->views();
                    $requests_table->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    </div><!-- .erp-hr-leave-requests-inner -->
</div><!-- .wrap -->

<style>
    .erp-help-tip {
        bottom: 0.45rem;
        width: 15px;
    }
</style>
