<?php

/**
 * General class
 */
class ERP_Settings_API_Keys extends ERP_Settings_Page {


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

            array( 'title' => __( 'API Keys', 'erp' ), 'type' => 'title', 'desc' => __( 'Various API keys and secrets that are needed for various services!', 'erp' ), 'id' => 'general_options' ),

            array(
                'title' => __( 'Google Analytics', 'erp' ),
                'id'    => 'google_analytics',
                'desc'    => __( 'Enter your google analytics account ID.', 'erp'),
                'type'  => 'text',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_api_keys', $fields );
    }
}

return new ERP_Settings_API_Keys();