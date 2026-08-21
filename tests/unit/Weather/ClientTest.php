<?php

require_once __DIR__ . '/bootstrap.php';

use WeDevs\ERP\Weather\Client;

class Weather_ClientTest extends \PHPUnit_Framework_TestCase {

    public function test_get_base_url_free() {
        $client = new Client( 'free', '' );
        $this->assertEquals( 'https://api.open-meteo.com/v1/forecast', $client->get_base_url() );
    }

    public function test_get_base_url_paid() {
        $client = new Client( 'paid', 'test-key-123' );
        $this->assertEquals( 'https://customer-api.open-meteo.com/v1/forecast', $client->get_base_url() );
    }

    public function test_get_base_url_defaults_to_free() {
        $client = new Client( 'unknown', '' );
        $this->assertEquals( 'https://api.open-meteo.com/v1/forecast', $client->get_base_url() );
    }

    public function test_format_response_normalizes_data() {
        $raw = [
            'latitude'     => 52.52,
            'longitude'    => 13.419998,
            'elevation'    => 38.0,
            'timezone'     => 'Europe/Berlin',
            'hourly_units' => [
                'temperature_2m'       => 'C',
                'relative_humidity_2m' => '%',
                'wind_speed_10m'       => 'km/h',
            ],
            'hourly' => [
                'time'                 => [ '2026-04-06T00:00', '2026-04-06T01:00' ],
                'temperature_2m'       => [ 8.2, 7.9 ],
                'relative_humidity_2m' => [ 72, 74 ],
                'wind_speed_10m'       => [ 12.5, 11.8 ],
            ],
        ];

        $result = Client::format_response( $raw );

        $this->assertInternalType( 'array', $result );
        $this->assertEquals( 52.52, $result['latitude'] );
        $this->assertEquals( 13.419998, $result['longitude'] );
        $this->assertEquals( 38.0, $result['elevation'] );
        $this->assertEquals( 'Europe/Berlin', $result['timezone'] );
        $this->assertNotEmpty( $result['fetched_at'] );
        $this->assertArrayHasKey( 'hourly', $result );
        $this->assertArrayHasKey( 'hourly_units', $result );
        $this->assertCount( 2, $result['hourly']['time'] );
        $this->assertEquals( 8.2, $result['hourly']['temperature_2m'][0] );
    }

    public function test_format_response_handles_missing_fields() {
        $result = Client::format_response( [] );

        $this->assertNull( $result['latitude'] );
        $this->assertNull( $result['longitude'] );
        $this->assertNull( $result['elevation'] );
        $this->assertEquals( '', $result['timezone'] );
        $this->assertEquals( '', $result['temperature_unit'] );
        $this->assertNotEmpty( $result['fetched_at'] );
        $this->assertEmpty( $result['hourly'] );
        $this->assertEmpty( $result['hourly_units'] );
    }

    public function test_format_response_includes_fetched_at_timestamp() {
        $result = Client::format_response( [ 'latitude' => 0, 'longitude' => 0 ] );

        $parsed = \DateTime::createFromFormat( \DateTime::ATOM, $result['fetched_at'] );
        $this->assertNotFalse( $parsed, 'fetched_at should be a valid ISO 8601 timestamp' );
    }

    public function test_format_response_extracts_temperature_unit() {
        $raw = [
            'hourly_units' => [ 'temperature_2m' => 'F' ],
        ];

        $result = Client::format_response( $raw );
        $this->assertEquals( 'F', $result['temperature_unit'] );
    }

    public function test_get_allowed_hourly_contains_weather_code() {
        $allowed = Client::get_allowed_hourly();

        $this->assertContains( 'weather_code', $allowed );
        $this->assertContains( 'temperature_2m', $allowed );
        $this->assertContains( 'apparent_temperature', $allowed );
    }

    public function test_get_default_hourly() {
        $defaults = Client::get_default_hourly();

        $this->assertContains( 'temperature_2m', $defaults );
        $this->assertContains( 'apparent_temperature', $defaults );
        $this->assertContains( 'weather_code', $defaults );
    }

    public function test_format_response_includes_weather_code() {
        $raw = [
            'latitude'  => 23.84,
            'longitude' => 90.38,
            'hourly'    => [
                'time'         => [ '2026-04-06T00:00' ],
                'weather_code' => [ 3 ],
            ],
        ];

        $result = Client::format_response( $raw );

        $this->assertArrayHasKey( 'weather_code', $result['hourly'] );
        $this->assertEquals( 3, $result['hourly']['weather_code'][0] );
    }
}
