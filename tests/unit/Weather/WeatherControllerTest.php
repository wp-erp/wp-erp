<?php

require_once __DIR__ . '/bootstrap.php';

use WeDevs\ERP\Weather\WeatherController;

class Weather_WeatherControllerTest extends \PHPUnit_Framework_TestCase {

    public function test_namespace_is_erp_v1() {
        $controller = new WeatherController();

        $reflection = new \ReflectionClass( $controller );
        $prop       = $reflection->getProperty( 'namespace' );
        $prop->setAccessible( true );

        $this->assertEquals( 'erp/v1', $prop->getValue( $controller ) );
    }

    public function test_rest_base_is_weather() {
        $controller = new WeatherController();

        $reflection = new \ReflectionClass( $controller );
        $prop       = $reflection->getProperty( 'rest_base' );
        $prop->setAccessible( true );

        $this->assertEquals( 'weather', $prop->getValue( $controller ) );
    }

    public function test_schema_has_expected_properties() {
        $controller = new WeatherController();
        $schema     = $controller->get_item_schema();

        $this->assertArrayHasKey( 'properties', $schema );

        $expected = [
            'latitude',
            'longitude',
            'elevation',
            'timezone',
            'temperature_unit',
            'fetched_at',
            'source',
            'requested_hourly',
            'hourly',
            'hourly_units',
        ];

        foreach ( $expected as $prop ) {
            $this->assertArrayHasKey( $prop, $schema['properties'], "Schema should have '{$prop}' property." );
        }
    }

    public function test_schema_title() {
        $controller = new WeatherController();
        $schema     = $controller->get_item_schema();

        $this->assertEquals( 'weather', $schema['title'] );
    }

    public function test_schema_source_enum() {
        $controller = new WeatherController();
        $schema     = $controller->get_item_schema();

        $this->assertEquals( [ 'cache', 'api' ], $schema['properties']['source']['enum'] );
    }

    public function test_cache_ttl_thirty_min() {
        $controller = new WeatherController();
        $reflection = new \ReflectionClass( $controller );
        $method     = $reflection->getMethod( 'get_cache_ttl' );
        $method->setAccessible( true );

        $settings = $this->getMockBuilder( \WeDevs\ERP\Weather\Settings::class )
            ->disableOriginalConstructor()
            ->setMethods( [ 'get_option' ] )
            ->getMock();
        $settings->method( 'get_option' )->willReturn( 'thirty_min' );

        $this->assertEquals( 1800, $method->invoke( $controller, $settings ) );
    }

    public function test_cache_ttl_hourly() {
        $controller = new WeatherController();
        $reflection = new \ReflectionClass( $controller );
        $method     = $reflection->getMethod( 'get_cache_ttl' );
        $method->setAccessible( true );

        $settings = $this->getMockBuilder( \WeDevs\ERP\Weather\Settings::class )
            ->disableOriginalConstructor()
            ->setMethods( [ 'get_option' ] )
            ->getMock();
        $settings->method( 'get_option' )->willReturn( 'hourly' );

        $this->assertEquals( 3600, $method->invoke( $controller, $settings ) );
    }

    public function test_cache_ttl_daily() {
        $controller = new WeatherController();
        $reflection = new \ReflectionClass( $controller );
        $method     = $reflection->getMethod( 'get_cache_ttl' );
        $method->setAccessible( true );

        $settings = $this->getMockBuilder( \WeDevs\ERP\Weather\Settings::class )
            ->disableOriginalConstructor()
            ->setMethods( [ 'get_option' ] )
            ->getMock();
        $settings->method( 'get_option' )->willReturn( 'daily' );

        $this->assertEquals( 86400, $method->invoke( $controller, $settings ) );
    }
}
