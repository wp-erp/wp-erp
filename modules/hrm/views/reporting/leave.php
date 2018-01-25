<div class="wrap">
    <div class="erp-hr-report-area" style="width:95%">
        <h2><?php _e( 'Leave Report', 'erp' ); ?></h2>
        <form method="get">
            <input type="hidden" name="page" value="erp-hr-reporting">
            <input type="hidden" name="type" value="leaves">
            <?php
            $leaves = new \WeDevs\ERP\HRM\Leave_Report_Employee_Based();
            $leaves->prepare_items();
            $leaves->display();
            ?>
        </form>

    </div>
</div>
