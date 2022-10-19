<?php
$requests_table = new \WeDevs\ERP\HRM\LeaveRequestsListTable();
?>
<div class="zero-request">
    <img class="main-image" src="<?php echo esc_url( WPERP_ASSETS . '/images/zero_request.svg' ); ?>" alt=''>
    <div class="title">
        <p>
            <?php echo esc_html__( 'Sit back and relax', 'erp' ); ?>
        </p>
    </div>
    <div class="description">
        <p>
            <?php echo esc_html__( 'You don’t have any pending leave request at this moment', 'erp' ); ?>
        </p>
    </div>
    <div class="filter-button">
        <?php
        $requests_table->filter_option();
        ?>
    </div>
</div>
