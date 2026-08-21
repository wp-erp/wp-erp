<?php

namespace WeDevs\ERP\Weather;

use WeDevs\ERP\Integration;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Open-Meteo Weather integration settings page.
 *
 * Registers configuration fields under the ERP Integrations tab.
 *
 * @since 1.18.0
 */
class Settings extends Integration {

    /**
     * Option group key used to retrieve saved values.
     *
     * @var string
     */
    const OPTION_KEY = 'erp_integration_settings_erp-open-meteo';

    /**
     * Constructor.
     *
     * @since 1.18.0
     */
    public function __construct() {
        $this->id          = 'erp-open-meteo';
        $this->title       = __( 'Open-Meteo Weather', 'erp' );
        $this->description = __( 'Fetch weather forecast data from the Open-Meteo API and serve it via a REST endpoint.', 'erp' );

        $this->init_settings();
        parent::__construct();
    }

    /**
     * Return the tab title.
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Return the tab description.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Initialize form fields.
     *
     * @since 1.18.0
     *
     * @return void
     */
    public function init_settings() {
        $this->form_fields = [
            [
                'title' => __( 'Open-Meteo Weather API', 'erp' ),
                'type'  => 'title',
            ],
            [
                'title'   => __( 'Enable', 'erp' ),
                'type'    => 'checkbox',
                'id'      => 'erp_weather_enable',
                'desc'    => __( 'Enable the Open-Meteo weather integration.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'API Tier', 'erp' ),
                'type'    => 'select',
                'id'      => 'erp_weather_api_tier',
                'desc'    => __( 'Select Free for the public API or Paid for the commercial API with an API key.', 'erp' ),
                'default' => 'free',
                'options' => [
                    'free' => __( 'Free', 'erp' ),
                    'paid' => __( 'Paid (Commercial)', 'erp' ),
                ],
            ],
            [
                'title'   => __( 'API Key', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_weather_api_key',
                'desc'    => __( 'Enter your Open-Meteo commercial API key. Only required when API Tier is set to Paid.', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Default Latitude', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_weather_default_latitude',
                'desc'    => __( 'Default latitude coordinate (e.g. 52.52).', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Default Longitude', 'erp' ),
                'type'    => 'text',
                'id'      => 'erp_weather_default_longitude',
                'desc'    => __( 'Default longitude coordinate (e.g. 13.41).', 'erp' ),
                'default' => '',
            ],
            [
                'title'   => __( 'Default Temperature Unit', 'erp' ),
                'type'    => 'select',
                'id'      => 'erp_weather_default_temp_unit',
                'desc'    => __( 'Default temperature unit for API responses.', 'erp' ),
                'default' => 'celsius',
                'options' => [
                    'celsius'    => __( 'Celsius', 'erp' ),
                    'fahrenheit' => __( 'Fahrenheit', 'erp' ),
                ],
            ],
            [
                'title'   => __( 'Fetch Interval', 'erp' ),
                'type'    => 'select',
                'id'      => 'erp_weather_fetch_interval',
                'desc'    => __( 'How often to refresh the cached weather data.', 'erp' ),
                'default' => 'hourly',
                'options' => [
                    'thirty_min' => __( 'Every 30 Minutes', 'erp' ),
                    'hourly'     => __( 'Hourly', 'erp' ),
                    'daily'      => __( 'Daily', 'erp' ),
                ],
            ],
        ];
    }

    /**
     * Retrieve the persisted value for a specific field.
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
     * @return string
     */
    public function get_option_id() {
        return self::OPTION_KEY;
    }
}
