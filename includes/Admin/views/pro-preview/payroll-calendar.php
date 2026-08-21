<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Payroll Pay Calendar.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll-calendar" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">

        <!-- Section 1: Calendar List -->
        <div id="erp-calendar-list-section">
            <h2>
                <?php esc_html_e( 'Pay Calendar', 'erp' ); ?>
                <a href="#" class="page-title-action erp-pro-preview-action"><?php esc_html_e( 'Add New Pay Calendar', 'erp' ); ?></a>
            </h2>

            <div style="margin-top: 20px; overflow: hidden;">
                <?php
                $calendars = [
                    [
                        'name'      => 'Monthly Payroll',
                        'type'      => 'Monthly',
                        'employees' => 8,
                    ],
                    [
                        'name'      => 'Bi-Weekly Payroll',
                        'type'      => 'Bi-Weekly',
                        'employees' => 3,
                    ],
                    [
                        'name'      => 'Hourly Contract',
                        'type'      => 'Hourly',
                        'employees' => 2,
                    ],
                ];
                foreach ( $calendars as $cal ) :
                ?>
                <div class="postbox" style="width: 256px; float: left; margin: 0 10px 10px 0;">
                    <h2 class="hndle" style="cursor: default; padding: 3px 12px; margin-bottom: -5px;"><span><?php echo esc_html( $cal['name'] ); ?></span></h2>
                    <div class="inside" style="padding: 10px 12px; margin: 0;">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 15px;">
                                <label><strong><?php esc_html_e( 'Calendar Name:', 'erp' ); ?></strong> <?php echo esc_html( $cal['name'] ); ?></label>
                            </li>
                            <li style="margin-bottom: 15px;">
                                <label><strong><?php esc_html_e( 'Calendar Type:', 'erp' ); ?></strong> <?php echo esc_html( $cal['type'] ); ?></label>
                            </li>
                            <li style="margin-bottom: 15px;">
                                <label><strong><?php esc_html_e( 'Total Employees:', 'erp' ); ?></strong> <?php echo esc_html( $cal['employees'] ); ?></label>
                            </li>
                        </ul>
                    </div>
                    <div style="border-top: 1px solid #eee; padding: 10px; display: flex; align-items: center;">
                        <a href="#" class="button erp-pro-preview-action" style="margin-right: 5px;">
                            <span class="dashicons dashicons-edit" style="font-size: 16px; width: 16px; height: 16px; line-height: 1.4;"></span>
                        </a>
                        <span class="button erp-pro-preview-action" style="margin-right: 5px; cursor: pointer;">
                            <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; line-height: 1.4;"></span>
                        </span>
                        <a href="#" class="button erp-pro-preview-action" style="margin-left: auto;">
                            <?php esc_html_e( 'Start Payrun', 'erp' ); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>
