<?php

require_once __DIR__ . '/bootstrap.php';

use WeDevs\ERP\Weather\Settings;

class Weather_SettingsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Settings
     */
    private $settings;

    protected function setUp() {
        $this->settings = new Settings();
    }

    public function test_integration_id() {
        $this->assertEquals( 'erp-open-meteo', $this->settings->id );
    }

    public function test_option_key_matches_id() {
        $this->assertEquals(
            'erp_integration_settings_erp-open-meteo',
            Settings::OPTION_KEY
        );
    }

    public function test_form_fields_are_defined() {
        $fields    = $this->settings->form_fields;
        $field_ids = array_filter( array_column( $fields, 'id' ) );

        $expected_ids = [
            'erp_weather_enable',
            'erp_weather_api_tier',
            'erp_weather_api_key',
            'erp_weather_default_latitude',
            'erp_weather_default_longitude',
            'erp_weather_default_temp_unit',
            'erp_weather_fetch_interval',
        ];

        foreach ( $expected_ids as $id ) {
            $this->assertContains( $id, $field_ids, "Form field '{$id}' should be defined." );
        }
    }

    public function test_api_tier_has_free_and_paid_options() {
        $tier_field = null;

        foreach ( $this->settings->form_fields as $field ) {
            if ( isset( $field['id'] ) && 'erp_weather_api_tier' === $field['id'] ) {
                $tier_field = $field;
                break;
            }
        }

        $this->assertNotNull( $tier_field, 'API tier field should exist.' );
        $this->assertArrayHasKey( 'free', $tier_field['options'] );
        $this->assertArrayHasKey( 'paid', $tier_field['options'] );
    }

    public function test_temp_unit_has_celsius_and_fahrenheit() {
        $unit_field = null;

        foreach ( $this->settings->form_fields as $field ) {
            if ( isset( $field['id'] ) && 'erp_weather_default_temp_unit' === $field['id'] ) {
                $unit_field = $field;
                break;
            }
        }

        $this->assertNotNull( $unit_field, 'Temperature unit field should exist.' );
        $this->assertArrayHasKey( 'celsius', $unit_field['options'] );
        $this->assertArrayHasKey( 'fahrenheit', $unit_field['options'] );
    }

    public function test_fetch_interval_options() {
        $interval_field = null;

        foreach ( $this->settings->form_fields as $field ) {
            if ( isset( $field['id'] ) && 'erp_weather_fetch_interval' === $field['id'] ) {
                $interval_field = $field;
                break;
            }
        }

        $this->assertNotNull( $interval_field, 'Fetch interval field should exist.' );
        $this->assertArrayHasKey( 'thirty_min', $interval_field['options'] );
        $this->assertArrayHasKey( 'hourly', $interval_field['options'] );
        $this->assertArrayHasKey( 'daily', $interval_field['options'] );
    }

    public function test_get_option_id_returns_option_key() {
        $this->assertEquals( Settings::OPTION_KEY, $this->settings->get_option_id() );
    }
}
