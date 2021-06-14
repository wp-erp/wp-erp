<?php

namespace WeDevs\ERP\Framework;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 */
class Ajax_Handler {
    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for Framework
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function __construct () {
        add_action( 'admin_init', [ $this, 'init_actions' ] );
    }

    /**
     * Init all actions
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function init_actions () {
        $this->action( 'wp_ajax_erp-settings-save', 'erp_settings_save' );
        $this->action( 'wp_ajax_erp-settings-get-data', 'erp_settings_get_data' );
    }

    /**
     * Save Settings Data For HRM's sections
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save() {
        $this->verify_nonce( 'erp-settings-nonce' );

        $has_not_permission = ! current_user_can( 'manage_options' );
        $module             = sanitize_text_field( wp_unslash( $_POST['module'] ) );
        $section            = sanitize_text_field( wp_unslash( $_POST['section'] ) );

        switch ( $module ) {
            case 'general':
                $settings           = ( new \WeDevs\ERP\Framework\Settings\ERP_Settings_General() );
                break;

            case 'erp-hr':
                $settings           = ( new \WeDevs\ERP\HRM\Settings() );
                $has_not_permission = $has_not_permission && ! current_user_can( 'erp_hr_manager' );
                break;

            case 'erp-ac':
                $settings           = ( new \WeDevs\ERP\Accounting\Includes\Classes\Settings() );
                $has_not_permission = $has_not_permission && ! current_user_can( 'erp_ac_manager' );
                break;

            case 'erp-crm':
                $settings           = ( new \WeDevs\ERP\CRM\CRM_Settings() );
                $has_not_permission = $has_not_permission && ! current_user_can( 'erp_crm_manager' );
                break;

            default:
                $settings = ( new \WeDevs\ERP\Framework\ERP_Settings_Page() );
                break;
        }

        if ( $has_not_permission ) {
            $this->send_error( erp_get_message ( ['type' => 'error_permission'] ) );
        }

        $result = $settings->save( $section );

        if ( is_wp_error( $result ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_process'] ) );
        }

        $this->send_success( [
            'message' => erp_get_message( [ 'type' => 'save_success', 'additional' => 'Settings' ] )
        ] );
    }

    /**
     * Get Settings Data For Common Sections
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_data() {
        $this->verify_nonce( 'erp-settings-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_permission'] ) );
        }

        $data = $this->process_settings_data( $_POST );

        if ( is_wp_error( $data ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_process'] ) );
        }

        $this->send_success( $data );
    }


    /**
     * Get Options For Settings
     *
     * @since 1.8.6
     *
     * @param array $options - Setting options
     *
     * @return array $data settings data
     */
    function process_settings_data ( $options = [] ) {
        $data               = [];
        $single_option_data = [];

        if ( ! empty ( $options['single_option'] ) ) {
            $single_option_id   = 'erp_settings_' . $options['single_option'];

            // If sub_section_id provided, then append it to single_option_id to get data from database
            // Modify it, since In database, it's stored like `erp_settings_{section}_{sub_section_id}`
            if ( ! empty ( $options['sub_section_id'] ) && $options['single_option'] !== $options['sub_section_id']) {
                $single_option_id .= '_' . $options['sub_section_id'];
            }

            $single_option_data = ( array ) get_option( $single_option_id );
        }

        foreach ( $options as $option ) {
            if ( ! empty ( $option['id'] ) ) {
                $option_value = count ( $single_option_data ) === 0 ? get_option( $option['id'] ) : $single_option_data[ $option['id'] ];

                if ( empty ( $option_value ) && $option['type'] !== 'select' ) {
                    $option_value = ! empty ( $option['default'] ) ? $option['default'] : '';
                }

                // Process option value for different type input
                switch ( $option['type'] ) {
                    case 'checkbox':
                        $option_value = $option_value === 'yes' ? true : false;
                        break;

                    case 'image':
                        $option_value = (int) $option_value;
                        $option_value = $option_value ? wp_get_attachment_url( $option_value ) : '';

                    default:
                        break;
                }

                $option['value'] = $option_value;

                array_push( $data, $option );
            }
        }

        return $data;
    }
}
