<?php

/**
 * General class
 */
class ERP_Settings_General extends ERP_Settings_Page {


    function __construct() {
        $this->id = 'general';
        $this->label = 'General';
        $this->single_option = true;
        $this->sections = $this->get_sections();
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

            array(
                'title'     => __( 'Footer Text', 'erp' ),
                'desc'      => __( 'You can change the footer text by entering your custom text here.', 'erp' ),
                'id'        => 'footer_text',
                'default'   => 'Powered by <a href="http://erp.com" title="Responsive Hotel Websites">erp</a>',
                'type'      => 'textarea',
                'custom_attributes'   =>  array(
                    'rows' => 3,
                    'cols' => 45
                ),
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_general', $fields );
    }

    public function get_section_fields( $section = '' ) {

        $fields[''] = array(

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
       
        return $section === false ? $fields[''] : $fields[$section];
    } 

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            '' => __( 'Checkout Options', 'erp' ),
            'mishu' => __( 'Mishu', 'erp' )
        );

        
        return apply_filters( 'erp_get_sections_' . $this->id, $sections );
    }
}

return new ERP_Settings_General();