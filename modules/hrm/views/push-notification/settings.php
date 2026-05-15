<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = new \WeDevs\ERP\HRM\PushNotification\Settings();
?>
<div class="erp-push-notification-settings">
    <form method="post" action="options.php">
        <?php settings_fields( 'erp_integration_settings_erp-push-notification' ); ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'OneSignal App ID', 'erp' ); ?></th>
                    <td>
                        <input type="text"
                               name="erp_integration_settings_erp-push-notification[erp_push_onesignal_app_id]"
                               value="<?php echo esc_attr( $settings->get_option( 'erp_push_onesignal_app_id' ) ); ?>"
                               class="regular-text"
                        />
                        <p class="description"><?php esc_html_e( 'Your OneSignal Application ID.', 'erp' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'REST API Key', 'erp' ); ?></th>
                    <td>
                        <input type="text"
                               name="erp_integration_settings_erp-push-notification[erp_push_onesignal_rest_api_key]"
                               value="<?php echo esc_attr( $settings->get_option( 'erp_push_onesignal_rest_api_key' ) ); ?>"
                               class="regular-text"
                        />
                        <p class="description"><?php esc_html_e( 'Your OneSignal REST API Key.', 'erp' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable for Leave Requests', 'erp' ); ?></th>
                    <td>
                        <input type="checkbox"
                               name="erp_integration_settings_erp-push-notification[erp_push_enable_leave]"
                               value="yes"
                               <?php checked( 'yes', $settings->get_option( 'erp_push_enable_leave' ) ); ?>
                        />
                        <label><?php esc_html_e( 'Send push notifications for leave request events.', 'erp' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable for Announcements', 'erp' ); ?></th>
                    <td>
                        <input type="checkbox"
                               name="erp_integration_settings_erp-push-notification[erp_push_enable_announcement]"
                               value="yes"
                               <?php checked( 'yes', $settings->get_option( 'erp_push_enable_announcement' ) ); ?>
                        />
                        <label><?php esc_html_e( 'Send push notifications when a new announcement is published.', 'erp' ); ?></label>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
