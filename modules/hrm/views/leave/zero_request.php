<?php
$requests_table = new \WeDevs\ERP\HRM\LeaveRequestsListTable();
$title_text     = esc_html__( 'Sit back and relax', 'erp' );
$desc           = esc_html__( 'You don’t have any pending leave request at this moment', 'erp' );
$img            = esc_url( WPERP_ASSETS . '/images/zero_request.svg' );
$button         = "<div id='wperp-filter-dropdown' class='wperp-filter-dropdown' style='margin: -46px 0 0 0;'>
            <div id='search-main'>
                <div class='filter-right'>
                    <a id='wperp-leave-filter-dropdown' class='wperp-btn btn--filter'>
                        <svg style='margin: 8px 10px 8px 10px;' width='17' height='12' viewBox='0 0 17 12' fill='none' xmlns='http://www.w3.org/2000/svg'>
                            <path d='M6.61111 11.6668H10.3889V9.77794H6.61111V11.6668ZM0 0.333496V2.22239H17V0.333496H0ZM2.83333 6.94461H14.1667V5.05572H2.83333V6.94461Z' fill='white' />
                        </svg>" . esc_html__( 'Filter Leave Requests', 'erp' ) . "&nbsp;&nbsp;&nbsp;
                    </a>
                </div>
            </div>
        </div>";
$get            = wp_unslash( $_GET ); // phpcs:ignore
if ( ! empty( $get['financial_year'] ) || ! empty( $get['employee_name'] ) || ! empty( $get['leave_policy'] ) || ! empty( $get['filter_leave_status'] ) || ! empty( $get['filter_leave_year'] ) ) {
    $title_text = esc_html__( 'No requests found!', 'erp' );
    $desc       = esc_html__( 'Try different search filters to filter leave requests as per your preference', 'erp' );
    $img        = esc_url( WPERP_ASSETS . '/images/no_request.svg' );
}
?>
<div class="zero-request">
    <img class="main-image" src="<?php echo esc_url($img); ?>" alt=''>
    <div class="title">
        <p>
            <?php echo esc_html( $title_text ); ?>
        </p>
    </div>
    <div class="description">
        <p>
            <?php echo esc_html( $desc ); ?>
        </p>
    </div>
    <div class="filter-button">
        <?php echo wp_kses( $button, [
            'div'  => [
                'id'    => [],
                'class' => [],
                'style' => [],
            ],
            'a'    => [
                'id'    => [],
                'class' => [],
                'href'  => [],
            ],
            'svg'  => [
                'style'   => [],
                'width'   => [],
                'height'  => [],
                'viewBox' => [],
                'fill'    => [],
                'xmlns'   => [],
            ],
            'path' => [
                'd'    => [],
                'fill' => [],
            ],
        ] ); ?>
    </div>
</div>
