<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Org Chart feature.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=org-chart" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div style="padding: 30px; text-align: center;">
        <!-- Org Chart Tree -->
        <div style="display: flex; flex-direction: column; align-items: center; gap: 0;">
            <!-- CEO -->
            <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="James Wilson" style="background: #4f46e5; color: #fff; padding: 12px 24px; border-radius: 8px; min-width: 180px; text-align: center; text-decoration: none; display: block;">
                <strong>James Wilson</strong>
                <div style="font-size: 12px; opacity: 0.85;">CEO</div>
            </a>

            <div style="width: 2px; height: 30px; background: #d1d5db;"></div>

            <!-- VPs Row -->
            <div style="display: flex; gap: 60px; position: relative;">
                <!-- Horizontal connector -->
                <div style="position: absolute; top: 0; left: 25%; right: 25%; height: 2px; background: #d1d5db;"></div>

                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div style="width: 2px; height: 15px; background: #d1d5db;"></div>
                    <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Sarah Johnson" style="background: #7c3aed; color: #fff; padding: 10px 20px; border-radius: 8px; min-width: 160px; text-align: center; text-decoration: none; display: block;">
                        <strong>Sarah Johnson</strong>
                        <div style="font-size: 12px; opacity: 0.85;">VP Engineering</div>
                    </a>
                    <div style="width: 2px; height: 20px; background: #d1d5db;"></div>
                    <div style="display: flex; gap: 20px;">
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="John Smith" style="background: #e0e7ff; color: #3730a3; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-align: center; text-decoration: none;">
                            <strong>John Smith</strong><br><span style="font-size: 11px;">Lead Dev</span>
                        </a>
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Mike Davis" style="background: #e0e7ff; color: #3730a3; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-align: center; text-decoration: none;">
                            <strong>Mike Davis</strong><br><span style="font-size: 11px;">Sr. Engineer</span>
                        </a>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div style="width: 2px; height: 15px; background: #d1d5db;"></div>
                    <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Emily Chen" style="background: #059669; color: #fff; padding: 10px 20px; border-radius: 8px; min-width: 160px; text-align: center; text-decoration: none; display: block;">
                        <strong>Emily Chen</strong>
                        <div style="font-size: 12px; opacity: 0.85;">VP Design</div>
                    </a>
                    <div style="width: 2px; height: 20px; background: #d1d5db;"></div>
                    <div style="display: flex; gap: 20px;">
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Alex Turner" style="background: #d1fae5; color: #065f46; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-align: center; text-decoration: none;">
                            <strong>Alex Turner</strong><br><span style="font-size: 11px;">UI Designer</span>
                        </a>
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Rachel Kim" style="background: #d1fae5; color: #065f46; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-align: center; text-decoration: none;">
                            <strong>Rachel Kim</strong><br><span style="font-size: 11px;">UX Research</span>
                        </a>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; align-items: center;">
                    <div style="width: 2px; height: 15px; background: #d1d5db;"></div>
                    <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="David Park" style="background: #d97706; color: #fff; padding: 10px 20px; border-radius: 8px; min-width: 160px; text-align: center; text-decoration: none; display: block;">
                        <strong>David Park</strong>
                        <div style="font-size: 12px; opacity: 0.85;">VP Marketing</div>
                    </a>
                    <div style="width: 2px; height: 20px; background: #d1d5db;"></div>
                    <div style="display: flex; gap: 20px;">
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Lisa Wang" style="background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-align: center; text-decoration: none;">
                            <strong>Lisa Wang</strong><br><span style="font-size: 11px;">Content Lead</span>
                        </a>
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-employee-detail" data-form-title="Tom Brown" style="background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-align: center; text-decoration: none;">
                            <strong>Tom Brown</strong><br><span style="font-size: 11px;">SEO Manager</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form template for Employee Detail -->
<div id="pro-form-employee-detail" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Full Name', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'Employee name', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Designation', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. VP Engineering', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Department', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Department —', 'erp' ); ?></option>
                    <option>Engineering</option>
                    <option>Design</option>
                    <option>Marketing</option>
                    <option>Sales</option>
                    <option>HR</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Reports To', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Manager —', 'erp' ); ?></option>
                    <option>James Wilson (CEO)</option>
                    <option>Sarah Johnson (VP Engineering)</option>
                    <option>Emily Chen (VP Design)</option>
                    <option>David Park (VP Marketing)</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Email', 'erp' ); ?></label></th>
            <td><input type="email" class="regular-text" placeholder="<?php esc_attr_e( 'employee@company.com', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Phone', 'erp' ); ?></label></th>
            <td><input type="tel" class="regular-text" placeholder="<?php esc_attr_e( '+1 (555) 000-0000', 'erp' ); ?>" /></td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Changes', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=org-chart" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
