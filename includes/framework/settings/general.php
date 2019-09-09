<?php

use \WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * General class
 */
class ERP_Settings_General extends ERP_Settings_Page {

    function __construct() {
        $this->id    = 'general';
        $this->label = __( 'General', 'erp' );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $country = \WeDevs\ERP\Countries::instance();

        $fields = array(

            array( 'title' => __( 'General Options', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title'   => __( 'Company Start Date', 'erp' ),
                'id'      => 'gen_com_start',
                'type'    => 'text',
                'desc'    => __( 'The date the company officially started.', 'erp' ),
                'class'   => 'erp-date-field',
                'tooltip' => true,
            ),

            array(
                'title'   => __( 'Financial Year Starts', 'erp' ),
                'id'      => 'gen_financial_month',
                'type'    => 'select',
                'options' => erp_months_dropdown(),
                'desc'    => __( 'Financial and tax calculation starts from this month of every year.', 'erp' ),
                'tooltip' => false,
            ),

            array(
                'title'   => __( 'Date Format', 'erp' ),
                'id'      => 'date_format',
                'desc'    => __( 'Format of date to show accross the system.', 'erp' ),
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
                'title'   => __( 'Currency', 'erp' ),
                'id'      => 'erp_currency',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => erp_get_currencies_for_dropdown(),
                'default' => '1'
            ),

            array(
                'title'   => __( 'Default Country (HRM, CRM, AC)', 'erp' ),
                'id'      => 'erp_country',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => $country->get_countries( -1 )
            ),

            array(
                'title'   => __( 'Role Based Login Redirection', 'erp' ),
                'id'      => 'role_based_login_redirection',
                'type'    => 'select',
                'options' => [ 1 => __('On', 'erp'), 0 => __( 'Off', 'erp') ],
                'desc'    => __( 'User will be redirected to the related page regrading their role after login.', 'erp' ),
                'tooltip' =>  true,
                'default' =>  0,
            ),
            array(
                'title'   => __( 'Enable Debug Mode', 'erp' ),
                'id'      => 'erp_debug_mode',
                'type'    => 'select',
                'options' => [ 1 => __('On', 'erp'), 0 => __( 'Off', 'erp') ],
                'desc'    => __( 'Switching testing or production mode', 'erp' ),
                'tooltip' =>  true,
                'default' =>  0,
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_general', $fields );
    }
}

return new ERP_Settings_General();
