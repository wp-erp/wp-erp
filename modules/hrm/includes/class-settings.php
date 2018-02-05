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
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'workdays'      => __( 'Workdays', 'erp' ),
            'leave'         => __( 'Leave', 'erp' ),
            'miscellaneous' => __( 'Miscellaneous', 'erp' ),
        );

        return apply_filters( 'erp_settings_hr_sections', $sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        $options = array(
            '8' => __( 'Full Day', 'erp' ),
            '4' => __( 'Half Day', 'erp' ),
            '0' => __( 'Non-working Day', 'erp' )
        );

        $week_days = array(
            'mon' => __( 'Monday', 'erp' ),
            'tue' => __( 'Tuesday', 'erp' ),
            'wed' => __( 'Wednesday', 'erp' ),
            'thu' => __( 'Thursday', 'erp' ),
            'fri' => __( 'Friday', 'erp' ),
            'sat' => __( 'Saturday', 'erp' ),
            'sun' => __( 'Sunday', 'erp' )
        );

        $fields = [];

        $fields['workdays'][] = [
            'title' => __( 'Work Days', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Week day settings for this company.', 'erp' ),
            'id'    => 'general_options'
        ];

        foreach ($week_days as $key => $day) {
            $fields['workdays'][] = [
                'title'   => $day,
                'id'      => $key,
                'type'    => 'select',
                'options' => $options,
            ];
        }

        $fields['workdays'][] = [
            'type'  => 'sectionend',
            'id'    => 'script_styling_options'
        ];


        $fields['leave'][] = [
            'title' => __( 'Leave', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Leave settings for this company.', 'erp' ),
            'id'    => 'general_options'
        ];
        $fields['leave'][] = [
            'title' => __( 'Extra Leave', 'erp' ),
            'type'  => 'checkbox',
            'id'    => 'enable_extra_leave',
            'desc'  => __( 'Employees can apply for leave, even when there is no entitlement left.', 'erp' )
        ];
        $fields['leave'][] = [
            'type'  => 'sectionend',
            'id'    => 'script_styling_options'
        ];

        $fields['miscellaneous'][] =[
            'title' => __( 'Miscellaneous', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'HRM miscellaneous settings.', 'erp' ),
            'id'    => 'hrm_miscellaneous'
        ];
        $fields['miscellaneous'][] = [
            'title' => __( 'Remove WP User', 'erp' ),
            'type'  => 'checkbox',
            'id'    => 'erp_hrm_remove_wp_user',
            'desc'  => __( 'Remove wp user on removing employee.', 'erp' )
        ];
        $fields['miscellaneous'][] =[
            'type'  => 'sectionend',
            'id'    => 'hrm_miscellaneous'
        ];
        $fields = apply_filters( 'erp_settings_hr_section_fields', $fields, $section );

        $section = $section === false ? $fields['workdays'] : $fields[$section];

        return $section;
    }
}

return new Settings();
