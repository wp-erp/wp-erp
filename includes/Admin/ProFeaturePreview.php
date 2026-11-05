<?php

namespace WeDevs\ERP\Admin;

/**
 * Renders realistic preview pages for pro HR features in the free plugin.
 * Each method renders a module-specific template with sample data so users
 * can see what the feature actually looks like.
 *
 * @since 1.13.0
 */
class ProFeaturePreview {

    /**
     * Preview templates directory
     */
    const PREVIEW_DIR = '/Admin/views/pro-preview/';

    /**
     * Render the Payroll Dashboard preview page
     *
     * @return void
     */
    public function payroll_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'payroll.php';
    }

    /**
     * Render the Pay Calendar preview page
     *
     * @return void
     */
    public function payroll_calendar_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'payroll-calendar.php';
    }

    /**
     * Render the Pay Run List preview page
     *
     * @return void
     */
    public function payroll_payrun_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'payroll-payrun.php';
    }

    /**
     * Render the Bulk Pay Item Edit preview page
     *
     * @return void
     */
    public function payroll_bulk_edit_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'payroll-bulk-edit.php';
    }

    /**
     * Render the Payroll Reports preview page
     *
     * @return void
     */
    public function payroll_reports_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'payroll-reports.php';
    }

    /**
     * Render the Attendance preview page
     *
     * @return void
     */
    public function attendance_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'attendance.php';
    }

    /**
     * Render the Recruitment preview page
     *
     * @return void
     */
    public function recruitment_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'recruitment.php';
    }

    /**
     * Render the Assets preview page
     *
     * @return void
     */
    public function assets_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'assets.php';
    }

    /**
     * Render the Documents preview page
     *
     * @return void
     */
    public function documents_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'documents.php';
    }

    /**
     * Render the Training preview page
     *
     * @return void
     */
    public function training_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'training.php';
    }

    /**
     * Render the Org Chart preview page
     *
     * @return void
     */
    public function org_chart_page() {
        include WPERP_INCLUDES . self::PREVIEW_DIR . 'org-chart.php';
    }

    /**
     * Filter report template for pro report types
     *
     * @param string $template Current template path
     * @param string $action   Report type
     *
     * @return string
     */
    public function filter_report_template( $template, $action ) {
        $pro_report_types = [
            'asset-report'        => 'report-assets',
            'attendance-report'   => 'report-attendance',
            'att-report-employee' => 'report-attendance-employee',
        ];

        if ( isset( $pro_report_types[ $action ] ) ) {
            return WPERP_INCLUDES . self::PREVIEW_DIR . $pro_report_types[ $action ] . '.php';
        }

        return $template;
    }
}
