<?php

namespace WeDevs\ERP\Weather;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Weather integration module for WP ERP.
 *
 * Bootstraps the Open-Meteo integration: registers the settings page,
 * REST controller, and cron-based cache pre-warming.
 *
 * @since 1.18.0
 */
class OpenMeteo {

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance;

    /**
     * Settings instance.
     *
     * @var Settings|null
     */
    private $settings;

    /**
     * Initializes the Module (singleton).
     *
     * @since 1.18.0
     *
     * @return self
     */
    public static function init() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.18.0
     */
    private function __construct() {
        $this->init_filters();
        $this->init_cron();
        $this->init_admin_scripts();
    }

    /**
     * Register WordPress filter hooks.
     *
     * @since 1.18.0
     *
     * @return void
     */
    private function init_filters() {
        add_filter( 'erp_integration_classes', [ $this, 'register_integration' ] );
        add_filter( 'erp_rest_api_controllers', [ $this, 'register_rest_controller' ] );
        add_filter( 'erp_integration_settings_erp-open-meteo_filter', [ $this, 'validate_settings' ] );
    }

    /**
     * Set up cron hooks for cache pre-warming.
     *
     * @since 1.18.0
     *
     * @return void
     */
    private function init_cron() {
        add_action( 'erp_daily_scheduled_events', [ $this, 'scheduled_fetch' ] );
    }

    /**
     * Register the Open-Meteo integration settings page.
     *
     * @since 1.18.0
     *
     * @param array $integrations Existing integration settings.
     *
     * @return array
     */
    public function register_integration( $integrations ) {
        $this->settings = new Settings();

        $integrations['open_meteo'] = $this->settings;

        return $integrations;
    }

    /**
     * Register the weather REST API controller.
     *
     * @since 1.18.0
     *
     * @param array $controllers Existing REST controllers.
     *
     * @return array
     */
    public function register_rest_controller( $controllers ) {
        $controllers[] = '\WeDevs\ERP\Weather\WeatherController';

        return $controllers;
    }

    /**
     * Validate integration settings before saving.
     *
     * Returns WP_Error if paid tier is selected without an API key.
     *
     * @since 1.18.0
     *
     * @param array $options Settings values being saved.
     *
     * @return array|\WP_Error
     */
    public function validate_settings( $options ) {
        $enabled = isset( $options['erp_weather_enable'] ) ? $options['erp_weather_enable'] : '';
        $tier    = isset( $options['erp_weather_api_tier'] ) ? $options['erp_weather_api_tier'] : '';
        $key     = isset( $options['erp_weather_api_key'] ) ? trim( $options['erp_weather_api_key'] ) : '';

        if ( 'yes' === $enabled && ! in_array( $tier, [ 'free', 'paid' ], true ) ) {
            return new \WP_Error(
                'erp_weather_api_tier_required',
                __( 'Please select an API Tier (Free or Paid) when the Open-Meteo integration is enabled.', 'erp' )
            );
        }

        if ( 'paid' === $tier && '' === $key ) {
            return new \WP_Error(
                'erp_weather_api_key_required',
                __( 'API Key is required when API Tier is set to Paid.', 'erp' )
            );
        }

        return $options;
    }

    /**
     * Enqueue admin scripts for conditional field visibility.
     *
     * @since 1.18.0
     *
     * @return void
     */
    private function init_admin_scripts() {
        add_action( 'admin_footer', [ $this, 'render_settings_script' ] );
    }

