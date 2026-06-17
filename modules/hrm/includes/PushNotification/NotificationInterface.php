<?php
namespace WeDevs\ERP\HRM\PushNotification;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Interface for push notification providers.
 *
 * Implementing this interface allows new notification providers to be
 * added without modifying existing code (Open/Closed Principle).
 *
 * @since 1.0.0
 */
interface NotificationInterface {

    /**
     * Send a push notification to specific users by their external IDs.
     *
     * @since 1.0.0
     *
     * @param array  $user_ids Array of external user IDs to target.
     * @param string $title    Notification title.
     * @param string $message  Notification message body.
     * @param array  $data     Optional additional data payload.
     *
     * @return array|WP_Error  Response array on success, WP_Error on failure.
     */
    public function send( $user_ids, $title, $message, $data = [] );

    /**
     * Send a push notification to all users in a named segment.
     *
     * @since 1.0.0
     *
     * @param string $segment Segment name (e.g. 'All', 'Subscribed Users').
     * @param string $title   Notification title.
     * @param string $message Notification message body.
     * @param array  $data    Optional additional data payload.
     *
     * @return array|WP_Error Response array on success, WP_Error on failure.
     */
    public function send_to_segment( $segment, $title, $message, $data = [] );
}
