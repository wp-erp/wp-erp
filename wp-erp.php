<?php
/**
 * Plugin Name: WP ERP
 * Description: An Open Source ERP Solution for WordPress. Built-in HR, CRM and Accounting system for WordPress
 * Plugin URI: https://wperp.com
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.5.16
 * License: GPL2
 * Text Domain: erp
 * Domain Path: /i18n/languages/
 *
 * Copyright (c) 2016 weDevs (email: info@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WeDevs_ERP class
 *
 * @class WeDevs_ERP The class that holds the entire WeDevs_ERP plugin
 */
final class WeDevs_ERP {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.5.16';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.6.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * @var object
     *
     * @since 1.2.1
     */
    private static $instance;

    /**
     * Initializes the WeDevs_ERP() class
     *
     * @since 0.1
     * @since 1.2.1 Rename `__construct` function to `setup` and call it only once
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     *
     * @return object
     */
    public static function init() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WeDevs_ERP ) ) {
            self::$instance = new WeDevs_ERP;
            self::$instance->setup();
        }

        return self::$instance;
    }

    /**
     * Setup the plugin
     *
     * Sets up all the appropriate hooks and actions within our plugin.
     *
     * @since 1.2.1
     *
     * @return void
     *
     */
    private function setup() {
        // dry check on older PHP versions, if found deactivate itself with an error
        register_activation_hook( __FILE__, array( $this, 'auto_deactivate' ) );

        if ( ! $this->is_supported_php() ) {
            return;
        }

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // instantiate classes
        $this->instantiate();

        // Initialize the action hooks
        $this->init_actions();

        // load the modules
        $this->load_module();

        // Loaded action
        do_action( 'erp_loaded' );
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * Check if the PHP version is supported
     *
     * @return bool
     */
    public function is_supported_php() {
        if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Bail out if the php version is lower than
     *
     * @return void
     */
    function auto_deactivate() {
        if ( $this->is_supported_php() ) {
            return;
        }

        deactivate_plugins( basename( __FILE__ ) );

        $error  = __( '<h1>An Error Occured</h1>', 'erp' );
        $error .= __( '<h2>Your installed PHP Version is: ', 'erp' ) . PHP_VERSION . '</h2>';
        $error .= __( '<p>The <strong>WP ERP</strong> plugin requires PHP version <strong>', 'erp' ) . $this->min_php . __( '</strong> or greater', 'erp' );
        $error .= __( '<p>The version of your PHP is ', 'erp' ) . '<a href="http://php.net/supported-versions.php" target="_blank"><strong>' . __( 'unsupported and old', 'erp' ) . '</strong></a>.';
        $error .= __( 'You should update your PHP software or contact your host regarding this matter.</p>', 'erp' );
        wp_die(
            wp_kses_post( $error ),
            esc_html__( 'Plugin Activation Error', 'erp' ),
            array(
				'response'  => 200,
				'back_link' => true,
            )
        );
    }

    /**
     * Define the plugin constants
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPERP_VERSION', $this->version );
        define( 'WPERP_FILE', __FILE__ );
        define( 'WPERP_PATH', dirname( WPERP_FILE ) );
        define( 'WPERP_INCLUDES', WPERP_PATH . '/includes' );
        define( 'WPERP_MODULES', WPERP_PATH . '/modules' );
        define( 'WPERP_URL', plugins_url( '', WPERP_FILE ) );
        define( 'WPERP_ASSETS', WPERP_URL . '/assets' );
        define( 'WPERP_VIEWS', WPERP_INCLUDES . '/admin/views' );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        include dirname( __FILE__ ) . '/vendor/autoload.php';
        require_once WPERP_INCLUDES . '/functions.php';
        require_once WPERP_INCLUDES . '/class-install.php';
        require_once WPERP_INCLUDES . '/actions-filters.php';
        require_once WPERP_INCLUDES . '/functions-html.php';
        require_once WPERP_INCLUDES . '/functions-company.php';
        require_once WPERP_INCLUDES . '/functions-people.php';
        require_once WPERP_INCLUDES . '/api/class-api-registrar.php';
        require_once WPERP_INCLUDES . '/class-i18n.php';

        if ( is_admin() ) {
            require_once WPERP_INCLUDES . '/admin/functions.php';
            require_once WPERP_INCLUDES . '/admin/class-menu.php';
            require_once WPERP_INCLUDES . '/admin/class-admin.php';

            // Includes background process libs
            require_once WPERP_INCLUDES . '/lib/bgprocess/wp-async-request.php';
            require_once WPERP_INCLUDES . '/lib/bgprocess/wp-background-process.php';

            require_once WPERP_INCLUDES . '/updates/bp/class-erp-acct-bg-process-1.5.0.php';
            require_once WPERP_INCLUDES . '/updates/bp/class-erp-acct-bg-process-1.5.2.php';
        }

        // cli command
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            include WPERP_INCLUDES . '/cli/commands.php';
        }
    }

    /**
     * Instantiate classes
     *
     * @since 1.0.0
     * @since 1.2.0 Call `setup_database` to setup db immediately
     *
     * @return void
     */
    private function instantiate() {
        $this->setup_database();

        new \WeDevs\ERP\Admin\User_Profile();
        new \WeDevs\ERP\Scripts();
        new \WeDevs\ERP\Updates();
        new \WeDevs\ERP\API\API_Registrar();
        new \WeDevs\ERP\Promotion();
        new \WeDevs\ERP\AddonTask();
        new \WeDevs\ERP\ERP_i18n();

        // Appsero Tracker
        \WeDevs\ERP\Tracker::get_instance()->init();

        $this->container['modules']     = new \WeDevs\ERP\Framework\Modules();
        $this->container['emailer']     = \WeDevs\ERP\Emailer::init();
        $this->container['integration'] = \WeDevs\ERP\Integration::init();
        $this->container['google_auth'] = \WeDevs\ERP\CRM\Google_Auth::init();
        $this->container['google_sync'] = \WeDevs\ERP\CRM\Gmail_Sync::init();
    }

    /**
     * Initialize WordPress action hooks
     *
     * @since 1.0.0
     * @since 1.2.0 Remove `setup_database` hook from `init` action
     *
     * @return void
     */
    private function init_actions() {
        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // initialize emailer class
        add_action( 'erp_loaded', array( $this->container['emailer'], 'init_emails' ) );

        // initialize integration class
        add_action( 'erp_loaded', array( $this->container['integration'], 'init_integrations' ) );

        // Add plugin action links
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

        // Admin footer text
        add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 10, 1 );
    }

    /**
     * Add action links
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=erp-settings' ) . '">' . __( 'Settings', 'erp-deals' ) . '</a>';
        $links[] = '<a target="_blank" href="https://wperp.com/documentation/?utm_source=Free+Plugin&utm_medium=CTA&utm_content=Backend&utm_campaign=Docs">' . __( 'Docs', 'erp' ) . '</a>';
        return $links;
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'erp', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );
    }

    /**
     * Setup database related tasks
     *
     * @return void
     */
    public function setup_database() {
        global $wpdb;

        $wpdb->erp_peoplemeta = $wpdb->prefix . 'erp_peoplemeta';
    }

    /**
     * Load the current ERP module
     *
     * We don't load every module at once, just load
     * what is necessary
     *
     * @return void
     */
    public function load_module() {
        $modules = $this->modules->get_modules();

        if ( ! $modules ) {
            return;
        }

        foreach ( $modules as $key => $module ) {

            if ( ! $this->modules->is_module_active( $key ) ) {
                continue;
            }

            if ( isset( $module['callback'] ) && class_exists( $module['callback'] ) ) {
                new $module['callback']( $this );
            }
        }
    }

    /**
     * Admin footer text
     *
     * @since 1.4.2
     * @param  string $text
     * @return string
     */
    public function admin_footer_text( $text ) {
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $page = substr( $page, 0, 3 );

        if ( 'erp' === $page ) {
            $text = sprintf(
                /* translators: %s: review url */
                __( 'If you like WPERP please leave us a <a href="%s" target="_blank" style="text-decoration:none">★★★★★</a> rating. Thanking you from the team of WPERP in advance!' ),
                __( 'https://wordpress.org/support/plugin/erp/reviews/?filter=5' )
            );
        }

        return $text;
    }
} // WeDevs_ERP

/**
 * Init the wperp plugin
 *
 * @return WeDevs_ERP the plugin object
 */
function wperp() {
    return WeDevs_ERP::init();
}

// kick it off
wperp();
