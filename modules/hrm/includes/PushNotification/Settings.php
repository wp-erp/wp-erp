<?php
namespace WeDevs\ERP\HRM\PushNotification;

use WeDevs\ERP\Integration;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Push Notification settings page.
 *
 * Registers the OneSignal configuration fields under the ERP Integrations tab.
 *
 * @since 1.0.0
 */
class Settings extends Integration {

    /**
     * Option group key used to retrieve saved values.
     *
     * @var string
     */
    const OPTION_KEY = 'erp_integration_settings_erp-push-notification';

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->id          = 'erp-push-notification';
        $this->title       = __( 'Push Notification', 'erp' );
        $this->description = __( 'Send push notifications via OneSignal.', 'erp' );

        $this->init_settings();
        parent::__construct();
    }

    /**
     * Return the tab title.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Return the tab description.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Initialize form fields.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_settings() {
        $this->form_fields = [
            [
                'title' => __( 'OneSignal', 'erp' ),
                'type'  => 'title',
            ],
            [
                'title'   => __( 'App ID', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_push_onesignal_app_id',
                'desc'    => __( 'Your OneSignal Application ID.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'REST API Key', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_push_onesignal_rest_api_key',
                'desc'    => __( 'Your OneSignal REST API Key.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Enable for Leave Requests', 'erp' ),
                'type'    => 'checkbox',
                'id'      => 'erp_push_enable_leave',
                'desc'    => __( 'Send push notifications for leave request events.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Enable for Announcements', 'erp' ),
                'type'    => 'checkbox',
                'id'      => 'erp_push_enable_announcement',
                'desc'    => __( 'Send push notifications when a new announcement is published.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Enable for Holidays', 'erp' ),
                'type'    => 'checkbox',
                'id'      => 'erp_push_enable_holiday',
                'desc'    => __( 'Send push notifications to all employees when a new holiday is created.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Enable for Birthdays', 'erp' ),
                'type'    => 'checkbox',
                'id'      => 'erp_push_enable_birthday',
                'desc'    => __( 'Send a birthday push notification to the employee on their birthday.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Notify All Employees on Birthday', 'erp' ),
                'type'    => 'checkbox',
                'id'      => 'erp_push_birthday_notify_all',
                'desc'    => __( 'Also notify all other employees about their colleague\'s birthday.', 'erp' ),
                'default' => '',
            ],
        ];
        // if pro version is active, add job apply notification settings
        if( defined( 'ERP_PRO_PLUGIN_VERSION' ) ) {
            $this->form_fields = array_merge( $this->form_fields, [
                [
                    'title'   => __( 'Enable for Job Applications', 'erp' ),
                    'type'    => 'checkbox',
                    'id'      => 'erp_push_enable_job_apply',
                    'desc'    => __( 'Send push notifications to HR when a new job application is submitted.', 'erp' ),
                    'default' => '',
                ],
            ] );
        }
    }

    /**
     * Retrieve the persisted value for a specific field.
     *
     * @since 1.0.0
     *
     * @param string $field_id Field option key.
     * @param mixed  $default  Default value when the option is not set.
     *
     * @return mixed
     */
    public function get_option( $field_id, $default = '' ) {
        return erp_get_option( $field_id, self::OPTION_KEY, $default );
    }

    /**
     * Return the option ID used to persist settings.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_option_id() {
        return self::OPTION_KEY;
    }
}
