<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Training module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=training" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php esc_html_e( 'Training Programs', 'erp' ); ?>
            <a href="#" class="page-title-action erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Add New Training', 'erp' ); ?>"><?php esc_html_e( 'Add New Training', 'erp' ); ?></a>
        </h1>

        <table class="widefat striped" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" disabled /></th>
                    <th><?php esc_html_e( 'Title', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Training Subject', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Trainer', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Employees', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Start Date', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'End Date', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Cost', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>">Leadership Development</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Management Skills</td>
                    <td>External - ABC Consulting</td>
                    <td>5</td>
                    <td>Apr 01, 2026</td>
                    <td>Apr 05, 2026</td>
                    <td>$2,500.00</td>
                    <td><span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Upcoming</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>">React Advanced Patterns</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Technical Skills</td>
                    <td>John Smith (Internal)</td>
                    <td>8</td>
                    <td>Mar 10, 2026</td>
                    <td>Mar 14, 2026</td>
                    <td>$0.00</td>
                    <td><span style="background: #fff7ed; color: #9a3412; padding: 2px 8px; border-radius: 3px; font-size: 12px;">In Progress</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>">Workplace Safety</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Compliance</td>
                    <td>External - SafeWork Inc</td>
                    <td>15</td>
                    <td>Feb 15, 2026</td>
                    <td>Feb 16, 2026</td>
                    <td>$1,200.00</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Completed</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>">Sales Communication</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-training" data-form-title="<?php esc_attr_e( 'Edit Training', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Soft Skills</td>
                    <td>External - SpeakUp Academy</td>
                    <td>6</td>
                    <td>Jan 20, 2026</td>
                    <td>Jan 22, 2026</td>
                    <td>$800.00</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Completed</span></td>
                </tr>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">4 items</span>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form template for New/Edit Training -->
<div id="pro-form-new-training" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Training Title', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Leadership Development', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Training Subject', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Subject —', 'erp' ); ?></option>
                    <option>Technical Skills</option>
                    <option>Management Skills</option>
                    <option>Soft Skills</option>
                    <option>Compliance</option>
                    <option>Other</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Trainer Type', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option>Internal</option>
                    <option>External</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Trainer Name', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. ABC Consulting', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Employees', 'erp' ); ?></label></th>
            <td>
                <select multiple style="min-width: 200px; min-height: 80px;">
                    <option>John Smith</option>
                    <option>Sarah Johnson</option>
                    <option>Mike Davis</option>
                    <option>Emily Chen</option>
                    <option>Alex Turner</option>
                    <option>Rachel Kim</option>
                </select>
                <p class="description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple employees.', 'erp' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Start Date', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'End Date', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Cost', 'erp' ); ?></label></th>
            <td><input type="number" step="0.01" class="regular-text" placeholder="0.00" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Description', 'erp' ); ?></label></th>
            <td><textarea rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Training description...', 'erp' ); ?>"></textarea></td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Training', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=training" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
