<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Payroll Settings.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll-settings" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2><?php esc_html_e( 'Payroll Settings', 'erp' ); ?></h2>

        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper" style="margin-top: 15px;">
            <a href="#" class="nav-tab nav-tab-active erp-pro-preview-action"><?php esc_html_e( 'Accounting Settings', 'erp' ); ?></a>
            <a href="#" class="nav-tab erp-pro-preview-action"><?php esc_html_e( 'Payment Settings', 'erp' ); ?></a>
            <a href="#" class="nav-tab erp-pro-preview-action"><?php esc_html_e( 'Pay Item Settings', 'erp' ); ?></a>
        </h2>

        <!-- Accounting Settings -->
        <div class="postbox" style="margin-top: 15px;">
            <div class="inside" style="padding: 15px;">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Account Head for Assets', 'erp' ); ?></label></th>
                        <td>
                            <select disabled style="min-width: 300px;">
                                <option><?php esc_html_e( '— Select Account —', 'erp' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select the account head for payroll assets.', 'erp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Account Head for Salary Reporting', 'erp' ); ?></label></th>
                        <td>
                            <select disabled style="min-width: 300px;">
                                <option><?php esc_html_e( '— Select Account —', 'erp' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select the account head for salary reporting.', 'erp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Account Head for Tax Reporting', 'erp' ); ?></label></th>
                        <td>
                            <select disabled style="min-width: 300px;">
                                <option><?php esc_html_e( '— Select Account —', 'erp' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select the account head for tax reporting.', 'erp' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Settings -->
        <div class="postbox" style="margin-top: 15px;">
            <h2 class="hndle"><span><?php esc_html_e( 'Payment Settings', 'erp' ); ?></span></h2>
            <div class="inside" style="padding: 15px;">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Payment Method', 'erp' ); ?></label></th>
                        <td>
                            <select disabled style="min-width: 200px;">
                                <option><?php esc_html_e( 'Bank Transfer', 'erp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Select a Bank', 'erp' ); ?></label></th>
                        <td>
                            <select disabled style="min-width: 200px;">
                                <option><?php esc_html_e( '— Select Bank —', 'erp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Pay Item Settings -->
        <div class="postbox" style="margin-top: 15px;">
            <h2 class="hndle"><span><?php esc_html_e( 'Pay Item Settings', 'erp' ); ?></span></h2>
            <div class="inside" style="padding: 15px;">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Pay Type', 'erp' ); ?></label></th>
                        <td>
                            <select disabled style="min-width: 200px;">
                                <option><?php esc_html_e( 'Earning', 'erp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php esc_html_e( 'Pay Item', 'erp' ); ?></label></th>
                        <td>
                            <input type="text" disabled class="regular-text" placeholder="<?php esc_attr_e( 'e.g. House Rent Allowance', 'erp' ); ?>" />
                        </td>
                    </tr>
                </table>

                <!-- Existing Pay Items Table -->
                <h4 style="margin-top: 20px;"><?php esc_html_e( 'Existing Pay Items', 'erp' ); ?></h4>
                <table class="widefat striped fixed" style="max-width: 600px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Pay Item', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Type', 'erp' ); ?></th>
                            <th style="width: 80px;"><?php esc_html_e( 'Action', 'erp' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pay_items = [
                            [ 'item' => 'Basic Salary',          'type' => 'Earning' ],
                            [ 'item' => 'House Rent Allowance',  'type' => 'Earning' ],
                            [ 'item' => 'Transport Allowance',   'type' => 'Earning' ],
                            [ 'item' => 'Medical Allowance',     'type' => 'Earning' ],
                            [ 'item' => 'Bonus',                 'type' => 'Earning' ],
                            [ 'item' => 'Provident Fund',        'type' => 'Deduction' ],
                            [ 'item' => 'Tax',                   'type' => 'Deduction' ],
                            [ 'item' => 'Insurance',             'type' => 'Deduction' ],
                        ];
                        foreach ( $pay_items as $pi ) :
                        ?>
                        <tr>
                            <td><?php echo esc_html( $pi['item'] ); ?></td>
                            <td>
                                <span style="background: <?php echo $pi['type'] === 'Earning' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $pi['type'] === 'Earning' ? '#166534' : '#991b1b'; ?>; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                                    <?php echo esc_html( $pi['type'] ); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="erp-pro-preview-action" title="<?php esc_attr_e( 'Delete', 'erp' ); ?>"><span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; color: #a00;"></span></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; margin-top: 15px;">
            <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Changes', 'erp' ); ?></button>
            <span class="erp-pro-save-notice">
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
                &mdash;
                <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-settings" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
            </span>
        </div>
    </div>
</div>
