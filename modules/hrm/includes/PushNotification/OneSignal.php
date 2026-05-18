<?php
namespace WeDevs\ERP\HRM\PushNotification;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OneSignal push notification provider.
 *
 * Implements the NotificationInterface using the OneSignal REST API v1.
 * Uses external_id aliases (not the deprecated player-based system) to
 * address individual subscribers, and named segments for bulk delivery.
 *
 * @see https://documentation.onesignal.com/reference/create-notification
 *
 * @since 1.0.0
 */
class OneSignal extends AbstractNotification {

    /**
     * OneSignal REST API base URL.
     *
     * @var string
     */
    const API_URL = 'https://api.onesignal.com/notifications?c=push';

    /**
     * OneSignal App ID.
     *
     * @var string
     */
    private $app_id;

    /**
     * OneSignal REST API key.
     *
     * @var string
     */
    private $rest_api_key;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param string $app_id       OneSignal Application ID.
     * @param string $rest_api_key OneSignal REST API key.
     */
    public function __construct( $app_id, $rest_api_key ) {
        $this->app_id       = $app_id;
        $this->rest_api_key = $rest_api_key;
    }

    /**
     * Send a push notification to specific users by their external IDs.
     *
     * @since 1.0.0
     *
     * @param array  $user_ids Array of external user IDs.
     * @param string $title    Notification title.
     * @param string $message  Notification message body.
     * @param array  $data     Optional additional key/value data payload.
     *
     * @return array|WP_Error
     */
    public function send( $user_ids, $title, $message, $data = [] ) {
        if ( empty( $user_ids ) ) {
            return new \WP_Error( 'no_recipients', __( 'No recipients specified.', 'erp' ) );
        }

        // Cast each ID to string as required by the OneSignal aliases API.
        $external_ids = array_values( array_map( 'strval', $user_ids ) );

        $payload = [
            'app_id'          => $this->app_id,
            'target_channel'  => 'push',
            'headings'        => [ 'en' => $title ],
            'contents'        => [ 'en' => $message ],
            'include_aliases' => [ 'external_id' => $external_ids ],
        ];

        if ( ! empty( $data ) ) {
            $payload['data'] = $data;
        }

        return $this->dispatch( $payload );
    }

    /**
     * Send a push notification to all subscribers in a named segment.
     *
     * @since 1.0.0
     *
     * @param string $segment Segment name (e.g. 'All', 'Subscribed Users').
     * @param string $title   Notification title.
     * @param string $message Notification message body.
     * @param array  $data    Optional additional key/value data payload.
     *
     * @return array|WP_Error
     */
    public function send_to_segment( $segment, $title, $message, $data = [] ) {
        if ( empty( $segment ) ) {
            return new \WP_Error( 'no_segment', __( 'No segment specified.', 'erp' ) );
        }

        $payload = [
            'app_id'            => $this->app_id,
            'target_channel'    => 'push',
            'headings'          => [ 'en' => $title ],
            'contents'          => [ 'en' => $message ],
            'included_segments' => [ $segment ],
        ];

        if ( ! empty( $data ) ) {
            $payload['data'] = $data;
        }

        return $this->dispatch( $payload );
    }

    /**
     * Execute the HTTP POST request to the OneSignal notifications endpoint.
     *
     * @since 1.0.0
     *
     * @param array $payload The fully-assembled notification payload.
     *
     * @return array|WP_Error
     */
    private function dispatch( $payload ) {
        $headers = [
            'Authorization' => $this->rest_api_key,
        ];

        $response = $this->post_json( self::API_URL, $headers, $payload );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code >= 400 ) {
            $error_message = isset( $body['errors'][0] ) ? $body['errors'][0] : __( 'Unknown OneSignal API error.', 'erp' );

            return new \WP_Error( 'onesignal_api_error', $error_message, [ 'status' => $status_code ] );
        }

        return $body;
    }
}
