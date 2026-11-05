<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Document Manager module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=documents" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h1><?php esc_html_e( 'Company Documents', 'erp' ); ?></h1>

        <!-- Toolbar -->
        <div style="display: flex; align-items: center; gap: 10px; margin: 15px 0; padding: 10px 15px; background: #f9fafb; border: 1px solid #e0e0e0; border-radius: 4px; flex-wrap: wrap;">
            <select disabled style="min-width: 130px;">
                <option><?php esc_html_e( 'Company Files', 'erp' ); ?></option>
            </select>
            <input type="search" disabled placeholder="<?php esc_attr_e( 'Search files...', 'erp' ); ?>" style="min-width: 200px;" />
            <span style="flex: 1;"></span>
            <button class="button erp-pro-preview-action" data-form="pro-form-upload-doc" data-form-title="<?php esc_attr_e( 'Upload Document', 'erp' ); ?>"><span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> <?php esc_html_e( 'Upload', 'erp' ); ?></button>
            <button class="button erp-pro-preview-action" data-form="pro-form-new-folder" data-form-title="<?php esc_attr_e( 'Create New Folder', 'erp' ); ?>"><span class="dashicons dashicons-plus" style="margin-top: 3px;"></span> <?php esc_html_e( 'New Folder', 'erp' ); ?></button>
        </div>

        <!-- Breadcrumb -->
        <div style="padding: 8px 0; color: #656668; font-size: 13px;">
            <span class="dashicons dashicons-admin-home" style="font-size: 16px; width: 16px; height: 16px;"></span>
            <a href="#" class="erp-pro-preview-action" style="text-decoration: none;">Company</a>
        </div>

        <!-- File List -->
        <table class="widefat">
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" disabled /></th>
                    <th><?php esc_html_e( 'Name', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Modified', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Created By', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Size', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-category" style="color: #f59e0b; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action"><strong>HR Policies</strong></a>
                    </td>
                    <td>Mar 20, 2026</td>
                    <td>Admin</td>
                    <td>&mdash;</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-category" style="color: #f59e0b; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action"><strong>Templates</strong></a>
                    </td>
                    <td>Mar 15, 2026</td>
                    <td>Admin</td>
                    <td>&mdash;</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-category" style="color: #f59e0b; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action"><strong>Onboarding</strong></a>
                    </td>
                    <td>Feb 28, 2026</td>
                    <td>Admin</td>
                    <td>&mdash;</td>
                </tr>
                <tr style="background: #fff;">
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-pdf" style="color: #dc2626; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action">Employee Handbook 2026.pdf</a>
                    </td>
                    <td>Mar 01, 2026</td>
                    <td>Admin</td>
                    <td>2.4 MB</td>
                </tr>
                <tr style="background: #fff;">
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-media-spreadsheet" style="color: #16a34a; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action">Holiday Calendar 2026.xlsx</a>
                    </td>
                    <td>Jan 05, 2026</td>
                    <td>HR Manager</td>
                    <td>128 KB</td>
                </tr>
                <tr style="background: #fff;">
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-media-document" style="color: #2563eb; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action">NDA Template.docx</a>
                    </td>
                    <td>Dec 15, 2025</td>
                    <td>Admin</td>
                    <td>45 KB</td>
                </tr>
                <tr style="background: #fff;">
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <span class="dashicons dashicons-format-image" style="color: #7c3aed; margin-right: 5px;"></span>
                        <a href="#" class="erp-pro-preview-action">Company Logo.png</a>
                    </td>
                    <td>Nov 10, 2025</td>
                    <td>Admin</td>
                    <td>856 KB</td>
                </tr>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">3 folders, 4 files</span>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form template for Upload Document -->
<div id="pro-form-upload-doc" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Select File', 'erp' ); ?></label></th>
            <td><input type="file" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Upload To', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option>Company (Root)</option>
                    <option>HR Policies</option>
                    <option>Templates</option>
                    <option>Onboarding</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Description', 'erp' ); ?></label></th>
            <td><textarea rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Optional file description...', 'erp' ); ?>"></textarea></td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Upload', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=documents" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>

<!-- Hidden form template for New Folder -->
<div id="pro-form-new-folder" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Folder Name', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Project Files', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Parent Folder', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option>Company (Root)</option>
                    <option>HR Policies</option>
                    <option>Templates</option>
                    <option>Onboarding</option>
                </select>
            </td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Create Folder', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=documents" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
