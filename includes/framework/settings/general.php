<?php

use \WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * General class
 */
class ERP_Settings_General extends ERP_Settings_Page {

    function __construct() {
        $this->id    = 'general';
        $this->label = __( 'General', 'wp-erp' );

        add_action( 'erp_admin_field_erp_api_key', array( $this, 'erp_api_key' ) );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = array(

            array( 'title' => __( 'General Options', 'wp-erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

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

            array(
                'type'  => 'erp_api_key',
            ),

            array( 'type' => 'sectionend', 'id' => 'script_styling_options' ),

        ); // End general settings

        return apply_filters( 'erp_settings_general', $fields );
    }

    /**
     * Display API settings view.
     *
     * @return void
     */
    public function erp_api_key() {
        $wp_erp_api_key         = get_option( 'wp_erp_apikey', null );
        $wp_erp_api_active      = get_option( 'wp_erp_api_active', false );
        $wp_erp_api_email_count = get_option( 'wp_erp_api_email_count', 0 );

        if( $wp_erp_api_key ) {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <span class="dashicons dashicons-admin-network"></span><?php echo __( 'API Key', 'wp-erp' ) ?>
            </th>
            <td class="forminp forminp-text">
                <p><?php echo $wp_erp_api_key ?> <span class="dashicons dashicons-<?php echo ($wp_erp_api_active) ? 'yes green' : 'no red' ?>"></p>
                <br />
                <a id="wp-erp-disconnect-api" class="button-secondary">Disconnect</a>
            </td>
        </tr>
        <?php
        } else {
        ?>
        <tr valign="top" id="erp-activation-container">
            <th scope="row" class="titledesc" colspan="2">
                <div class="erp-activation-cloud-prompt">
                    <div class="activation-prompt-text">
                        <?php _e( "You're awesome for installing <strong>WP ERP!</strong> Get API Key to get access to wperp <em>cloud</em> features!", "wp-erp" ) ?>
                    </div>

                    <div class="activation-form-container">
                        <input type="email" name="email" placeholder="email@example.com" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
                        <button class="button-primary" id="get-api-key"><?php _e( 'Get API Key', 'wp-erp' ); ?></button>
                    </div>
                </div>
            </th>
        </tr>
        <?php
        }
    }
}

return new ERP_Settings_General();