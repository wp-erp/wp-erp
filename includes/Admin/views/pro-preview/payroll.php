<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Payroll module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap erp hrm-dashboard hrm-payroll-dashboard">
        <h2><?php esc_html_e( 'Payroll Overview', 'erp' ); ?></h2>

        <!-- Top Row: Checklist + 3 Badge Cards -->
        <div style="display: flex; gap: 12px; margin-top: 10px; align-items: stretch;">
            <!-- Checklist -->
            <div class="postbox" style="margin: 0; min-width: 260px; flex-shrink: 0;">
                <h3 class="hndle" style="cursor: default;"><span><?php esc_html_e( 'Checklist', 'erp' ); ?></span></h3>
                <div class="inside" style="padding: 10px 12px;">
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px; line-height: 2;">
                        <li>
                            <span class="dashicons dashicons-yes-alt" style="color: #006505; margin-right: 2px;"></span>
                            <?php esc_html_e( 'Setup Wizard', 'erp' ); ?>
                        </li>
                        <li>
                            <span class="dashicons dashicons-yes-alt" style="color: #006505; margin-right: 2px;"></span>
                            <a href="#" class="erp-pro-preview-action" style="text-decoration: none;"><?php esc_html_e( 'Pay Calendar', 'erp' ); ?></a>
                        </li>
                        <li>
                            <span class="dashicons dashicons-marker" style="color: #ddd; margin-right: 2px;"></span>
                            <a href="#" class="erp-pro-preview-action" style="text-decoration: none;"><?php esc_html_e( 'Pay Run', 'erp' ); ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Total Pay Calendar Created -->
            <div class="postbox" style="flex: 1; margin: 0; display: flex; flex-direction: column;">
                <div class="inside" style="flex: 1; padding: 15px 20px;">
                    <h3 style="margin: 0; font-size: 27px; color: #1e1e1e; font-weight: bold;">3</h3>
                    <p style="color: #656668; margin: 8px 0 0; font-size: 13px;"><?php esc_html_e( 'Total Pay Calendar Created', 'erp' ); ?></p>
                </div>
                <div class="wp-ui-highlight" style="text-align: center; padding: 8px 0;">
                    <a href="#" class="erp-pro-preview-action" style="color: #fff; text-decoration: none; font-size: 13px;">
                        <?php esc_html_e( 'View Pay Calendar', 'erp' ); ?>
                        <span class="erp-pro-badge-nav" style="margin-left: 4px;">Pro</span>
                    </a>
                </div>
            </div>

            <!-- Pay Calendar Approved -->
            <div class="postbox" style="flex: 1; margin: 0; display: flex; flex-direction: column;">
                <div class="inside" style="flex: 1; padding: 15px 20px;">
                    <h3 style="margin: 0; font-size: 27px; color: #1e1e1e; font-weight: bold;">2</h3>
                    <p style="color: #656668; margin: 8px 0 0; font-size: 13px;"><?php esc_html_e( 'Pay Calendar Approved', 'erp' ); ?></p>
                </div>
                <div class="wp-ui-highlight" style="text-align: center; padding: 8px 0;">
                    <a href="#" class="erp-pro-preview-action" style="color: #fff; text-decoration: none; font-size: 13px;">
                        <?php esc_html_e( 'View Pay Run List', 'erp' ); ?>
                        <span class="erp-pro-badge-nav" style="margin-left: 4px;">Pro</span>
                    </a>
                </div>
            </div>

            <!-- Spent on Previous Month -->
            <div class="postbox" style="flex: 1; margin: 0; display: flex; flex-direction: column;">
                <div class="inside" style="flex: 1; padding: 15px 20px;">
                    <h3 style="margin: 0; font-size: 27px; color: #1e1e1e; font-weight: bold;">$4,150.00</h3>
                    <p style="color: #656668; margin: 8px 0 0; font-size: 13px;"><?php esc_html_e( 'Spent on Previous Month', 'erp' ); ?></p>
                </div>
                <div class="wp-ui-highlight" style="text-align: center; padding: 8px 0;">
                    <a href="#" class="erp-pro-preview-action" style="color: #fff; text-decoration: none; font-size: 13px;">
                        <?php esc_html_e( 'View Detail', 'erp' ); ?>
                        <span class="erp-pro-badge-nav" style="margin-left: 4px;">Pro</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Latest 5 Pay Run Records -->
        <div class="postbox" style="margin-top: 20px;">
            <h3 class="hndle" style="cursor: default;">
                <span>
                    <span class="dashicons dashicons-media-text" style="font-size: 16px; width: 16px; height: 16px; margin-right: 4px;"></span>
                    <?php esc_html_e( 'Latest 5 Pay Run Records', 'erp' ); ?>
                </span>
            </h3>
            <div class="inside" style="padding: 0;">
                <?php
                $records = [
                    [ 'period' => 'Mar 01 – Mar 31, 2026', 'run' => 'Monthly Payroll',    'date' => 'Mar 31, 2026', 'emps' => 12, 'net' => '$4,150.00', 'status' => 'Approved' ],
                    [ 'period' => 'Mar 01 – Mar 14, 2026', 'run' => 'Bi-Weekly Payroll',  'date' => 'Mar 14, 2026', 'emps' => 3,  'net' => '$1,820.00', 'status' => 'Approved' ],
                    [ 'period' => 'Feb 01 – Feb 28, 2026', 'run' => 'Monthly Payroll',    'date' => 'Feb 28, 2026', 'emps' => 12, 'net' => '$4,150.00', 'status' => 'Approved' ],
                    [ 'period' => 'Jan 01 – Jan 31, 2026', 'run' => 'Monthly Payroll',    'date' => 'Jan 31, 2026', 'emps' => 11, 'net' => '$3,890.00', 'status' => 'Approved' ],
                    [ 'period' => 'Dec 01 – Dec 31, 2025', 'run' => 'Monthly Payroll',    'date' => 'Dec 31, 2025', 'emps' => 11, 'net' => '$3,890.00', 'status' => 'Pending' ],
                ];
                ?>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li style="display: flex; background: #f5f5f5; border-bottom: 1px solid #eee; padding: 8px 12px; font-weight: 600; font-size: 13px;">
                        <span style="width: 20%;"><?php esc_html_e( 'Pay Period', 'erp' ); ?></span>
                        <span style="width: 15%;"><?php esc_html_e( 'Pay Run', 'erp' ); ?></span>
                        <span style="width: 15%;"><?php esc_html_e( 'Payment Date', 'erp' ); ?></span>
                        <span style="width: 10%;"><?php esc_html_e( 'Employees', 'erp' ); ?></span>
                        <span style="width: 15%;"><?php esc_html_e( 'Net Pay + Tax', 'erp' ); ?></span>
                        <span style="width: 15%;"><?php esc_html_e( 'Status', 'erp' ); ?></span>
                        <span style="width: 10%;"><?php esc_html_e( 'Action', 'erp' ); ?></span>
                    </li>
                    <?php foreach ( $records as $rec ) : ?>
                    <li style="display: flex; border-bottom: 1px solid #eee; border-left: 1px solid #eee; border-right: 1px solid #eee; padding: 8px 12px; font-size: 13px; align-items: center;">
                        <span style="width: 20%;"><?php echo esc_html( $rec['period'] ); ?></span>
                        <span style="width: 15%;"><?php echo esc_html( $rec['run'] ); ?></span>
                        <span style="width: 15%;"><?php echo esc_html( $rec['date'] ); ?></span>
                        <span style="width: 10%;"><?php echo esc_html( $rec['emps'] ); ?></span>
                        <span style="width: 15%;"><?php echo esc_html( $rec['net'] ); ?></span>
                        <span style="width: 15%;"><?php echo esc_html( $rec['status'] ); ?></span>
                        <span style="width: 10%;"><a href="#" class="erp-pro-preview-action"><span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px; color: #0073aa;"></span></a></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Payroll History of Current Month -->
        <div class="postbox" style="margin-top: 20px;">
            <h3 class="hndle" style="cursor: default;">
                <span>
                    <span class="dashicons dashicons-media-text" style="font-size: 16px; width: 16px; height: 16px; margin-right: 4px;"></span>
                    <?php esc_html_e( 'Payroll History of Current Month', 'erp' ); ?>
                </span>
            </h3>
            <div class="inside" style="padding: 15px;">
                <?php
                // Dummy daily payroll amounts for current month
                $daily_data = [
                    1 => 450, 2 => 0, 3 => 380, 4 => 520, 5 => 0,
                    6 => 0, 7 => 410, 8 => 390, 9 => 480, 10 => 350,
                    11 => 0, 12 => 0, 13 => 420, 14 => 1820, 15 => 370,
                    16 => 400, 17 => 460, 18 => 0, 19 => 0, 20 => 380,
                    21 => 410, 22 => 450, 23 => 390, 24 => 500, 25 => 0,
                    26 => 0, 27 => 430, 28 => 4150, 29 => 0, 30 => 0, 31 => 380,
                ];
                $max_val = max( $daily_data );
                ?>
                <!-- Bar chart matching Pro's Flot.js style -->
                <div style="position: relative; height: 200px; border-left: 1px solid #ccc; border-bottom: 1px solid #ccc; margin-left: 45px; margin-bottom: 5px;">
                    <!-- Y-axis labels -->
                    <span style="position: absolute; left: -45px; top: -5px; font-size: 11px; color: #555;">$<?php echo esc_html( number_format( $max_val ) ); ?></span>
                    <span style="position: absolute; left: -45px; top: 48%; font-size: 11px; color: #555;">$<?php echo esc_html( number_format( (int) ( $max_val / 2 ) ) ); ?></span>
                    <span style="position: absolute; left: -20px; bottom: -5px; font-size: 11px; color: #555;">0</span>
                    <!-- Grid lines -->
                    <div style="position: absolute; top: 50%; left: 0; right: 0; border-top: 1px solid #eee;"></div>

                    <!-- Bars area -->
                    <div style="display: flex; align-items: flex-end; height: 100%; padding: 0 5px; gap: 2px;">
                        <?php
                        $days = (int) gmdate( 't' );
                        for ( $d = 1; $d <= $days; $d++ ) :
                            $val     = isset( $daily_data[ $d ] ) ? $daily_data[ $d ] : 0;
                            $pct     = $max_val > 0 ? round( ( $val / $max_val ) * 100 ) : 0;
                            $bar_h   = max( $pct, ( $val > 0 ? 3 : 0 ) );
                        ?>
                        <div style="flex: 1; background: <?php echo $val > 0 ? 'rgba(35, 191, 170, 0.7)' : 'transparent'; ?>; height: <?php echo esc_attr( $bar_h ); ?>%; min-width: 0; border-top: <?php echo $val > 0 ? '1px solid #23bfaa' : 'none'; ?>;"></div>
                        <?php endfor; ?>
                    </div>

                    <!-- Dashed baseline -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; border-top: 2px dashed #5bc0de; pointer-events: none;"></div>
                </div>

                <!-- X-axis date labels -->
                <div style="display: flex; padding: 0 5px; gap: 2px; margin-top: 4px; margin-left: 45px;">
                    <?php
                    $year  = gmdate( 'Y' );
                    $month = gmdate( 'm' );
                    for ( $d = 1; $d <= $days; $d++ ) :
                    ?>
                    <div style="flex: 1; text-align: center; min-width: 0;">
                        <span style="font-size: 9px; color: #555; writing-mode: vertical-lr; transform: rotate(180deg); display: inline-block; white-space: nowrap;">
                            <?php echo esc_html( $year . '-' . $month . '-' . str_pad( $d, 2, '0', STR_PAD_LEFT ) ); ?>
                        </span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form template for New Pay Run -->
<div id="pro-form-new-payrun" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Pay Calendar', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Pay Calendar —', 'erp' ); ?></option>
                    <option>Monthly Payroll</option>
                    <option>Bi-Weekly Payroll</option>
                    <option>Weekly Payroll</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Pay Period Start', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Pay Period End', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Payment Date', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Employees', 'erp' ); ?></label></th>
            <td>
                <label><input type="radio" name="pay_employees" checked /> <?php esc_html_e( 'All Employees', 'erp' ); ?></label><br>
                <label><input type="radio" name="pay_employees" /> <?php esc_html_e( 'Select Department', 'erp' ); ?></label><br>
                <label><input type="radio" name="pay_employees" /> <?php esc_html_e( 'Select Individually', 'erp' ); ?></label>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Note', 'erp' ); ?></label></th>
            <td><textarea rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Optional note for this pay run...', 'erp' ); ?>"></textarea></td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Start Pay Run', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
