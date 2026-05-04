<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Recruitment module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=recruitment" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php esc_html_e( 'Job Openings', 'erp' ); ?>
            <a href="#" class="page-title-action erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Create Job Opening', 'erp' ); ?>"><?php esc_html_e( 'Create Opening', 'erp' ); ?></a>
        </h1>

        <!-- Filters -->
        <div style="margin: 15px 0;">
            <ul class="subsubsub">
                <li><a href="#" class="current erp-pro-preview-action"><?php esc_html_e( 'All', 'erp' ); ?> <span class="count">(5)</span></a> |</li>
                <li><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Published', 'erp' ); ?> <span class="count">(3)</span></a> |</li>
                <li><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Draft', 'erp' ); ?> <span class="count">(1)</span></a> |</li>
                <li><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Closed', 'erp' ); ?> <span class="count">(1)</span></a></li>
            </ul>
        </div>

        <table class="widefat striped" style="clear: both;">
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" disabled /></th>
                    <th><?php esc_html_e( 'Job Title', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'No. of Positions', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Applicants', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Deadline', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>">Senior Frontend Developer</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="view"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'View Candidates', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Engineering</td>
                    <td>2</td>
                    <td>14</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Published</span></td>
                    <td>Apr 30, 2026</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>">Product Designer</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="view"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'View Candidates', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Design</td>
                    <td>1</td>
                    <td>8</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Published</span></td>
                    <td>May 15, 2026</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>">Marketing Manager</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="view"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'View Candidates', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Marketing</td>
                    <td>1</td>
                    <td>22</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Published</span></td>
                    <td>Apr 15, 2026</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>">DevOps Engineer</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Engineering</td>
                    <td>1</td>
                    <td>0</td>
                    <td><span style="background: #f3f4f6; color: #4b5563; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Draft</span></td>
                    <td>&mdash;</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>">Sales Representative</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-opening" data-form-title="<?php esc_attr_e( 'Edit Job Opening', 'erp' ); ?>"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="view"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'View Candidates', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Sales</td>
                    <td>3</td>
                    <td>31</td>
                    <td><span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Closed</span></td>
                    <td>Feb 28, 2026</td>
                </tr>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">5 items</span>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form template for New/Edit Job Opening -->
<div id="pro-form-new-opening" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Job Title', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Senior Frontend Developer', 'erp' ); ?>" /></td>
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
            <th><label><?php esc_html_e( 'No. of Positions', 'erp' ); ?></label></th>
            <td><input type="number" min="1" value="1" style="width: 80px;" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Employment Type', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option>Full Time</option>
                    <option>Part Time</option>
                    <option>Contract</option>
                    <option>Internship</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Application Deadline', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Experience Required', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 3-5 years', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Job Description', 'erp' ); ?></label></th>
            <td><textarea rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Describe the role, responsibilities, and requirements...', 'erp' ); ?>"></textarea></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Status', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option>Draft</option>
                    <option>Published</option>
                </select>
            </td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Opening', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=recruitment" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
