<?php
namespace WeDevs\ERP\HRM\PushNotification;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract base class for push notification providers.
 *
 * Provides shared utility methods for all notification implementations.
 *
 * @since 1.0.0
 */
abstract class AbstractNotification implements NotificationInterface {

    /**
     * Make an HTTP POST request with a JSON body.
     *
     * @since 1.0.0
     *
     * @param string $url     The endpoint URL.
     * @param array  $headers Request headers.
     * @param array  $payload Request payload (will be JSON-encoded).
     *
     * @return array|WP_Error The response or a WP_Error on failure.
     */
    protected function post_json( $url, $headers, $payload ) {
        $args = [
            'headers' => array_merge(
                [ 'Content-Type' => 'application/json; charset=utf-8' ],
                $headers
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 15,
        ];

        return wp_remote_post( $url, $args );
    }
}
