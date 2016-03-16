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
        $this->label         = __( 'HR', 'wp-erp' );

        $this->sections      = $this->get_sections();
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'workdays' => __( 'Workdays', 'wp-erp' ),
        );

        return $sections;
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        $options = array(
            '8' => __( 'Full Day', 'wp-erp' ),
            '4' => __( 'Half Day', 'wp-erp' ),
            '0' => __( 'Non-working Day', 'wp-erp' )
        );

        $week_days = array(
            'mon' => __( 'Monday', 'wp-erp' ),
            'tue' => __( 'Tuesday', 'wp-erp' ),
            'wed' => __( 'Wednesday', 'wp-erp' ),
            'thu' => __( 'Thursday', 'wp-erp' ),
            'fri' => __( 'Friday', 'wp-erp' ),
            'sat' => __( 'Saturday', 'wp-erp' ),
            'sun' => __( 'Sunday', 'wp-erp' )
        );

        $fields = [];

        $fields['workdays'][] = [
            'title' => __( 'Work Days', 'wp-erp' ),
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

        $fields = apply_filters( 'erp_settings_hr_sections', $fields, $section );

        $section = $section === false ? $fields['workdays'] : $fields[$section];

        return $section;
    }
}

return new Settings();
