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
        <h2>
            <?php esc_html_e( 'Pay Calendar', 'erp' ); ?>
            <a href="#" class="page-title-action erp-pro-preview-action" data-form="pro-form-add-calendar" data-form-title="<?php esc_attr_e( 'Add New Pay Calendar', 'erp' ); ?>"><?php esc_html_e( 'Add New Pay Calendar', 'erp' ); ?></a>
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
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
            <div class="postbox" style="margin: 0;">
                <div class="inside" style="padding: 20px;">
                    <h3 style="margin: 0 0 12px; font-size: 16px;"><?php echo esc_html( $cal['name'] ); ?></h3>
                    <p style="margin: 5px 0; color: #656668;">
                        <strong><?php esc_html_e( 'Type:', 'erp' ); ?></strong> <?php echo esc_html( $cal['type'] ); ?>
                    </p>
                    <p style="margin: 5px 0; color: #656668;">
                        <strong><?php esc_html_e( 'Total Employees:', 'erp' ); ?></strong> <?php echo esc_html( $cal['employees'] ); ?>
                    </p>
                    <div style="display: flex; gap: 8px; margin-top: 15px; align-items: center;">
                        <a href="#" class="button button-primary erp-pro-preview-action"><?php esc_html_e( 'Start Payrun', 'erp' ); ?></a>
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-add-calendar" data-form-title="<?php esc_attr_e( 'Edit Pay Calendar', 'erp' ); ?>" title="<?php esc_attr_e( 'Edit', 'erp' ); ?>"><span class="dashicons dashicons-edit" style="color: #0073aa;"></span></a>
                        <a href="#" class="erp-pro-preview-action" title="<?php esc_attr_e( 'Delete', 'erp' ); ?>"><span class="dashicons dashicons-trash" style="color: #a00;"></span></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Hidden form: Add/Edit Pay Calendar -->
<div id="pro-form-add-calendar" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Calendar Name', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Monthly Payroll', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Calendar Type', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Type —', 'erp' ); ?></option>
                    <option><?php esc_html_e( 'Weekly', 'erp' ); ?></option>
                    <option><?php esc_html_e( 'Bi-Weekly', 'erp' ); ?></option>
                    <option selected><?php esc_html_e( 'Monthly', 'erp' ); ?></option>
                    <option><?php esc_html_e( 'Hourly', 'erp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Assign Employees', 'erp' ); ?></label></th>
            <td>
                <select multiple style="min-width: 200px; height: 100px;">
                    <option>John Smith</option>
                    <option>Sarah Johnson</option>
                    <option>Mike Davis</option>
                    <option>Emily Chen</option>
                    <option>Alex Turner</option>
                </select>
                <p class="description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple employees.', 'erp' ); ?></p>
            </td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Calendar', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-calendar" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
