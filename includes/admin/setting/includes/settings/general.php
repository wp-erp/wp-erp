<?php

/**
 * General class
 */
class bedIQ_Settings_General extends bedIQ_Settings_Page {


    function __construct() {
        $this->id = 'general';
        $this->label = 'General';
        $this->single_option = true;
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'General Options', 'bediq' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Site Name', 'bediq' ),
                'id'    => 'blogname',
                'desc'  => __( 'The name of your site. If you don\'t use a logo, this name will be displayed instead.', 'bediq' ),
                'type'  => 'text',
            ),
            array(
                'title' => __( 'Site Description', 'bediq' ),
                'id'    => 'blogdescription',
                'desc'  => __( 'This will help peoples and search engines to find your site.', 'bediq' ),
                'type'  => 'text',
            ),

            array(
                'title'     => __( 'Footer Text', 'bediq' ),
                'desc'      => __( 'You can change the footer text by entering your custom text here.', 'bediq' ),
                'id'        => 'footer_text',
                'default'   => 'Powered by <a href="http://bediq.com" title="Responsive Hotel Websites">bedIQ</a>',
                'type'      => 'textarea',
                'custom_attributes'   =>  array(
                    'rows' => 3,
                    'cols' => 45
                ),
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'bediq_settings_general', $fields );
    }
}

return new bedIQ_Settings_General();