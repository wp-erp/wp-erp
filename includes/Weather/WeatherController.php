<?php

namespace WeDevs\ERP\Weather;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\API\REST_Controller;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST API controller for weather data.
 *
 * Serves cached Open-Meteo weather data via GET /erp/v1/weather.
 *
 * @since 1.18.0
 */
class WeatherController extends REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'weather';

    /**
     * Register the routes for the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_weather' ],
                'permission_callback' => [ $this, 'get_weather_permissions_check' ],
                'args'                => [
                    'latitude' => [
                        'description'       => __( 'Latitude coordinate (WGS84).', 'erp' ),
                        'type'              => 'number',
                        'required'          => false,
                        'sanitize_callback' => function ( $value ) {
                            return (float) $value;
                        },
                        'validate_callback' => function ( $value ) {
                            $val = (float) $value;
                            return $val >= -90 && $val <= 90;
                        },
                    ],
                    'longitude' => [
                        'description'       => __( 'Longitude coordinate (WGS84).', 'erp' ),
                        'type'              => 'number',
                        'required'          => false,
                        'sanitize_callback' => function ( $value ) {
                            return (float) $value;
                        },
                        'validate_callback' => function ( $value ) {
                            $val = (float) $value;
                            return $val >= -180 && $val <= 180;
                        },
                    ],
                    'temperature_unit' => [
                        'description'       => __( 'Temperature unit: celsius or fahrenheit.', 'erp' ),
                        'type'              => 'string',
                        'required'          => false,
                        'enum'              => [ 'celsius', 'fahrenheit' ],
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => 'rest_validate_request_arg',
                    ],
                    'hourly' => [
                        'description'       => __( 'Comma-separated hourly weather variables to fetch (e.g. temperature_2m,weather_code,apparent_temperature).', 'erp' ),
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Check permissions for the weather endpoint.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return bool|WP_Error
     */
    public function get_weather_permissions_check( $request ) {
        return current_user_can( 'erp_view_list' );
    }

    /**
     * Get weather data.
     *
     * Returns cached data when available, otherwise fetches fresh data from the API.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_weather( $request ) {
        $settings = new Settings();

        // Check if integration is enabled.
        $enabled = $settings->get_option( 'erp_weather_enable', '' );

        if ( 'yes' !== $enabled ) {
            return new WP_Error(
                'erp_weather_disabled',
                __( 'The Open-Meteo weather integration is not enabled.', 'erp' ),
                [ 'status' => 403 ]
            );
        }

        // Resolve parameters: request > saved defaults.
        $latitude = $request->get_param( 'latitude' );
        if ( null === $latitude || '' === $latitude ) {
            $latitude = $settings->get_option( 'erp_weather_default_latitude', '' );
        }

        $longitude = $request->get_param( 'longitude' );
        if ( null === $longitude || '' === $longitude ) {
            $longitude = $settings->get_option( 'erp_weather_default_longitude', '' );
        }

        $temp_unit = $request->get_param( 'temperature_unit' );
        if ( empty( $temp_unit ) ) {
            $temp_unit = $settings->get_option( 'erp_weather_default_temp_unit', 'celsius' );
        }

        // Parse hourly variables.
        $hourly_param = $request->get_param( 'hourly' );
        $hourly       = [];

        if ( ! empty( $hourly_param ) ) {
            $hourly = array_map( 'trim', explode( ',', $hourly_param ) );
            $hourly = array_values( array_intersect( $hourly, Client::get_allowed_hourly() ) );
        }

        if ( empty( $hourly ) ) {
            $hourly = Client::get_default_hourly();
        }

        // Validate that we have coordinates.
        if ( '' === $latitude || '' === $longitude ) {
            return new WP_Error(
                'erp_weather_missing_coords',
                __( 'Latitude and longitude are required. Provide them as query parameters or configure defaults in settings.', 'erp' ),
                [ 'status' => 400 ]
            );
        }

        $latitude  = (float) $latitude;
        $longitude = (float) $longitude;

        if ( $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
            return new WP_Error(
                'erp_weather_invalid_coords',
                __( 'Invalid coordinates. Latitude must be between -90 and 90, longitude between -180 and 180.', 'erp' ),
                [ 'status' => 400 ]
            );
        }

        // Build cache key with coordinates rounded to 2 decimal places + hourly vars.
        sort( $hourly );
        $hourly_hash = md5( implode( ',', $hourly ) );
        $cache_key   = sprintf(
            'erp_weather_%s_%s_%s_%s',
            round( $latitude, 2 ),
            round( $longitude, 2 ),
            $temp_unit,
            substr( $hourly_hash, 0, 8 )
        );

        // Check for cached data.
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            $cached['source'] = 'cache';

            return new WP_REST_Response( $cached, 200 );
        }

        // Fetch fresh data.
        $api_tier = $settings->get_option( 'erp_weather_api_tier', 'free' );
        $api_key  = $settings->get_option( 'erp_weather_api_key', '' );

        $client = new Client( $api_tier, $api_key );
        $data   = $client->fetch( $latitude, $longitude, $temp_unit, $hourly );

        if ( is_wp_error( $data ) ) {
            return new WP_Error(
                'erp_weather_upstream_error',
                $data->get_error_message(),
                [ 'status' => 502 ]
            );
        }

        // Determine cache TTL based on fetch interval setting.
        $ttl = $this->get_cache_ttl( $settings );

        set_transient( $cache_key, $data, $ttl );

        $data['source'] = 'api';

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Get cache TTL in seconds based on the configured fetch interval.
     *
     * @param Settings $settings Settings instance.
     *
     * @return int TTL in seconds.
     */
    private function get_cache_ttl( $settings ) {
        $interval = $settings->get_option( 'erp_weather_fetch_interval', 'hourly' );

        $map = [
            'thirty_min' => 30 * MINUTE_IN_SECONDS,
            'hourly'     => HOUR_IN_SECONDS,
            'daily'      => DAY_IN_SECONDS,
        ];

        return isset( $map[ $interval ] ) ? $map[ $interval ] : HOUR_IN_SECONDS;
    }

    /**
     * Get the item schema for the weather endpoint.
     *
     * @return array
     */
    public function get_item_schema() {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'weather',
            'type'       => 'object',
            'properties' => [
                'latitude' => [
                    'description' => __( 'Latitude of the weather location.', 'erp' ),
                    'type'        => 'number',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'longitude' => [
                    'description' => __( 'Longitude of the weather location.', 'erp' ),
                    'type'        => 'number',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'elevation' => [
                    'description' => __( 'Elevation in meters.', 'erp' ),
                    'type'        => 'number',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'timezone' => [
                    'description' => __( 'Timezone of the location.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'temperature_unit' => [
                    'description' => __( 'Temperature unit used in the response.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'fetched_at' => [
                    'description' => __( 'ISO 8601 timestamp when data was fetched.', 'erp' ),
                    'type'        => 'string',
                    'format'      => 'date-time',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'source' => [
                    'description' => __( 'Data source: cache or api.', 'erp' ),
                    'type'        => 'string',
                    'enum'        => [ 'cache', 'api' ],
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'requested_hourly' => [
                    'description' => __( 'List of hourly variables that were requested.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'hourly' => [
                    'description' => __( 'Hourly weather data. Only contains the requested variables.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'hourly_units' => [
                    'description' => __( 'Units for hourly weather data fields.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
            ],
        ];
    }
}
