<?php

/**
 * Class to handle updates for version 1.17.12
 *
 * @since 1.17.12
 */
class ERP_Update_1_17_12 {

    /**
     * Class constructor.
     *
     * @since 1.17.12
     */
    public function __construct() {
        $this->enable_email_templates();
    }

    /**
     * Mark email templates without a saved enable state as enabled
     *
     * The enable switch was ignored before this version, so every template was sent.
     * Saving the state keeps them sending now that the switch is honoured.
     *
     * @since 1.17.12
     *
     * @return void
     */
    public function enable_email_templates() {
        global $wpdb;

        $option_names = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like( 'erp_email_settings_' ) . '%'
            )
        );

        foreach ( $option_names as $option_name ) {
            $settings = get_option( $option_name );

            if ( ! is_array( $settings ) || isset( $settings['is_enable'] ) ) {
                continue;
            }

            $settings['is_enable'] = 'yes';
            update_option( $option_name, $settings );
        }
    }
}

new ERP_Update_1_17_12();
