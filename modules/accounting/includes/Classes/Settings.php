<?php

namespace WeDevs\ERP\Accounting\Classes;

use WeDevs\ERP\Settings\Template;

/**
 * General class
 */
class Settings extends Template {

    public $id;
    public $label;
    public $single_option;
    public $sections;
    public $icon;
    
    public function __construct() {
        $this->id            = 'erp-ac';
        $this->label         = __( 'Accounting', 'erp' );
        $this->single_option = true;
        $this->sections      = $this->get_sections();
        $this->icon          = WPERP_ASSETS . '/images/wperp-settings/accounting.png';
    }

    /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = [
            'customers'       => __( 'Customers', 'erp' ),
            'currency_option' => __( 'Currency Settings', 'erp' ),
            'opening_balance' => __( 'Financial Years', 'erp' ),
        ];

        return apply_filters( 'erp_get_sections_' . $this->id, $sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '', $all_data = false ) {
        $symbol = erp_acct_get_currency_symbol();

        $fields['customers'] = [
            // [
            //     'title' => __( '', 'erp' ),
            //     'type' => 'title',
            //     'desc' => '',
            //     'id' => 'general_options'
            // ],

            [
                'title' => __( 'Customer Settings', 'erp' ),
                'type'  => 'title',
                'desc'  => __( 'Settings for Accounting customers.', 'erp' ),
                'id'    => 'customer_settings',
            ],

            [
                'title'   => __( 'Auto Import', 'erp' ),
                'id'      => 'customer_auto_import',
                'type'    => 'select',
                'desc'    => __( 'Allow to auto import new crm user as accounting customer.', 'erp' ),
                'options' => [ 1 => __( 'On', 'erp' ), 0 => __( 'Off', 'erp' ) ],
                'default' => 0,
            ],

            [
                'title'   => __( 'Import User&apos;s From', 'erp' ),
                'id'      => 'crm_user_type',
                'type'    => 'multicheck',
                'desc'    => __( 'Selected user type are considered to auto import.', 'erp' ),
                'options' => [ 'contact' => __( 'Contact', 'erp' ), 'company' => __( 'Company', 'erp' ) ],
                'default' => [],
            ],

            [
                'type' => 'sectionend',
                'id'   => 'script_styling_options',
            ],
        ];

        $fields['currency_option'] = [
            [
                'title' => __( 'Currency Settings', 'erp' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'general_options',
            ],

            [
                'title'   => __( 'Currency Position', 'erp' ),
                'id'      => 'erp_ac_currency_position',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => [
                    'left'        => sprintf( '%1$s (%2$s99.99)', __( 'Left', 'erp' ), $symbol ),
                    'right'       => sprintf( '%1$s (99.99%2$s)', __( 'Right', 'erp' ), $symbol ),
                    'left_space'  => sprintf( '%1$s (%2$s 99.99)', __( 'Left with space', 'erp' ), $symbol ),
                    'right_space' => sprintf( '%1$s (99.99 %2$s)', __( 'Right with space', 'erp' ), $symbol ),
                ],
            ],

            [
                'title'   => __( 'Thousand Separator', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_ac_th_separator',
                'default' => ',',
            ],

            [
                'title'   => __( 'Decimal Separator', 'erp' ),
                'id'      => 'erp_ac_de_separator',
                'type'    => 'text',
                'default' => '.',
            ],

            // array(
            //     'title'   => __( 'Number of Decimals', 'erp' ),
            //     'type'    => 'text',
            //     'id'      => 'erp_ac_nm_decimal',
            //     'default' => 2
            // ),

            [
                'type' => 'sectionend',
                'id'   => 'script_styling_options',
            ],
        ]; // End currency options settings

        $fields['opening_balance'] = [
            [
                'title' => __( 'Financial Years', 'erp' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'erp_acct_ob_options',
            ],
            [
                'type' => 'acct_opening_balance',
                'id'   => 'erp_ac_ob_years',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'script_styling_options',
            ],
        ]; // End opening balance settings

        $fields = apply_filters( 'erp_settings_acct_section_fields', $fields, $section );

        if ( false !== $section ) {
            if ( isset( $fields[ $section ] ) ) {
                $section = $fields[ $section ];
            } else {
                $section = [];
            }
        } else {
            $section = $fields['checkout'];
        }

        foreach ( $this->get_sections() as $sec => $name ) {
            if ( empty( $fields[ $sec ] ) ) {
                $fields = apply_filters( 'erp_ac_settings_section_fields_', $fields, $sec );
            }
        }

        if ( $all_data ) {
            return $fields;
        }

        return apply_filters( 'erp_ac_settings_section_fields_' . $this->id, $section );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_settings() {
        $fields = [
            [
                'title' => __( 'Accounting Settings', 'erp' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'general_options',
            ],

            [
                'title'   => __( 'Home Currency', 'erp' ),
                'id'      => 'base_currency',
                'desc'    => __( 'The base currency of the system.', 'erp' ),
                'type'    => 'select',
                'options' => erp_get_currencies(),
            ],

            [
                'type' => 'sectionend',
                'id'   => 'script_styling_options',
            ],
        ]; // End general settings

        return apply_filters( 'erp_ac_settings_general', $fields );
    }
}

return new Settings();
