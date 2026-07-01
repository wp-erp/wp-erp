<?php

namespace WeDevs\ERP\CRM\ContactForms;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Pro contact form integrations awareness
 *
 * The actual Fluent Forms, Formidable, Forminator, WPForms, SureForms and
 * MetForm integrations live in the erp-pro plugin. In the free plugin we only
 * advertise them as locked "upgrade to pro" tabs so users know the feature
 * exists. When erp-pro is active, its own integration classes register the
 * same slugs first (default priority) and this class steps aside, so the real
 * mapping UI is shown instead of the upsell.
 */
class ContactFormsPro {
    use Hooker;

    /**
     * Upgrade URL shown on the upsell tabs
     */
    const UPGRADE_URL = 'https://wperp.com/pricing/';

    /**
     * Initializes the class
     *
     * @return object class instance
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct() {
        // Run late so the erp-pro integration classes (default priority) win
        // for any slug they already registered.
        $this->filter( 'erp_contact_forms_plugin_list', 'add_pro_plugins', 99 );
    }

    /**
     * The pro form integrations advertised in the free plugin
     *
     * Each entry maps the integration slug to its display title and a callback
     * that reports whether the underlying form plugin is installed/active (used
     * to show an "install <plugin>" hint).
     *
     * @return array
     */
    public function get_pro_plugins() {
        return [
            'fluentform' => [
                'title'         => __( 'Fluent Forms', 'erp' ),
                'plugin_check'  => function () {
                    return function_exists( 'wpFluentForm' );
                },
            ],
            'formidable' => [
                'title'         => __( 'Formidable Forms', 'erp' ),
                'plugin_check'  => function () {
                    return class_exists( 'FrmForm' );
                },
            ],
            'forminator' => [
                'title'         => __( 'Forminator', 'erp' ),
                'plugin_check'  => function () {
                    return defined( 'FORMINATOR_VERSION' );
                },
            ],
            'wpforms' => [
                'title'         => __( 'WPForms', 'erp' ),
                'plugin_check'  => function () {
                    return function_exists( 'wpforms' );
                },
            ],
            'sureforms' => [
                'title'         => __( 'SureForms', 'erp' ),
                'plugin_check'  => function () {
                    return defined( 'SRFM_VER' );
                },
            ],
            'metform' => [
                'title'         => __( 'MetForm', 'erp' ),
                'plugin_check'  => function () {
                    return class_exists( '\MetForm\Plugin' );
                },
            ],
        ];
    }

    /**
     * Advertise the pro integrations as locked tabs
     *
     * @param array $plugins
     *
     * @return array
     */
    public function add_pro_plugins( $plugins ) {
        foreach ( $this->get_pro_plugins() as $slug => $info ) {
            // erp-pro already registered this integration — leave it alone.
            if ( isset( $plugins[ $slug ] ) ) {
                continue;
            }

            $plugins[ $slug ] = [
                'title'                => $info['title'],
                // keep the tab visible regardless of the form plugin status
                'is_active'            => true,
                'is_pro'               => true,
                'upgrade_url'          => self::UPGRADE_URL,
                // whether the underlying form plugin is present (for the hint)
                'form_plugin_active'   => (bool) call_user_func( $info['plugin_check'] ),
            ];
        }

        return $plugins;
    }
}
