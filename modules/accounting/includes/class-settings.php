<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * General class
 */
class Settings extends ERP_Settings_Page {


    function __construct() {
        $this->id            = 'accounting';
        $this->label         = __( 'Accounting', 'erp' );
        $this->single_option = true;
        $this->sections      = $this->get_sections();

        add_action( 'erp_admin_field_ac_tax_list', [ $this, 'ac_tax_list' ] );
    }

   /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'currency_option' => __( 'Currency Settings', 'erp' ),
            'general'         => __( 'Invoice Formatting', 'erp' ),
            'erp_ac_tax'      => __( 'Sales Tax', 'erp' )
        );

        return apply_filters( 'erp_get_sections_' . $this->id, $sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {

        $fields['general'] = array(
            array( 'title' => __( '', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title'   => __( 'Sales Payment', 'erp' ),
                'id'      => 'erp_ac_payment',
                'type'    => 'text',
                'default' => erp_ac_get_default_invoice_prefix( 'erp_ac_payment' ),
                'desc'    => __( 'Sales payment invoice format. Rearrange if you like. <strong>prefix-{id}, {id}-postfix, prefix-{id}-postfix</strong>', 'erp' )
            ),

            array(
                'title'   => __( 'Sales Invoice', 'erp' ),
                'id'      => 'erp_ac_invoice',
                'type'    => 'text',
                'default' => erp_ac_get_default_invoice_prefix( 'erp_ac_invoice' ),
                'desc'    => __( 'Sales invoice format. Rearrange if you like. <strong>prefix-{id}, {id}-postfix, prefix-{id}-postfix</strong>', 'erp' )
            ),

            array(
                'title'   => __( 'Expense Voucher', 'erp' ),
                'id'      => 'erp_ac_payment_voucher',
                'type'    => 'text',
                'default' => erp_ac_get_default_invoice_prefix( 'erp_ac_payment_voucher' ),
                'desc'    => __( 'Expense voucher invoice format. Rearrange if you like. <strong>prefix-{id}, {id}-postfix, prefix-{id}-postfix</strong>', 'erp' )
            ),

            array(
                'title'   => __( 'Expense Credit', 'erp' ),
                'id'      => 'erp_ac_vendor_credit',
                'type'    => 'text',
                'default' => erp_ac_get_default_invoice_prefix( 'erp_ac_vendor_credit' ),
                'desc'    => __( 'Expense credit invoice format. Rearrange if you like. <strong>prefix-{id}, {id}-postfix, prefix-{id}-postfix</strong>', 'erp' )
           ),

            array(
                'title'   => __( 'Journal', 'erp' ),
                'id'      => 'erp_ac_journal',
                'type'    => 'text',
                'default' => erp_ac_get_default_invoice_prefix( 'erp_ac_journal' ),
                'desc'    => __( 'Journal invoice format. Rearrange if you like. <strong>prefix-{id}, {id}-postfix, prefix-{id}-postfix</strong>', 'erp' )
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),
        );

        $fields['currency_option'] = array(

            array( 'title' => __( '', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title'   => __( 'Currency', 'erp' ),
                'id'      => 'erp_ac_currency',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => erp_get_currency_list_with_symbol(),
                'default' => 'USD'
            ),
            array(
                'title'   => __( 'Currency Position', 'erp' ),
                'id'      => 'erp_ac_currency_position',
                'type'    => 'select',
                'class'   => 'erp-select2',
                'options' => array(
                    'left'        => sprintf( '%1$s (%2$s99.99)', __( 'Left', 'erp' ), erp_ac_get_currency_symbol() ),
                    'right'       => sprintf( '%1$s (99.99%2$s)', __( 'Right', 'erp' ), erp_ac_get_currency_symbol() ),
                    'left_space'  => sprintf( '%1$s (%2$s 99.99)', __( 'Left with space', 'erp' ), erp_ac_get_currency_symbol() ),
                    'right_space' => sprintf( '%1$s (99.99 %2$s)', __( 'Right with space', 'erp' ), erp_ac_get_currency_symbol() ),
                ),
            ),

            array(
                'title'   => __( 'Thousand Separator', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_ac_th_separator',
                'default' => ','
            ),


            array(
                'title'   => __( 'Decimal Separator', 'erp' ),
                'id'      => 'erp_ac_de_separator',
                'type'    => 'text',
                'default' => '.'
            ),

            array(
                'title'   => __( 'Number of Decimals', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_ac_nm_decimal',
                'default' => 2
            ),

            array(
                'title'   => __( 'PDF Theme Color', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_ac_pdf_theme_color',
                'class'   => 'erp-color-picker'
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        $fields['erp_ac_tax'] = array(
            array(
                'title' => __( 'Sales Taxes', 'erp' ),
                'type'  => 'title',
                'desc'  => __( '', 'erp' ),
                'id'    => 'erp-ac-tax-options'
            ),
            array(
                'type' => 'ac_tax_list'
            ),
            array( 'type' => 'sectionend', 'id' => 'script_styling_optionsjj' ),
        );

        $fields['erp_ac_tax']['submit_button'] = false;

        $section = $section === false ? $fields['checkout'] : isset( $fields[$section] ) ? $fields[$section] : array();

        return apply_filters( 'erp_ac_settings_section_fields_' . $this->id , $section );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'Accounting Settings', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

            array(
                'title'   => __( 'Home Currency', 'erp' ),
                'id'      => 'base_currency',
                'desc'    => __( 'The base currency of the system.', 'erp' ),
                'type'    => 'select',
                'options' => erp_get_currencies()
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings


        return apply_filters( 'erp_ac_settings_general', $fields );
    }

    function ac_tax_list() {
        require_once WPERP_ACCOUNTING_VIEWS . '/settings/tax.php';
    }
}

return new Settings();
