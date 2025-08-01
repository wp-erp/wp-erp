<?php
/**
 * Plugin Name: WP ERP
 * Description: An Open Source ERP Solution for WordPress. Built-in HR, CRM and Accounting system for WordPress
 * Plugin URI: https://wperp.com
 * Author: weDevs
 * Author URI: https://wedevs.com
 * Version: 1.16.1
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

use WeDevs\ERP\Emailer;
use WeDevs\ERP\Scripts;
use WeDevs\ERP\Tracker;
use WeDevs\ERP\Updates;
use WeDevs\ERP\ERP_i18n;
use WeDevs\ERP\Promotion;
use WeDevs\ERP\AddonTask;
use WeDevs\ERP\Integration;
use WeDevs\ERP\ValidateData;
use WeDevs\ERP\Settings\Ajax;
use WeDevs\ERP\CRM\GmailSync;
use WeDevs\ERP\CRM\GoogleAuth;
use WeDevs\ERP\Admin\AdminMenu;
use WeDevs\ERP\Admin\AdminPage;
use WeDevs\ERP\API\ApiRegistrar;
use WeDevs\ERP\Framework\Modules;
use WeDevs\ERP\Admin\UserProfile;
use WeDevs\ERP\WeDevsERPInstaller;

require_once __DIR__ . '/vendor/autoload.php';
define( 'WPERP_VERSION', '1.16.1' );
define( 'WPERP_FILE', __FILE__ );
define( 'WPERP_PATH', dirname( WPERP_FILE ) );
define( 'WPERP_INCLUDES', WPERP_PATH . '/includes' );
define( 'WPERP_MODULES', WPERP_PATH . '/modules' );
define( 'WPERP_URL', plugins_url( '', WPERP_FILE ) );
define( 'WPERP_ASSETS', WPERP_URL . '/assets' );
define( 'WPERP_VIEWS', WPERP_INCLUDES . '/Admin/views' );

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
    public $version = WPERP_VERSION;

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '7.2';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * @var WeDevs_ERP
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
     * @return WeDevs_ERP A single instance of this class.
     */
    public static function init() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WeDevs_ERP ) ) {
            self::$instance = new WeDevs_ERP();
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
     */
    private function setup() {
        // dry check on older PHP versions, if found deactivate itself with an error
        register_activation_hook( __FILE__, [ $this, 'auto_deactivate' ] );

        if ( ! $this->is_supported_php() ) {
            return;
        }

        // Define constants
        // $this->define_constants();

        // Include required files
        $this->includes();

        // instantiate classes
        $this->instantiate();

        if ( $this->is_need_to_upgrade() ) {
            return;
        }

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
     * Check if this version needs to upgrade in composer version 2.
     *
     * @return bool
     */
    public function is_need_to_upgrade() {
        $update_notice = new \WeDevs\ERP\Admin\ComposerUpgradeNotice();
        if ( $update_notice->need_to_upgrade() ) {
            return true;
        }

        return false;
    }

    /**
     * Bail out if the php version is lower than
     *
     * @return void
     */
    public function auto_deactivate() {
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
            [
                'response'  => 200,
                'back_link' => true,
            ]
        );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        include __DIR__ . '/vendor/autoload.php';

        require_once WPERP_INCLUDES . '/functions.php';
        require_once WPERP_INCLUDES . '/actions-filters.php';
        require_once WPERP_INCLUDES . '/functions-html.php';
        require_once WPERP_INCLUDES . '/functions-company.php';
        require_once WPERP_INCLUDES . '/functions-people.php';
//        require_once WPERP_INCLUDES . '/api/class-api-registrar.php';
//        require_once WPERP_INCLUDES . '/class-i18n.php';
        require_once WPERP_INCLUDES . '/functions-cache-helper.php';

        if ( is_admin() ) {
            require_once WPERP_INCLUDES . '/Admin/functions.php';
            // Includes background process libs
            require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-async-request.php';
            require_once WPERP_INCLUDES . '/Lib/bgprocess/wp-background-process.php';
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

        new AdminMenu();

        $this->container['modules'] = new Modules();
		// Erp pro is loaded in erp-mail action hook. Not to load that if upgrade is needed, we check in this place (along with L-143) too.
        if ( $this->is_need_to_upgrade() ) {
            return;
        }

        new AdminPage();
        new UserProfile();
        new Scripts();
        new Updates();
        new ApiRegistrar();
        new Promotion();
        new AddonTask();
        new ERP_i18n();
        new ValidateData();
        new Ajax();

        // Appsero Tracker
        Tracker::get_instance()->init();

        $this->container['emailer']     = Emailer::init();
        $this->container['integration'] = Integration::init();
        $this->container['google_auth'] = GoogleAuth::init();
        $this->container['google_sync'] = GmailSync::init();
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
        add_action( 'init', [ $this, 'localization_setup' ] );

        // initialize emailer class
        add_action( 'erp_loaded', [ $this->container['emailer'], 'init_emails' ] );

        // initialize integration class
        add_action( 'erp_loaded', [ $this->container['integration'], 'init_integrations' ] );

        // Add plugin action links
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

        // Enqueue footer queued js scripts
        add_action( 'admin_footer', 'erp_print_js', 25 );

        // Admin footer text
        add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ], 10, 1 );
        //add_action( 'admin_notices', array( $this, 'promotional_offer' ) );

    }


     /**
     * Get prmotion data
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function promotional_offer() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        // Check if inside the wp-project-manager page
        if ( ! isset( $_GET['page'] ) ) {
            // return;
        }

        $offer = $this->get_offer();
        if ( ! $offer->status ) {
            return;
        }

        ?>
            <style>
                #wperp-notice .content {
                    display: flex;
                    /* align-items: center; */
                }

                .wperp-promotional-offer-notice {
                    background: linear-gradient(30deg, #f2f2f2, #4a90e2);
                    color: #444;
                    border-left: 5px solid #4a90e2;
                }

                .wperp-promotional-offer-notice p {
                    font-size: 16px;
                    font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
                    color: #444;
                }

                .wperp-promotional-offer-notice a {
                    color: #fff;
                    display: inline-block;
                    margin-top: 18px;
                    border: 0.5px solid #4a90e2;
                    border-radius: 3px;
                    padding: 2px 5px 1px 5px;
                    text-decoration: none;
                    font-size: 16px;
                    padding: 4px 10px;
                    font-weight: 300;
                    background: #4a90e2;
                    /* font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; */
                }

                .wperp-promotional-offer-notice a:hover {
                    color: #fff;
                    border: 0.5px solid #357abd;
                    background: #357abd;
                }
                .welcome-panel .welcome-panel-close:before, .tagchecklist .ntdelbutton .remove-tag-icon:before, #bulk-titles .ntdelbutton:before, .notice-dismiss:before{
                    color:white;
                }
            </style>

            <div class="notice notice-success is-dismissible wperp-promotional-offer-notice" id="wperp-notice">
                <div class="content">
                <p style="margin-right:14px ;">
                        <img height="100" src="https://ps.w.org/erp/assets/icon-256x256.gif?rev=2818774" alt="">
                </p>
                <p>
                        <?php echo wp_kses( $offer->message, [ 'strong' => [], 'br' => [] ] ); ?>
                        <br>
                        <a class="link" target="_blank" href="<?php echo esc_url( $offer->link ); ?>">
                            <?php printf( esc_html__( '%s', 'erp' ), $offer->btn_txt ); ?>
                        </a>
                    </p>

                </div>
            </div>

            <script type='text/javascript'>

                jQuery('body').on('click', '#wperp-notice .notice-dismiss', function(e) {
                    e.preventDefault();

                    jQuery.ajax({
                        type: 'POST',
                        data: {
                            action: 'erp_dismiss_offer',
                            nonce: '<?php echo esc_attr( wp_create_nonce( 'wperp-dismiss-offer-notice' ) ); ?>',
                            wperp_offer_key: '<?php echo esc_attr( $offer->key ); ?>'
                        },
                        url: '<?php echo esc_url( admin_url( "admin-ajax.php" ) ); ?>',
                        success: function (res) {

                        }
                    });
                });
            </script>
        <?php
    }

     /**
     * Retrieves offer data.
     *
     * @return object
     */
    public function get_offer() {
        $offer         = new \stdClass;
        $offer->status = false;
        $promo_notice  = get_transient( 'wperp_promo_notice' );

        if ( false === $promo_notice ) {
            $promo_notice_url = 'https://raw.githubusercontent.com/wp-erp/erp-utils/refs/heads/main/promotions.json';
            $response         = wp_remote_get( $promo_notice_url, array( 'timeout' => 15 ) );

            if ( is_wp_error( $response ) || $response['response']['code'] !== 200 ) {
                return $offer;
            }

            $promo_notice = wp_remote_retrieve_body( $response );
            set_transient( 'wperp_promo_notice', $promo_notice, 6 * HOUR_IN_SECONDS );
        }

        $promo_notice = json_decode( $promo_notice, true );
        $current_time = new \DateTimeImmutable( 'now', new \DateTimeZone('America/New_York') );
        $current_time = $current_time->format( 'Y-m-d H:i:s T' );
        $disabled_key = get_option( 'wperp_offer_notice' );

        if ( $current_time >= $promo_notice['start_date'] && $current_time <= $promo_notice['end_date'] ) {
            $offer->link      = $promo_notice['action_url'];
            $offer->key       = $promo_notice['key'];
            $offer->btn_txt   = ! empty( $promo_notice['action_title'] ) ? $promo_notice['action_title'] : 'Get Now';
            $offer->message   = [];
            $offer->message[] = sprintf( __( '<strong>%s</strong>', 'erp' ), $promo_notice['title'] );

            if ( ! empty( $promo_notice['description'] ) ) {
                $offer->message[] = sprintf( __( '%s', 'erp' ), $promo_notice['description'] );
            }

            $offer->message[] = sprintf( __( '%s', 'erp' ), $promo_notice['content'] );
            $offer->message   = implode( '<br>', $offer->message );

            if ( $disabled_key != $promo_notice['key'] ) {
                $offer->status = true;
            }
        }

        return $offer;
    }
    /**
     * Add action links
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=erp-settings' ) . '">' . __( 'Settings', 'erp' ) . '</a>';
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
     *
     * @param string $text
     *
     * @return string
     */
    public function admin_footer_text( $text ) {
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $page = substr( $page, 0, 3 );

        if ( 'erp' === $page ) {
            $text = sprintf(
                /* translators: %s: review url */
                __( 'If you like WPERP please leave us a <a href="%s" target="_blank" style="text-decoration:none">★★★★★</a> rating. Thanking you from the team of WPERP in advance!', 'erp' ),
                'https://wordpress.org/support/plugin/erp/reviews/?filter=5'
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


add_action('init', function(){
    wperp();
}, 1);

register_activation_hook( __FILE__, function() {

    $installer = new WeDevsERPInstaller();
    $installer->activate();
} );
