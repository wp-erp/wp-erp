<?php

namespace WeDevs\ERP\Weather;

use WP_Error;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * HTTP client for the Open-Meteo Weather API.
 *
 * Supports both the free and commercial (paid) API tiers.
 *
 * @since 1.18.0
 */
class Client {

    /**
     * API tier: 'free' or 'paid'.
     *
     * @var string
     */
    private $api_tier;

    /**
     * API key for the commercial tier.
     *
     * @var string
     */
    private $api_key;

    /**
     * Constructor.
     *
     * @param string $api_tier 'free' or 'paid'.
     * @param string $api_key  API key (only used when tier is 'paid').
     */
    public function __construct( $api_tier = 'free', $api_key = '' ) {
        $this->api_tier = $api_tier;
        $this->api_key  = $api_key;
    }

    /**
     * Get the base URL for the configured API tier.
     *
     * @return string
     */
    public function get_base_url() {
        if ( 'paid' === $this->api_tier ) {
            return 'https://customer-api.open-meteo.com/v1/forecast';
        }

        return 'https://api.open-meteo.com/v1/forecast';
    }

    /**
     * Allowed hourly variables that can be requested from the API.
     *
     * @var array
     */
    private static $allowed_hourly = [
        'temperature_2m',
        'relative_humidity_2m',
        'wind_speed_10m',
        'apparent_temperature',
        'precipitation',
        'weather_code',
        'cloud_cover',
        'pressure_msl',
        'surface_pressure',
        'wind_direction_10m',
        'wind_gusts_10m',
        'uv_index',
        'visibility',
        'dew_point_2m',
        'rain',
        'showers',
        'snowfall',
    ];

    /**
     * Default hourly variables when none are specified.
     *
     * @var array
     */
    private static $default_hourly = [
        'temperature_2m',
        'apparent_temperature',
        'weather_code',
    ];

    /**
     * Get the list of allowed hourly variable names.
     *
     * @return array
     */
    public static function get_allowed_hourly() {
        return self::$allowed_hourly;
    }

    /**
     * Get the default hourly variable names.
     *
     * @return array
     */
    public static function get_default_hourly() {
        return self::$default_hourly;
    }

    /**
     * Fetch weather data from the Open-Meteo API.
     *
     * @param float  $latitude       Latitude coordinate.
     * @param float  $longitude      Longitude coordinate.
     * @param string $temp_unit      Temperature unit: 'celsius' or 'fahrenheit'.
     * @param array  $hourly         Hourly variables to request. Uses defaults if empty.
     *
     * @return array|WP_Error Decoded API response array on success, WP_Error on failure.
     */
    public function fetch( $latitude, $longitude, $temp_unit = 'celsius', $hourly = [] ) {
        if ( empty( $hourly ) ) {
            $hourly = self::$default_hourly;
        }

        // Filter to only allowed variables.
        $hourly = array_values( array_intersect( $hourly, self::$allowed_hourly ) );

        if ( empty( $hourly ) ) {
            $hourly = self::$default_hourly;
        }

        $params = [
            'latitude'         => round( (float) $latitude, 4 ),
            'longitude'        => round( (float) $longitude, 4 ),
            'temperature_unit' => in_array( $temp_unit, [ 'celsius', 'fahrenheit' ], true ) ? $temp_unit : 'celsius',
            'hourly'           => implode( ',', $hourly ),
            'forecast_days'    => 7,
            'timezone'         => 'auto',
        ];

        if ( 'paid' === $this->api_tier && ! empty( $this->api_key ) ) {
            $params['apikey'] = $this->api_key;
        }

        $url = add_query_arg( $params, $this->get_base_url() );

        $response = wp_remote_get( $url, [
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'erp_weather_request_failed',
                __( 'Failed to connect to the Open-Meteo API.', 'erp' ),
                [ 'original_error' => $response->get_error_message() ]
            );
        }

        $status_code = wp_remote_retrieve_response_code( $response );

        if ( $status_code < 200 || $status_code >= 300 ) {
            return new WP_Error(
                'erp_weather_api_error',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __( 'Open-Meteo API returned HTTP %d.', 'erp' ),
                    $status_code
                ),
                [ 'status' => $status_code ]
            );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error(
                'erp_weather_invalid_json',
                __( 'Open-Meteo API returned invalid JSON.', 'erp' )
            );
        }

        return self::format_response( $data );
    }

    /**
     * Normalize the raw API response into a consistent structure.
     *
     * @param array $raw Raw decoded JSON from the API.
     *
     * @return array Normalized weather data.
     */
    public static function format_response( $raw ) {
        return [
            'latitude'         => isset( $raw['latitude'] ) ? (float) $raw['latitude'] : null,
            'longitude'        => isset( $raw['longitude'] ) ? (float) $raw['longitude'] : null,
            'elevation'        => isset( $raw['elevation'] ) ? (float) $raw['elevation'] : null,
            'timezone'         => isset( $raw['timezone'] ) ? $raw['timezone'] : '',
            'temperature_unit' => isset( $raw['hourly_units']['temperature_2m'] ) ? $raw['hourly_units']['temperature_2m'] : '',
            'fetched_at'       => gmdate( 'c' ),
            'hourly'           => isset( $raw['hourly'] ) ? $raw['hourly'] : [],
            'hourly_units'     => isset( $raw['hourly_units'] ) ? $raw['hourly_units'] : [],
        ];
    }
}
