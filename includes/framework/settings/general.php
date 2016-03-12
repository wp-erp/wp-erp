<?php

use \WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * General class
 */
class ERP_Settings_General extends ERP_Settings_Page {

    function __construct() {
        $this->id    = 'general';
        $this->label = 'General';
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
                'desc'    => __( 'The date the company officially started.', 'wp-erp' ),
                'class'   => 'erp-date-field',
                'tooltip' => true,
            ),

            array(
                'title'   => __( 'Financial Year Starts', 'wp-erp' ),
                'id'      => 'gen_financial_month',
                'type'    => 'select',
                'options' => erp_months_dropdown(),
                'desc'    => __( 'Financial and tax calculation starts from this month of every year.', 'wp-erp' ),
                'tooltip' => false,
            ),

            array(
                'title'   => __( 'Date Format', 'wp-erp' ),
                'id'      => 'date_format',
                'desc'    => __( 'Format of date to show accross the system.', 'wp-erp' ),
                'tooltip' => true,
                'type'    => 'select',
                'options' => [
                    'm-d-Y' => 'mm-dd-yyyy',
                    'd-m-Y' => 'dd-mm-yyyy',
                    'm/d/Y' => 'mm/dd/yyyy',
                    'd/m/Y' => 'dd/mm/yyyy',
                    'Y-m-d' => 'yyyy-mm-dd',
                ]
            ),

            array(
                'title'   => __( 'Enable Debug Mode', 'wp-erp' ),
                'id'      => 'erp_debug_mode',
                'type'    => 'select',
                'options' => [ 1 => __('On', 'wp-erp'), 0 => __( 'Off', 'wp-erp') ],
                'desc'    => __( 'Switching testing or producting mode', 'wp-erp' ),
                'tooltip' =>  true,
                'default' =>  0,
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_general', $fields );
    }

}

return new ERP_Settings_General();