<?php
namespace WeDevs\ERP\HRM\PushNotification;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Push Notification module for WP ERP.
 *
 * Dispatches OneSignal push notifications for:
 *   - New leave requests (notifies HR managers)
 *   - Approved / rejected leave requests (notifies the employee)
 *   - New HR announcements (notifies assigned employees)
 *
 * @since 1.0.0
 */
class Module {

    /**
     * Holds the NotificationHandler instance.
     *
     * @var NotificationHandler|null
     */
    private $handler;

    /**
     * Initializes the Module (singleton).
     *
     * @since 1.0.0
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init_handler();
        $this->init_actions();
        $this->init_filters();
    }

    /**
     * Build the NotificationHandler using the configured OneSignal credentials.
     *
     * When credentials are missing the handler stays null and all callbacks
     * become no-ops.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function init_handler() {
        $app_id       = $this->get_option( 'erp_push_onesignal_app_id' );
        $rest_api_key = $this->get_option( 'erp_push_onesignal_rest_api_key' );

        if ( empty( $app_id ) || empty( $rest_api_key ) ) {
            return;
        }

        $provider      = new OneSignal( $app_id, $rest_api_key );
        $this->handler = new NotificationHandler( $provider );
    }

    /**
     * Register WordPress action hooks.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function init_actions() {
        // Leave request hooks.
        add_action( 'erp_hr_leave_new',              [ $this, 'on_leave_request_new' ],      10, 2 );
        add_action( 'erp_hr_leave_request_approved', [ $this, 'on_leave_request_approved' ], 10, 2 );
        add_action( 'erp_hr_leave_request_reject',   [ $this, 'on_leave_request_rejected' ], 10, 2 );

        // Announcement hook.
        add_action( 'hr_announcement_insert_assignment', [ $this, 'on_announcement' ], 10, 2 );

        // Holiday reminder: runs daily via cron, fires N days before holiday start.
        add_action( 'erp_daily_scheduled_events', [ $this, 'on_holiday_reminder_check' ] );

        // Birthday hooks (fired per employee by cron, same as birthday wish email).
        add_action( 'erp_hr_happened_birthday_today', [ $this, 'on_birthday' ],                   10, 1 );
        add_action( 'erp_hr_happened_birthday_today', [ $this, 'on_birthday_notify_colleagues' ], 20, 1 );

        // Job apply hook.
        add_action( 'erp_rec_applied_job', [ $this, 'on_job_apply' ], 10, 1 );

        // Add checkbox to announcement creation form.
        add_action( 'hr_announcement_table_last', [ $this, 'announcement_push_checkbox' ] );
    }

    /**
     * Register WordPress filter hooks.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function init_filters() {
        add_filter( 'erp_integration_classes', [ $this, 'add_settings_page' ] );
    }

    /**
     * Register the settings integration page.
     *
     * @since 1.0.0
     *
     * @param array $settings Existing integration settings.
     *
     * @return array
     */
    public function add_settings_page( $settings ) {
        $settings['push_notification'] = new Settings();

        return $settings;
    }

    /**
     * Add the "Send Push Notification" checkbox to the announcement edit screen.
     *
     * @since 1.0.0
     *
     * @param \WP_Post $post The current announcement post object.
     *
     * @return void
     */
    public function announcement_push_checkbox( $post ) {
        if ( ! $this->is_push_enabled_for( 'announcement' ) ) {
            return;
        }

        $push_enabled = get_post_meta( $post->ID, '_announcement_send_push', true );
        ?>
        <tr>
            <th><?php esc_html_e( 'Send Push Notification', 'erp' ); ?></th>
            <td>
                <input id="hr-announcement-push-check"
                       name="hr_announcement_send_push"
                       type="checkbox"
                       <?php checked( 'on', $push_enabled ); ?>
                />
                &nbsp;
                <span><?php esc_html_e( 'Check to send push notification', 'erp' ); ?></span>
            </td>
        </tr>
        <?php
    }

