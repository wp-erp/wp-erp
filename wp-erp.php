<?php
/**
 * Plugin Name: WP ERP
 * Description: ERP solution for WordPress
 * Plugin URI: http://wedevs.com/plugin/erp/
 * Author: Tareq Hasan
 * Author URI: http://wedevs.com
 * Version: 1.0
 * License: GPL2
 * Text Domain: wp-erp
 * Domain Path: languages
 *
 * Copyright (c) 2014 Tareq Hasan (email: info@wedevs.com). All rights reserved.
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
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs_ERP class
 *
 * @class WeDevs_ERP The class that holds the entire WeDevs_ERP plugin
 */
class WeDevs_ERP {

    /**
     * @var string
     */
    public $version = '0.1';

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
     *
     * @return void
     */
    public function __construct() {

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // Initialize the action hooks
        $this->init_actions();

        // Loaded action
        do_action( 'erp_loaded' );
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
        require_once WPERP_INCLUDES . '/class-install.php';

        if ( version_compare( PHP_VERSION, '5.3.0', '<=' ) ) {
            return;
        }

        require_once WPERP_INCLUDES . '/functions.php';
        require_once WPERP_INCLUDES . '/functions-html.php';
        require_once WPERP_INCLUDES . '/functions-company.php';

        require_once WPERP_INCLUDES . '/class-abstract-item.php';
        require_once WPERP_INCLUDES . '/class-countries.php';
        require_once WPERP_INCLUDES . '/class-company.php';
        require_once WPERP_MODULES . '/hrm/includes/class-employee.php';

        require_once WPERP_INCLUDES . '/admin/class-form-handler.php';
        require_once WPERP_MODULES . '/modules.php';


        if ( is_admin() ) {
            require_once WPERP_INCLUDES . '/admin/functions.php';
            require_once WPERP_INCLUDES . '/admin/class-menu.php';
            require_once WPERP_INCLUDES . '/admin/class-admin.php';
        }
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'init', array( $this, 'load_module' ) );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wp-erp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
        $current_module = erp_get_current_module();

        if ( $current_module ) {
            do_action( 'wp-erp-load-module_' . $current_module['slug'], $current_module );
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
