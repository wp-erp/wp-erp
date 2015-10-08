<?php

/**
 * General class
 */
class ERP_Settings_Others extends ERP_Settings_Page {


    function __construct() {
        $this->id = 'others';
        $this->label = __( 'Other', 'erp' );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'Other Settings', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

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

        return apply_filters( 'erp_settings_others', $fields );
    }
}

return new ERP_Settings_Others();