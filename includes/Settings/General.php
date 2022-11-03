<?php

namespace WeDevs\ERP\Settings;

/**
 * General class
 */
class General extends Template {
    
    /**
     * Class constructor
     */
    public function __construct() {
        $this->id    = 'general';
        $this->label = __( 'General', 'erp' );
        $this->icon = WPERP_ASSETS . '/images/wperp-settings/general.png';
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {
        $country = \WeDevs\ERP\Countries::instance();

        $fields = [
            [ 'title' => __( 'General Options', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ],
            [
                'title'   => __( 'Company Start Date', 'erp' ),
                'id'      => 'gen_com_start',
                'type'    => 'text',
                'desc'    => __( 'The date the company officially started.', 'erp' ),
                'class'   => 'erp-date-field',
                'tooltip' => true,
            ],
            [
                'title'   => __( 'Financial Year Starts', 'erp' ),
                'id'      => 'gen_financial_month',
                'type'    => 'select',
                'options' => erp_months_dropdown(),
                'desc'    => __( 'Financial and tax calculation starts from this month of every year.', 'erp' ),
                'tooltip' => false,
            ],
            [
                'title'   => __( 'Date Format', 'erp' ),
                'id'      => 'date_format',
                'desc'    => __( 'Format of date to show accross the system.', 'erp' ),
                'tooltip' => true,
                'type'    => 'select',
                'options' => [
                    'm-d-Y' => 'mm-dd-yyyy',
                    'd-m-Y' => 'dd-mm-yyyy',
                    'd.m.Y' => 'dd.mm.yyyy',
                    'm/d/Y' => 'mm/dd/yyyy',
                    'd/m/Y' => 'dd/mm/yyyy',
                    'Y-m-d' => 'yyyy-mm-dd',
                ],
            ],
            [
                'title'   => __( 'Currency', 'erp' ),
                'id'      => 'erp_currency',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => erp_get_currencies_for_dropdown(),
                'default' => '1',
            ],
            [
                'title'   => __( 'Default Country (HRM, CRM, AC)', 'erp' ),
                'id'      => 'erp_country',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => $country->get_countries( -1 ),
            ],
            [
                'title'   => __( 'Role Based Login Redirection', 'erp' ),
                'id'      => 'role_based_login_redirection',
                'type'    => 'select',
                'options' => [ 1 => __( 'On', 'erp' ), 0 => __( 'Off', 'erp' ) ],
                'desc'    => __( 'User will be redirected to the related page regrading their role after login.', 'erp' ),
                'tooltip' => true,
                'default' => 0,
            ],
            [
                'title'   => __( 'Enable Debug Mode', 'erp' ),
                'id'      => 'erp_debug_mode',
                'type'    => 'select',
                'options' => [ 1 => __( 'On', 'erp' ), 0 => __( 'Off', 'erp' ) ],
                'desc'    => __( 'Switching testing or production mode', 'erp' ),
                'tooltip' => true,
                'default' => 0,
            ],

            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],
        ]; // End general settings

        return apply_filters( 'erp_settings_general', $fields );
    }

    public function get_section_fields( $section = false ) {
        return $this->get_settings();
    }
}
