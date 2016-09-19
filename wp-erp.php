<?php
/**
 * Plugin Name: WP ERP
 * Description: An Open Source ERP Solution for WordPress. Built-in HR, CRM and Accounting system for WordPress
 * Plugin URI: https://wperp.com
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.1.5
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
    public $version = '1.1.5';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.4.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Initializes the WeDevs_ERP() class
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor for the WeDevs_ERP class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {
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
        if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
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

        $error = __( '<h1>An Error Occured</h1>', 'erp' );
        $error .= __( '<h2>Your installed PHP Version is: ', 'erp' ) . PHP_VERSION . '</h2>';
        $error .= __( '<p>The <strong>WP ERP</strong> plugin requires PHP version <strong>', 'erp' ) . $this->min_php . __( '</strong> or greater', 'erp' );
        $error .= __( '<p>The version of your PHP is ', 'erp' ) . '<a href="http://php.net/supported-versions.php" target="_blank"><strong>' . __( 'unsupported and old', 'erp' ) . '</strong></a>.';
        $error .= __( 'You should update your PHP software or contact your host regarding this matter.</p>', 'erp' );
        wp_die( $error, __( 'Plugin Activation Error', 'erp' ), array( 'response' => 200, 'back_link' => true ) );
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

        require_once WPERP_INCLUDES . '/class-install.php';
        require_once WPERP_INCLUDES . '/functions.php';
        require_once WPERP_INCLUDES . '/actions-filters.php';
        require_once WPERP_INCLUDES . '/functions-html.php';
        require_once WPERP_INCLUDES . '/functions-company.php';
        require_once WPERP_INCLUDES . '/functions-people.php';
        require_once WPERP_INCLUDES . '/lib/class-wedevs-insights.php';

        if ( is_admin() ) {
            require_once WPERP_INCLUDES . '/admin/functions.php';
            require_once WPERP_INCLUDES . '/admin/class-menu.php';
            require_once WPERP_INCLUDES . '/admin/class-admin.php';
        }
    }

    /**
     * Instantiate classes
     *
     * @return void
     */
    private function instantiate() {

        new \WeDevs\ERP\Admin\User_Profile();
        new \WeDevs\ERP\Scripts();
        new \WeDevs\ERP\Updates();
        new \WeDevs\ERP\Tracker();

        $this->container['modules']     = new \WeDevs\ERP\Framework\Modules();
        $this->container['emailer']     = \WeDevs\ERP\Emailer::init();
        $this->container['integration'] = \WeDevs\ERP\Integration::init();
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'init', array( $this, 'setup_database' ) );

        // initialize emailer class
        add_action( 'erp_loaded', array( $this->container['emailer'], 'init_emails' ) );

        // initialize integration class
        add_action( 'erp_loaded', array( $this->container['integration'], 'init_integrations' ) );
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

        foreach ($modules as $key => $module) {

            if ( ! $this->modules->is_module_active( $key ) ) {
                continue;
            }

            if ( isset( $module['callback'] ) && class_exists( $module['callback'] ) ) {
                new $module['callback']( $this );
            }
        }
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
