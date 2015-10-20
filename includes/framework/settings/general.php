<?php

/**
 * General class
 */
class ERP_Settings_General extends ERP_Settings_Page {


    function __construct() {
        $this->id = 'general';
        $this->label = 'General';
        $this->single_option = true;
        //$this->sections = $this->get_sections();
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'General Options', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),
            
            array(
                'title'   => __( 'Company Start Date', 'wp-erp' ),
                'id'      => 'gen_com_start',
                'type'    => 'text',
                'desc'      => __( 'The date the company officially started.', 'wp-erp' ),
                'class'   => 'erp-date-field',
                'tooltip'   =>  true,
            ),

            array(
                'title'   => __( 'First Month of Financial Year', 'wp-erp' ),
                'id'      => 'gen_financial_month',
                'type'    => 'select',
                'options' => erp_months_dropdown(),
                'desc'      => __( 'Financial and tax calculation starts from this month of every year.', 'wp-erp' ),
                'tooltip'   =>  true,
            ),

            array(
                'title'   => __( 'Enable Debug mood', 'wp-erp' ),
                'id'      => 'erp_debug_mode',
                'type'    => 'select',
                'options' => [ 1 => __('On', 'wp-erp'), 0 => __( 'Off', 'wp-erp') ],
                'desc'      => __( 'Switching testing or producting mood', 'wp-erp' ),
                'tooltip'   =>  true,
                'default'   =>  0,
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_general', $fields );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        
        $fields['checkout'] = array(

            array( 'title' => __( 'General Options', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Site Name', 'erp' ),
                'id'    => 'blogname',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'erp' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'Site Description', 'erp' ),
                'id'    => 'blogdescription',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'erp' ),
                'type'  => 'text',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        $fields['mishu'] = array(

            array( 'title' => __( 'Mishu', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Site Name', 'erp' ),
                'id'    => 'blogname',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'erp' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'Site Description', 'erp' ),
                'id'    => 'blogdescription',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'erp' ),
                'type'  => 'text',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings
       
        $section = $section === false ? $fields['checkout'] : $fields[$section];
        return apply_filters( 'erp_settings_general_section', $section );
    } 

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {

        $sections = array(
            'checkout' => __( 'Checkout Options', 'erp' ),
            'mishu' => __( 'Mishu', 'erp' )
        );

        
        return apply_filters( 'erp_get_sections_' . $this->id, $sections );
    }
}

return new ERP_Settings_General();