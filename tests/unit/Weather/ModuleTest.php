<?php

require_once __DIR__ . '/bootstrap.php';

use WeDevs\ERP\Weather\OpenMeteo;

class Weather_ModuleTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var OpenMeteo
     */
    private $module;

    protected function setUp() {
        $this->module = OpenMeteo::init();
    }

    public function test_validate_settings_allows_free_without_api_key() {
        $options = [
            'erp_weather_api_tier' => 'free',
            'erp_weather_api_key'  => '',
        ];

        $result = $this->module->validate_settings( $options );

        $this->assertInternalType( 'array', $result );
        $this->assertEquals( $options, $result );
    }

    public function test_validate_settings_allows_paid_with_api_key() {
        $options = [
            'erp_weather_api_tier' => 'paid',
            'erp_weather_api_key'  => 'my-api-key-123',
        ];

        $result = $this->module->validate_settings( $options );

        $this->assertInternalType( 'array', $result );
        $this->assertEquals( $options, $result );
    }

    public function test_validate_settings_rejects_paid_without_api_key() {
        $options = [
            'erp_weather_api_tier' => 'paid',
            'erp_weather_api_key'  => '',
        ];

        $result = $this->module->validate_settings( $options );

        $this->assertInstanceOf( 'WP_Error', $result );
        $this->assertEquals( 'erp_weather_api_key_required', $result->get_error_code() );
    }

    public function test_validate_settings_rejects_paid_with_whitespace_only_key() {
        $options = [
            'erp_weather_api_tier' => 'paid',
            'erp_weather_api_key'  => '   ',
        ];

        $result = $this->module->validate_settings( $options );

        $this->assertInstanceOf( 'WP_Error', $result );
    }

    public function test_validate_settings_defaults_tier_to_free() {
        $options = [
            'erp_weather_api_key' => '',
        ];

        $result = $this->module->validate_settings( $options );

        $this->assertInternalType( 'array', $result );
    }
}
