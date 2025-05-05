<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Settings\Template;

/**
 * Settings class
 */
class Settings extends Template {


    public $id;
    public $label;
    public $sections;
    public $icon;
    public $single_option;

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->id            = 'erp-hr';
        $this->label         = __( 'HR', 'erp' );
        $this->single_option = true;
        $this->sections      = $this->get_sections();
        $this->icon          = WPERP_ASSETS . '/images/wperp-settings/hr.png';
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
     * @param string|bool $section
     * @param bool        $all_data Get all data or only single section data
     *
     * @return array
     */
    public function get_section_fields( $section = '', $all_data = false ) {
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
        $fields['leave'][] = [
            'title' => __( 'Auto Assign Leave Policy', 'erp' ),
            'type'  => 'checkbox',
            'id'    => 'enable_auto_leave_policy_assignment_on_type_change',
            'desc'  => __( 'Employees will be assigned relevant leave policies automatically after updating their Employment type', 'erp' ),
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
        $fields['miscellaneous'][] = [
            'title' => __( 'Hide Pay Rate', 'erp' ),
            'type'  => 'checkbox',
            'id'    => 'erp_hrm_hide_pay_rate',
            'desc'  => __( 'By default hide pay rate on employee profile.', 'erp' ),
        ];
        $fields['miscellaneous'][] =[
            'type'  => 'sectionend',
            'id'    => 'hrm_miscellaneous',
        ];
        $fields = apply_filters( 'erp_settings_hr_section_fields', $fields, $section );

        foreach ( $this->get_sections() as $sec => $name ) {
            if ( empty( $fields[ $sec ] ) ) {
                $fields = apply_filters( 'erp_settings_hr_section_fields', $fields, $sec );
            }
        }

        if ( $all_data ) {
            return $fields;
        }

        $section = $section === false ? $fields['workdays'] : $fields[$section];

        return $section;
    }
}
