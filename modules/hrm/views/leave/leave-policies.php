<div class="wrap erp-hr-leave-policy">
    <h2><?php esc_html_e( 'Leave Policies', 'erp' ); ?>
        <a href="<?php echo esc_url( erp_hr_new_policy_url() ); ?>" id="erp-leave-policy-new" class="add-new-h2">
            <?php esc_html_e( 'Add New', 'erp' ); ?>
        </a>

        <a href="<?php echo esc_url( erp_hr_new_policy_name_url() ); ?>" id="erp-leave-name-new" class="add-new-h2">
            <?php esc_html_e( 'View Leave Types', 'erp' ); ?>
        </a>
    </h2>

    <div class="list-table-wrap">
        <div class="list-wrap-inner">

            <form method="get" action="">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="leave">
                <input type="hidden" name="sub-section" value="policies">

                <?php
                $leave_policy = new \WeDevs\ERP\HRM\LeavePoliciesListTable();
                $leave_policy->prepare_items();
                $leave_policy->views();

                $leave_policy->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
