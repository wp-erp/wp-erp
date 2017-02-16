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
        $this->id             = 'erp-hr';
        $this->label          = __( 'HR', 'erp' );
        $this->single_option  = true;
        $this->arrayed_option = 'erp_settings_erp-hr_workdays';
        $this->sections       = $this->get_sections();
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'workdays' => __( 'Workdays', 'erp' ),
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
            'erp_settings_erp-hr_workdays[mon]' => __( 'Monday', 'erp' ),
            'erp_settings_erp-hr_workdays[tue]' => __( 'Tuesday', 'erp' ),
            'erp_settings_erp-hr_workdays[wed]' => __( 'Wednesday', 'erp' ),
            'erp_settings_erp-hr_workdays[thu]' => __( 'Thursday', 'erp' ),
            'erp_settings_erp-hr_workdays[fri]' => __( 'Friday', 'erp' ),
            'erp_settings_erp-hr_workdays[sat]' => __( 'Saturday', 'erp' ),
            'erp_settings_erp-hr_workdays[sun]' => __( 'Sunday', 'erp' )
        );

        $fields = [];

        $fields['workdays'][] = [
            'title' => __( 'Work Days', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Week day settings for this company.', 'domain' ),
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

        $fields = apply_filters( 'erp_settings_hr_section_fields', $fields, $section );

        $section = $section === false ? $fields['workdays'] : $fields[$section];

        return $section;
    }
}

return new Settings();