    /**
     * Persist the push checkbox meta and dispatch on announcement save.
     *
     * @since 1.0.0
     *
     * @param array $employees Array of employee user IDs.
     * @param int   $post_id   Announcement post ID.
     *
     * @return void
     */
    public function on_announcement( $employees, $post_id ) {
        if ( ! current_user_can( 'manage_erp' ) && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Persist the checkbox value whenever the announcement is saved.
        $push_enabled = isset( $_REQUEST['hr_announcement_send_push'] ) // phpcs:ignore WordPress.Security.NonceVerification
            ? sanitize_text_field( wp_unslash( $_REQUEST['hr_announcement_send_push'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
            : '';

        update_post_meta( $post_id, '_announcement_send_push', $push_enabled );

        if ( ! $this->handler ) {
            return;
        }

        $this->handler->on_announcement( $employees, $post_id );
    }

    /**
     * Dispatch a push notification when a leave request is submitted.
     *
     * @since 1.0.0
     *
     * @param int   $request_id Leave request ID.
     * @param array $request    Leave request data array.
     *
     * @return void
     */
    public function on_leave_request_new( $request_id, $request ) {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'leave' ) ) {
            return;
        }

        $this->handler->on_leave_request_new( $request_id, $request );
    }

    /**
     * Dispatch a push notification when a leave request is approved.
     *
     * @since 1.0.0
     *
     * @param int   $request_id Leave request ID.
     * @param array $request    Leave request data array.
     *
     * @return void
     */
    public function on_leave_request_approved( $request_id, $request ) {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'leave' ) ) {
            return;
        }

        $this->handler->on_leave_request_approved( $request_id, $request );
    }

    /**
     * Dispatch a push notification when a leave request is rejected.
     *
     * @since 1.0.0
     *
     * @param int   $request_id Leave request ID.
     * @param array $request    Leave request data array.
     *
     * @return void
     */
    public function on_leave_request_rejected( $request_id, $request ) {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'leave' ) ) {
            return;
        }

        $this->handler->on_leave_request_rejected( $request_id, $request );
    }

    /**
     * Daily cron callback: send holiday push reminders to all employees
     * the configured number of days before each upcoming holiday.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function on_holiday_reminder_check() {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'holiday' ) ) {
            return;
        }

        $days_before = $this->get_holiday_reminder_days();
        $this->handler->on_holiday_reminder( $days_before );
    }

    /**
     * Resolve the configured number of days before a holiday to send the reminder.
     *
     * Custom days field takes priority over the preset dropdown.
     *
     * @since 1.0.0
     *
     * @return int Always >= 1.
     */
    private function get_holiday_reminder_days() {
        $custom = absint( $this->get_option( 'erp_push_holiday_custom_days', '' ) );
        if ( $custom > 0 ) {
            return $custom;
        }

        $preset = absint( $this->get_option( 'erp_push_holiday_days_before', 1 ) );
        return max( 1, $preset );
    }

    /**
     * Dispatch a birthday push notification to the employee.
     *
     * @since 1.0.0
     *
     * @param int $user_id WordPress user ID of the birthday employee.
     *
     * @return void
     */
    public function on_birthday( $user_id ) {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'birthday' ) ) {
            return;
        }

        $this->handler->on_birthday( $user_id );
    }

    /**
     * Dispatch a push notification to HR managers when a new job application is submitted.
     *
     * @since 1.0.0
     *
     * @param array $data Application data: job_id, applicant_id, stage, exam_detail.
     *
     * @return void
     */
    public function on_job_apply( $data ) {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'job_apply' ) ) {
            return;
        }

        $this->handler->on_job_apply( $data );
    }

    /**
     * Notify all other employees about their colleague's birthday.
     *
     * @since 1.0.0
     *
     * @param int $user_id WordPress user ID of the birthday employee.
     *
     * @return void
     */
    public function on_birthday_notify_colleagues( $user_id ) {
        if ( ! $this->handler || ! $this->is_push_enabled_for( 'birthday_colleagues' ) ) {
            return;
        }

        $this->handler->on_birthday_notify_colleagues( $user_id );
    }

    /**
     * Check whether push notifications are enabled for a particular feature.
     *
     * @since 1.0.0
     *
     * @param string $feature Feature key: 'leave' or 'announcement'.
     *
     * @return bool
     */
    private function is_push_enabled_for( $feature ) {
        $option_map = [
            'leave'               => 'erp_push_enable_leave',
            'announcement'        => 'erp_push_enable_announcement',
            'holiday'             => 'erp_push_enable_holiday',
            'birthday'            => 'erp_push_enable_birthday',
            'birthday_colleagues' => 'erp_push_birthday_notify_all',
            'job_apply'           => 'erp_push_enable_job_apply',
        ];

        if ( ! isset( $option_map[ $feature ] ) ) {
            return false;
        }

        return 'yes' === $this->get_option( $option_map[ $feature ] );
    }

    /**
     * Retrieve a saved push notification option value.
     *
     * @since 1.0.0
     *
     * @param string $field_id Option field key.
     * @param mixed  $default  Default value.
     *
     * @return mixed
     */
    private function get_option( $field_id, $default = '' ) {
        return erp_get_option( $field_id, Settings::OPTION_KEY, $default );
    }
}
