<?php

/**
 * General class
 */
class bedIQ_Settings_Others extends bedIQ_Settings_Page {


    function __construct() {
        $this->id = 'others';
        $this->label = __( 'Other', 'bediq' );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'Other Settings', 'bediq' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title' => __( 'Admin Color Scheme', 'bediq' ),
                'desc'      => __( 'Select the color scheme of the admin area.', 'bediq' ),
                'id'        => 'admin_color_scheme',
                'default'   => 'green',
                'type'      => 'radio',
                'tooltip'   =>  true,
                'options' => array(
                    'green'    => __( 'Green', 'bediq' ),
                    'blue'     => __( 'Blue', 'bediq' ),
                    'orange'   => __( 'Orange', 'bediq' ),
                    'black'    => __( 'Black', 'bediq' ),
                    'alizarin' => __( 'Alizarin', 'bediq' ),
                    'pumpkin'  => __( 'Pumpkin', 'bediq' ),
                )
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'bediq_settings_others', $fields );
    }
}

return new bedIQ_Settings_Others();