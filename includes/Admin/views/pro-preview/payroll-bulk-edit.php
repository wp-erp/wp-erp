<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of Bulk Pay Item Edit.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll-bulk-edit" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div id="bulk_edit_items" class="wrap payroll-report-container erp">
        <h1><?php esc_html_e( 'Bulk pay item edit', 'erp' ); ?></h1>

        <div id="bulk_edit_items_container" class="erp-grid-container">
            <div class="row">
                <div class="col-6">
                    <div class="postbox">
                        <div class="inside">
                            <div id="bulk_edit_items_wrapper" class="information-container">
                                <div id="candidate-overview-zone">
                                    <select disabled class="erp-select-field">
                                        <option><?php esc_html_e( 'Travel Allowance', 'erp' ); ?></option>
                                    </select>

                                    <select disabled class="erp-select-field">
                                        <option><?php esc_html_e( 'All Department', 'erp' ); ?></option>
                                    </select>

                                    <select disabled class="erp-select-field">
                                        <option><?php esc_html_e( 'All Designations', 'erp' ); ?></option>
                                    </select>

                                    <input disabled autocomplete="off" type="search" value="" placeholder="<?php esc_attr_e( 'Search an employee', 'erp' ); ?>">

                                    <button disabled class="button erp-pro-preview-action"><?php esc_html_e( 'Search', 'erp' ); ?></button>

                                    <div style="margin-top: 10px;">
                                        <select disabled class="erp-select-field">
                                            <option><?php esc_html_e( 'Fixed Payment', 'erp' ); ?></option>
                                            <option><?php esc_html_e( 'Attendance Based Payment', 'erp' ); ?></option>
                                        </select>

                                        <span>
                                            <input disabled placeholder="<?php esc_attr_e( 'Set fixed payment for all fields', 'erp' ); ?>" type="number" value="">
                                            <button disabled class="button erp-pro-preview-action"><?php esc_html_e( 'Set', 'erp' ); ?></button>
                                        </span>
                                    </div>

                                    <table id="default-report" class="wp-list-table widefat fixed striped table-rec-reports">
                                        <thead>
                                            <tr>
                                                <th width="6%;"><?php esc_html_e( 'SL', 'erp' ); ?></th>
                                                <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                                                <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                                                <th><?php esc_html_e( 'Designation', 'erp' ); ?></th>
                                                <th><?php esc_html_e( 'Total Payment', 'erp' ); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $employees = [
                                                [ 'name' => 'Autumn J',      'dept' => 'General',    'desig' => 'Management' ],
                                                [ 'name' => 'Bianca Lang',   'dept' => 'General',    'desig' => 'Management' ],
                                                [ 'name' => 'Driscoll K',    'dept' => 'General',    'desig' => 'Management' ],
                                                [ 'name' => 'Kiara Kevin',   'dept' => 'General',    'desig' => 'Management' ],
                                            ];
                                            $sl = 1;
                                            foreach ( $employees as $emp ) :
                                            ?>
                                            <tr>
                                                <td class="align-center"><?php echo esc_html( $sl++ ); ?></td>
                                                <td class="align-center"><?php echo esc_html( $emp['name'] ); ?></td>
                                                <td class="align-center"><?php echo esc_html( $emp['dept'] ); ?></td>
                                                <td class="align-center"><?php echo esc_html( $emp['desig'] ); ?></td>
                                                <td class="align-center">
                                                    <input disabled class="pay_item_value" type="number" value="0" min="0">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td class="align-center" colspan="3">&nbsp;</td>
                                                <td class="align-center"><?php esc_html_e( 'Total :', 'erp' ); ?></td>
                                                <td class="align-center">$0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>

                                </div>
                                <div class="nv-holder">
                                    <span class="erp-pro-save-notice">
                                        <span class="dashicons dashicons-lock"></span>
                                        <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
                                        &mdash;
                                        <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-bulk-edit" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
                                    </span>
                                    <button type="button" class="button button-primary alignright" disabled><?php esc_html_e( 'Update', 'erp' ); ?></button>
                                </div>
                            </div>
                        </div>
                        <!-- inside -->
                    </div>
                    <!-- postbox -->
                </div>
                <!-- col-6 -->
            </div>
            <!-- row -->
        </div>
        <!-- erp-grid-container -->
    </div>
</div>
