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
                    <h2 class="hndle" style="cursor: default;"><span><?php echo esc_html( $cal['name'] ); ?></span></h2>
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
                        <a href="#" class="button erp-payrun-start-btn" style="margin-left: auto;">
                            <?php esc_html_e( 'Start Payrun', 'erp' ); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Section 2: Pay Run Steps -->
        <div id="erp-payrun-steps-section" class="erp-payroll-steps" style="display: none;">
            <h1>
                <a href="#" class="erp-payrun-back-btn" style="text-decoration: none; color: #444;" title="<?php esc_attr_e( 'Back to Calendars', 'erp' ); ?>">
                    <span class="dashicons dashicons-arrow-left-alt2" style="font-size: 20px; width: 20px; height: 20px; vertical-align: middle;"></span>
                </a>
                <?php esc_html_e( 'Pay Run', 'erp' ); ?>
                <span class="alignright cal_status cal_status_not_approve">
                    <?php esc_html_e( 'Not Approved', 'erp' ); ?>
                </span>
            </h1>

            <!-- Chevron Step Wizard -->
            <ul class="payroll-step-progress">
                <li class="active" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-content"><?php esc_html_e( 'Employees', 'erp' ); ?></span>
                </li>
                <li class="not-active" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-content"><?php esc_html_e( 'Variable Input', 'erp' ); ?></span>
                </li>
                <li class="not-active" data-step="3">
                    <span class="step-number">3</span>
                    <span class="step-content"><?php esc_html_e( 'PaySlips', 'erp' ); ?></span>
                </li>
                <li class="not-active" data-step="4">
                    <span class="step-number">4</span>
                    <span class="step-content"><?php esc_html_e( 'Approval', 'erp' ); ?></span>
                </li>
            </ul>

            <!-- ========== Step 1: Employees ========== -->
            <div class="erp-payrun-step-content" id="erp-step-1" style="clear: both;">

                <!-- Date fields -->
                <div style="display: flex; justify-content: center; margin: 20px 0 15px;">
                    <table style="border-collapse: separate; border-spacing: 10px 8px;">
                        <tr>
                            <td style="text-align: right; font-weight: 600; color: #555; background: #e5e5e5; padding: 5px 15px; border-radius: 3px;"><?php esc_html_e( 'From Date', 'erp' ); ?></td>
                            <td><input type="text" value="2026-03-01" style="width: 130px; padding: 4px 8px;" /></td>
                            <td style="text-align: right; font-weight: 600; color: #555; background: #e5e5e5; padding: 5px 15px; border-radius: 3px;"><?php esc_html_e( 'To Date', 'erp' ); ?></td>
                            <td><input type="text" value="2026-03-31" style="width: 130px; padding: 4px 8px;" /></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: 600; color: #555; background: #e5e5e5; padding: 5px 15px; border-radius: 3px;"><?php esc_html_e( 'Payment Date', 'erp' ); ?></td>
                            <td><input type="text" value="2026-03-01" style="width: 130px; padding: 4px 8px;" /></td>
                            <td colspan="2">
                                <button class="button button-primary" disabled style="width: 286px; font-weight: 600;"><?php esc_html_e( 'Generate Employee List', 'erp' ); ?></button>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Pay item checkbox -->
                <div style="margin-bottom: 15px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <?php esc_html_e( 'Do you want to specify pay item fields ?', 'erp' ); ?>
                    <input type="checkbox" disabled />
                </div>

                <!-- Active Employees -->
                <div class="postbox metabox-holder">
                    <h3 class="openingform_header_title hndle">
                        <?php esc_html_e( 'Active Employees', 'erp' ); ?>
                    </h3>
                    <div class="inside">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Designation', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Pay Rate', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Time Worked', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Pay Basic', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Payment', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Deduction', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Tax', 'erp' ); ?></th>
                                    <th><?php esc_html_e( 'Net Pay', 'erp' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#" class="erp-pro-preview-action">John Smith</a></td>
                                    <td>Engineering</td>
                                    <td>Sr. Developer</td>
                                    <td>$30,000.00</td>
                                    <td>-</td>
                                    <td>$30,000.00</td>
                                    <td>$2,345.00</td>
                                    <td>$0.00</td>
                                    <td>$0.00</td>
                                    <td>$32,345.00</td>
                                </tr>
                                <tr>
                                    <td><a href="#" class="erp-pro-preview-action">Sarah Johnson</a></td>
                                    <td>General Management</td>
                                    <td>Business Manager</td>
                                    <td>$5,000.00</td>
                                    <td>-</td>
                                    <td>$5,000.00</td>
                                    <td>$0.00</td>
                                    <td>$0.00</td>
                                    <td>$0.00</td>
                                    <td>$5,000.00</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>$35,000.00</td>
                                    <td>$2,345.00</td>
                                    <td>$0.00</td>
                                    <td>$0.00</td>
                                    <td>$37,345.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button class="button button-primary erp-pro-preview-action" disabled>
                        <?php esc_html_e( 'Next &rarr;', 'erp' ); ?>
                    </button>
                </div>
            </div>

            <!-- ========== Step 2: Variable Input ========== -->
            <div class="erp-payrun-step-content" id="erp-step-2" style="clear: both; display: none;">

                <div class="single-emp-info-left-side">
                    <div class="postbox metabox-holder">
                        <h3 class="openingform_header_title hndle">
                            <?php esc_html_e( 'Employee profile information', 'erp' ); ?>
                        </h3>
                        <div class="inside">
                            <div class="row">
                                <select style="width: 100%;">
                                    <option>John Smith</option>
                                    <option>Sarah Johnson</option>
                                </select>
                            </div>

                            <ul class="add_ded_info">
                                <li>
                                    <label>
                                        <span class="lpart-normal"><?php esc_html_e( 'Pay Basic', 'erp' ); ?></span>
                                        <span class="rpart-normal">$30,000.00</span>
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <span class="lpart-normal">&nbsp;House Rent Allowance</span>
                                        <span class="rpart-normal">$150.00</span>
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <span class="lpart-normal">&nbsp;Transport Allowance</span>
                                        <span class="rpart-normal">$100.00</span>
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <span class="lpart"><?php esc_html_e( 'Total Payment', 'erp' ); ?></span>
                                        <span class="rpart">$30,250.00</span>
                                    </label>
                                </li>
                            </ul>

                            <ul class="add_ded_info">
                                <li>
                                    <label>
                                        <span class="lpart-normal">&nbsp;Provident Fund</span>
                                        <span class="rpart-normal">$200.00</span>
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <span class="lpart"><?php esc_html_e( 'Total Deduction', 'erp' ); ?></span>
                                        <span class="rpart">$200.00</span>
                                    </label>
                                </li>
                                <li class="net-pay">
                                    <label>
                                        <span class="lpart"><?php esc_html_e( 'Net Pay', 'erp' ); ?></span>
                                        <span class="rpart">$30,050.00</span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="single-emp-info-right-side">
                    <div class="postbox metabox-holder">
                        <h3 class="openingform_header_title hndle"><?php esc_html_e( 'Additional allowance or deduction for this pay run only', 'erp' ); ?></h3>
                        <div class="inside">
                            <div class="row">
                                <h4 class="dynamic-header"><?php esc_html_e( 'Additional Pay', 'erp' ); ?></h4>
                                <select style="width: 37%;" disabled>
                                    <option><?php esc_html_e( '— Select Pay Item —', 'erp' ); ?></option>
                                    <option>Bonus</option>
                                    <option>Overtime</option>
                                    <option>Commission</option>
                                </select>
                                <input type="number" min="1" max="100000" disabled placeholder="0" />
                                <input type="text" disabled placeholder="Note" />
                                <button class="button" disabled><i class="fa fa-plus"></i> <?php esc_html_e( 'Add', 'erp' ); ?></button>
                            </div>

                            <div class="row">
                                <h4 class="dynamic-header"><?php esc_html_e( 'Payments (Non-Taxable)', 'erp' ); ?></h4>
                                <select style="width: 37%;" disabled>
                                    <option><?php esc_html_e( '— Select Pay Item —', 'erp' ); ?></option>
                                    <option>Reimbursement</option>
                                    <option>Travel Allowance</option>
                                </select>
                                <input type="number" min="1" max="100000" disabled placeholder="0" />
                                <input type="text" disabled placeholder="Note" />
                                <button class="button" disabled><i class="fa fa-plus"></i> <?php esc_html_e( 'Add', 'erp' ); ?></button>
                            </div>

                            <div class="row">
                                <h4 class="dynamic-header"><?php esc_html_e( 'Additional Deduction', 'erp' ); ?></h4>
                                <select style="width: 37%;" disabled>
                                    <option><?php esc_html_e( '— Select Deduction Item —', 'erp' ); ?></option>
                                    <option>Loan Repayment</option>
                                    <option>Insurance</option>
                                </select>
                                <input type="number" min="1" max="100000" disabled placeholder="0" />
                                <input type="text" disabled placeholder="Note" />
                                <button class="button" disabled><i class="fa fa-plus"></i> <?php esc_html_e( 'Add', 'erp' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <div class="nv-holder">
                        <div class="row">
                            <button class="button button-primary alignright nbutton erp-pro-preview-action" disabled><?php esc_html_e( 'Next &rarr;', 'erp' ); ?></button>
                            <button class="button button-primary alignright bbutton erp-pro-preview-action" disabled><?php esc_html_e( '&larr; Back', 'erp' ); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== Step 3: PaySlips ========== -->
            <div class="erp-payrun-step-content" id="erp-step-3" style="clear: both; display: none;">

                <div class="postbox metabox-holder payslip-postbox">
                    <h3 class="openingform_header_title hndle"><?php esc_html_e( 'Employee Payslip', 'erp' ); ?></h3>
                    <div class="inside">
                        <div class="row">
                            <div class="seventy-left-row">
                                <select style="min-width: 250px;">
                                    <option>John Smith</option>
                                    <option>Sarah Johnson</option>
                                </select>
                                <button class="button print-btn" disabled><?php esc_html_e( 'Print Payslip', 'erp' ); ?></button>
                            </div>
                        </div>

                        <div class="row">
                            <h2 class="erp-pay-slip-label" style="text-align: center"><?php esc_html_e( 'Payslip', 'erp' ); ?></h2>
                            <div class="erp-payslip-header">
                                <h2>weDevs</h2>
                            </div>
                            <address class="address">
                                Level 6, 23/C, Road 7, Block F, Banani, Dhaka 1213
                            </address>
                            <br>
                            <b><?php esc_html_e( 'Employee Name', 'erp' ); ?></b>
                            <br>
                            John Smith
                            <br>
                            <b><?php esc_html_e( 'Employee Address', 'erp' ); ?></b>
                            <address class="address">
                                123 Main Street, Apt 4B, Dhaka
                            </address>
                            <div class="inner-row">
                                <span class="draft-text"><?php esc_html_e( 'DRAFT', 'erp' ); ?></span>
                                <ul class="payslip-list">
                                    <li>
                                        <label><b><?php esc_html_e( 'Department', 'erp' ); ?></b></label>
                                        <label><b><?php esc_html_e( 'Designation', 'erp' ); ?></b></label>
                                        <label><b><?php esc_html_e( 'Period', 'erp' ); ?></b></label>
                                    </li>
                                    <li>
                                        <label>Engineering</label>
                                        <label>Sr. Developer</label>
                                        <label>Mar 01, 2026 <?php esc_html_e( 'to', 'erp' ); ?> Mar 31, 2026</label>
                                    </li>
                                </ul>
                                <ul class="payslip-list">
                                    <li>
                                        <label><b><?php esc_html_e( 'Payment Date', 'erp' ); ?></b></label>
                                        <label><b><?php esc_html_e( 'Tax Number', 'erp' ); ?></b></label>
                                        <label><b><?php esc_html_e( 'Bank Account Number', 'erp' ); ?></b></label>
                                        <label><b><?php esc_html_e( 'Payment Method', 'erp' ); ?></b></label>
                                    </li>
                                    <li>
                                        <label>Mar 31, 2026</label>
                                        <label>TIN-9876543</label>
                                        <label>****4521</label>
                                        <label>Bank Transfer</label>
                                    </li>
                                </ul>
                            </div>

                            <div class="inner-row">
                                <div class="half-left-row" style="clear: left">
                                    <ul class="paylist">
                                        <li>
                                            <label><b><?php esc_html_e( 'Payments', 'erp' ); ?></b></label>
                                        </li>
                                        <li>
                                            <label class="text-alignleft"><?php esc_html_e( 'Pay Basic', 'erp' ); ?></label>
                                            <label class="text-alignright">$30,000.00</label>
                                        </li>
                                        <li>
                                            <label class="text-alignleft">House Rent Allowance</label>
                                            <label class="text-alignright">$150.00</label>
                                        </li>
                                        <li>
                                            <label class="text-alignleft">Transport Allowance</label>
                                            <label class="text-alignright">$100.00</label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="half-right-row">
                                    <ul class="paylist">
                                        <li>
                                            <label><b><?php esc_html_e( 'Deductions', 'erp' ); ?></b></label>
                                        </li>
                                        <li>
                                            <label class="text-alignleft">Provident Fund</label>
                                            <label class="text-alignright">$200.00</label>
                                        </li>
                                        <li class="final-total-row">
                                            <label class="text-alignleft"><?php esc_html_e( 'Total Deduction', 'erp' ); ?></label>
                                            <label class="text-alignright">$200.00</label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="inner-row">
                                <div class="half-left-row" style="clear: left">
                                    <ul class="paylist paylist-final-amount">
                                        <li>
                                            <label class="text-alignleft"><b><?php esc_html_e( 'Total Payment', 'erp' ); ?></b></label>
                                            <label class="text-alignright"><b>$30,250.00</b></label>
                                        </li>
                                    </ul>
                                </div>
                                <div class="half-right-row">
                                    <ul class="paylist paylist-final-amount">
                                        <li>
                                            <label class="text-alignleft"><b><?php esc_html_e( 'Net Pay', 'erp' ); ?></b></label>
                                            <label class="text-alignright"><b>$30,050.00</b></label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nv-holder">
                    <button class="button button-primary alignright nbutton erp-pro-preview-action" disabled><?php esc_html_e( 'Next &rarr;', 'erp' ); ?></button>
                    <button class="button button-primary alignright bbutton erp-pro-preview-action" disabled><?php esc_html_e( '&larr; Back', 'erp' ); ?></button>
                </div>
            </div>

            <!-- ========== Step 4: Approval ========== -->
            <div class="erp-payrun-step-content" id="erp-step-4" style="clear: both; display: none;">

                <div class="nv-holder-top">
                    <span><?php esc_html_e( 'Payment Date', 'erp' ); ?></span>
                    <input type="text" value="2026-03-01" disabled />
                </div>

                <div class="postbox metabox-holder">
                    <h3 class="openingform_header_title hndle">
                        <?php esc_html_e( 'Ready to approve', 'erp' ); ?>
                    </h3>
                    <div class="inside">
                        <div class="row">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Designation', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Pay Basic', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Payment', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Deduction', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Tax', 'erp' ); ?></th>
                                        <th><?php esc_html_e( 'Net Pay', 'erp' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="#" class="erp-pro-preview-action">John Smith</a></td>
                                        <td>Engineering</td>
                                        <td>Sr. Developer</td>
                                        <td>$30,000.00</td>
                                        <td>$30,250.00</td>
                                        <td>$200.00</td>
                                        <td>$0.00</td>
                                        <td>$30,050.00</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#" class="erp-pro-preview-action">Sarah Johnson</a></td>
                                        <td>General Management</td>
                                        <td>Business Manager</td>
                                        <td>$5,000.00</td>
                                        <td>$5,000.00</td>
                                        <td>$0.00</td>
                                        <td>$0.00</td>
                                        <td>$5,000.00</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>$35,000.00</td>
                                        <td>$35,250.00</td>
                                        <td>$200.00</td>
                                        <td>$0.00</td>
                                        <td>$35,050.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="nv-holder">
                    <div class="rrow">
                        <button class="button button-primary alignright erp-pro-preview-action" disabled><?php esc_html_e( 'Approve', 'erp' ); ?></button>
                    </div>
                    <button class="button button-primary alignright bbutton erp-pro-preview-action" disabled><?php esc_html_e( '&larr; Back', 'erp' ); ?></button>
                    <span class="erp-pro-save-notice" style="float: left; margin-top: 5px;">
                        <span class="dashicons dashicons-lock"></span>
                        <?php esc_html_e( 'Upgrade to Pro to approve pay runs', 'erp' ); ?>
                        &mdash;
                        <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-payrun" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
                    </span>
                </div>
            </div>

        </div><!-- /erp-payrun-steps-section -->

    </div>
</div>

<script>
jQuery(function($) {
    // Start Payrun → show steps, hide calendars
    $('.erp-payrun-start-btn').on('click', function(e) {
        e.preventDefault();
        $('#erp-calendar-list-section').hide();
        $('#erp-payrun-steps-section').fadeIn(200);
        switchStep(1);
    });

    // Back to calendar list
    $('.erp-payrun-back-btn').on('click', function(e) {
        e.preventDefault();
        $('#erp-payrun-steps-section').hide();
        $('#erp-calendar-list-section').fadeIn(200);
    });

    // Clickable step tabs
    $('.payroll-step-progress li').on('click', function() {
        var step = $(this).data('step');
        switchStep(step);
    });

    function switchStep(step) {
        // Update chevron tabs
        $('.payroll-step-progress li').each(function() {
            var s = $(this).data('step');
            $(this).removeClass('active not-active').addClass(s === step ? 'active' : 'not-active');
        });
        // Show/hide step content
        $('.erp-payrun-step-content').hide();
        $('#erp-step-' + step).fadeIn(200);
    }
});
</script>
