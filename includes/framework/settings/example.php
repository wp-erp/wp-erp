<?php

/**
 * General class
 */
class ERP_Settings_Example extends ERP_Settings_Page {


    function __construct() {
        $this->id = 'example';
        $this->label = __( 'Example', 'erp' );
        //$this->single_option = true;
        $this->sections = $this->get_sections();
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'Example Settings', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Admin Color Scheme', 'erp' ),
                'desc'      => __( 'Select the color scheme of the admin area.', 'erp' ),
                'id'        => 'admin_color_scheme',
                'default'   => 'green',
                'type'      => 'radio',
                'tooltip'   =>  true,
                'options' => array(
                    'green'    => __( 'Green', 'erp' ),
                    'blue'     => __( 'Blue', 'erp' ),
                    'orange'   => __( 'Orange', 'erp' ),
                    'black'    => __( 'Black', 'erp' ),
                    'alizarin' => __( 'Alizarin', 'erp' ),
                    'pumpkin'  => __( 'Pumpkin', 'erp' ),
                )
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_example', $fields );
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
                'id'    => 'blogname_test',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'erp' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'Site Description', 'erp' ),
                'id'    => 'blogdescription_test',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'erp' ),
                'type'  => 'text',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        $fields['mishu'] = array(

            array( 'title' => __( 'Mishu', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'mishu Site Name', 'erp' ),
                'id'    => 'blogname',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'erp' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'mishu Site Description', 'erp' ),
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

return new ERP_Settings_Example();