    /**
     * Output inline JS to toggle API key field visibility based on tier selection.
     *
     * @since 1.18.0
     *
     * @return void
     */
    public function render_settings_script() {
        $screen = get_current_screen();

        if ( ! $screen || false === strpos( $screen->id, 'erp-settings' ) ) {
            return;
        }

        ?>
        <script type="text/javascript">
        (function() {
            var pollTimer = null;

            // Weather dropdowns whose Vue v-model needs to be set directly to
            // sidestep a track-by mismatch in the shared MultiSelect wrapper
            // (which would otherwise blank the field on any change).
            var weatherDropdownIds = [
                'erp-erp_weather_api_tier',
                'erp-erp_weather_default_temp_unit',
                'erp-erp_weather_fetch_interval'
            ];

            function toggleApiKeyField() {
                var tierEl = document.getElementById('erp-erp_weather_api_tier');
                if ( ! tierEl ) {
                    return;
                }

                var selectedSpan = tierEl.querySelector('.multiselect__single');
                if ( ! selectedSpan ) {
                    return;
                }

                var text   = selectedSpan.textContent.trim().toLowerCase();
                var isPaid = text.indexOf('paid') !== -1;

                var apiKeyEl = document.getElementById('erp-erp_weather_api_key');
                if ( ! apiKeyEl ) {
                    return;
                }

                var formGroup = apiKeyEl.closest('.wperp-form-group');
                if ( formGroup ) {
                    formGroup.style.display = isPaid ? '' : 'none';
                }
            }

            function findVueInstance( el, name ) {
                var cur = el && el.__vue__;
                for ( var i = 0; cur && i < 8; i++ ) {
                    if ( cur.$options && cur.$options.name === name ) {
                        return cur;
                    }
                    cur = cur.$parent;
                }
                cur = el && el.__vue__;
                for ( var j = 0; cur && j < 8; j++ ) {
                    if ( cur.$options && cur.$options.name === name ) {
                        return cur;
                    }
                    cur = ( cur.$children && cur.$children[ 0 ] ) || null;
                }
                return null;
            }

            function onCaptureClick( e ) {
                var optionEl = e.target && e.target.closest ? e.target.closest( '.multiselect__option' ) : null;
                if ( ! optionEl ) {
                    return;
                }

                var multiselectEl = optionEl.closest( '.multiselect' );
                if ( ! multiselectEl || weatherDropdownIds.indexOf( multiselectEl.id ) === -1 ) {
                    return;
                }

                var inner = findVueInstance( multiselectEl, 'Multiselect' );
                if ( ! inner || ! Array.isArray( inner.filteredOptions ) ) {
                    return;
                }

                var option = inner.filteredOptions[ inner.pointer ];
                if ( ! option || option.$isLabel || option.$isDisabled || option.isTag ) {
                    return;
                }

                e.stopPropagation();
                e.preventDefault();

                var wrapper = findVueInstance( multiselectEl, 'MultiSelect' ) || inner.$parent;
                if ( ! wrapper || typeof wrapper.$emit !== 'function' ) {
                    return;
                }

                var current     = wrapper.value;
                var sameClicked = current && typeof current === 'object' && current.id === option.id;

                if ( sameClicked ) {
                    wrapper.$emit( 'input', null );
                } else {
                    wrapper.$emit( 'input', { id: option.id, name: option.name } );
                }

                if ( typeof inner.deactivate === 'function' ) {
                    inner.deactivate();
                }
            }

            document.addEventListener( 'click', onCaptureClick, true );

            var observer = new MutationObserver(function() {
                var tierEl = document.getElementById('erp-erp_weather_api_tier');

                if ( tierEl && ! pollTimer ) {
                    toggleApiKeyField();
                    pollTimer = setInterval( toggleApiKeyField, 300 );
                } else if ( ! tierEl && pollTimer ) {
                    clearInterval( pollTimer );
                    pollTimer = null;
                }
            });

            observer.observe( document.body, { childList: true, subtree: true } );
        })();
        </script>
        <?php
    }

    /**
     * Pre-warm the weather cache for the default location.
     *
     * Hooked into the daily cron event. Fetches weather data for the
     * configured default coordinates and stores it in a transient.
     *
     * @since 1.18.0
     *
     * @return void
     */
    public function scheduled_fetch() {
        $settings = new Settings();

        $enabled = $settings->get_option( 'erp_weather_enable', '' );

        if ( 'yes' !== $enabled ) {
            return;
        }

        $latitude  = $settings->get_option( 'erp_weather_default_latitude', '' );
        $longitude = $settings->get_option( 'erp_weather_default_longitude', '' );
        $temp_unit = $settings->get_option( 'erp_weather_default_temp_unit', 'celsius' );

        if ( '' === $latitude || '' === $longitude ) {
            return;
        }

        $latitude  = (float) $latitude;
        $longitude = (float) $longitude;

        $api_tier = $settings->get_option( 'erp_weather_api_tier', 'free' );
        $api_key  = $settings->get_option( 'erp_weather_api_key', '' );

        $hourly = Client::get_default_hourly();
        $client = new Client( $api_tier, $api_key );
        $data   = $client->fetch( $latitude, $longitude, $temp_unit, $hourly );

        if ( is_wp_error( $data ) ) {
            return;
        }

        sort( $hourly );
        $hourly_hash = md5( implode( ',', $hourly ) );
        $cache_key   = sprintf(
            'erp_weather_%s_%s_%s_%s',
            round( $latitude, 2 ),
            round( $longitude, 2 ),
            $temp_unit,
            substr( $hourly_hash, 0, 8 )
        );

        // Determine TTL from settings.
        $interval = $settings->get_option( 'erp_weather_fetch_interval', 'hourly' );
        $ttl_map  = [
            'thirty_min' => 30 * MINUTE_IN_SECONDS,
            'hourly'     => HOUR_IN_SECONDS,
            'daily'      => DAY_IN_SECONDS,
        ];
        $ttl = isset( $ttl_map[ $interval ] ) ? $ttl_map[ $interval ] : HOUR_IN_SECONDS;

        set_transient( $cache_key, $data, $ttl );
    }
}
