<?php

/**
 * General class
 */
class bedIQ_Settings_API_Keys extends bedIQ_Settings_Page {


    function __construct() {
        $this->id = 'api_keys';
        $this->label = 'API Keys';
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'API Keys', 'bediq' ), 'type' => 'title', 'desc' => __( 'Various API keys and secrets that are needed for various services!', 'bediq' ), 'id' => 'general_options' ),

            array(
                'title' => __( 'Google Analytics', 'bediq' ),
                'id'    => 'google_analytics',
                'desc'    => __( 'Enter your google analytics account ID.', 'bediq'),
                'type'  => 'text',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'bediq_settings_api_keys', $fields );
    }
}

return new bedIQ_Settings_API_Keys();