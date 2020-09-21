<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Settings class
 */
class Settings extends ERP_Settings_Page {

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->id            = 'erp-hr';
        $this->label         = __( 'HR', 'erp' );
        $this->single_option = true;
        $this->sections      = $this->get_sections();

        add_action( 'erp_admin_field_hr_financial_years', [ $this, 'get_hr_financial_years' ] );
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = [
            'workdays'      => __( 'Workdays', 'erp' ),
            'leave'         => __( 'Leave', 'erp' ),
            'financial'     => __( 'Leave Years', 'erp' ),
            'miscellaneous' => __( 'Miscellaneous', 'erp' ),
        ];

        return apply_filters( 'erp_settings_hr_sections', $sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        $options = [
            '8' => __( 'Full Day', 'erp' ),
            '4' => __( 'Half Day', 'erp' ),
            '0' => __( 'Non-working Day', 'erp' ),
        ];

        $week_days = [
            'mon' => __( 'Monday', 'erp' ),
            'tue' => __( 'Tuesday', 'erp' ),
            'wed' => __( 'Wednesday', 'erp' ),
            'thu' => __( 'Thursday', 'erp' ),
            'fri' => __( 'Friday', 'erp' ),
            'sat' => __( 'Saturday', 'erp' ),
            'sun' => __( 'Sunday', 'erp' ),
        ];

        $fields = [];

        $fields['workdays'][] = [
            'title' => __( 'Work Days', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Week day settings for this company.', 'erp' ),
            'id'    => 'general_options',
        ];

        foreach ( $week_days as $key => $day ) {
            $fields['workdays'][] = [
                'title'   => $day,
                'id'      => $key,
                'type'    => 'select',
                'options' => $options,
            ];
        }

        $fields['workdays'][] = [
            'type'  => 'sectionend',
            'id'    => 'script_styling_options',
        ];

        $fields['leave'][] = [
            'title' => __( 'Leave', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Leave settings for this company.', 'erp' ),
            'id'    => 'general_options',
        ];
        $fields['leave'][] = [
            'title' => __( 'Extra Unpaid Leave', 'erp' ),
            'type'  => 'checkbox',
            'id'    => 'enable_extra_leave',
            'desc'  => __( 'Employees can apply for leave, even when there is no entitlement left.', 'erp' ),
        ];

        $fields = apply_filters( 'erp_settings_hr_leave_section_fields', $fields );

        $fields['leave'][] = [
            'type'  => 'sectionend',
            'id'    => 'script_styling_options',
        ];

        $fields['financial'] = [
            [
                'title' => __( 'Leave Years', 'erp' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'erp_acct_ob_options',
            ],
            [
                'type' => 'hr_financial_years',
                'id'   => 'erp_hr_financial_years',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'script_styling_options',
            ],
        ];

        $fields['miscellaneous'][] =[
            'title' => __( 'Miscellaneous', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'HRM miscellaneous settings.', 'erp' ),
            'id'    => 'hrm_miscellaneous',
        ];
        $fields['miscellaneous'][] = [
            'title' => __( 'Remove WP User', 'erp' ),
            'type'  => 'checkbox',
            'id'    => 'erp_hrm_remove_wp_user',
            'desc'  => __( 'Remove wp user on removing employee.', 'erp' ),
        ];
        $fields['miscellaneous'][] =[
            'type'  => 'sectionend',
            'id'    => 'hrm_miscellaneous',
        ];
        $fields = apply_filters( 'erp_settings_hr_section_fields', $fields, $section );

        $section = $section === false ? $fields['workdays'] : $fields[$section];

        return $section;
    }

    public function get_hr_financial_years() {
        global $wpdb;

        $f_years = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_hr_financial_years", ARRAY_A );

        require_once WPERP_HRM_VIEWS . '/settings/fyear.php';
    }
}

return new Settings();
