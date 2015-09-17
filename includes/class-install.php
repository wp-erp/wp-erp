<?php
/**
 * Installation related functions and actions.
 *
 * @author Tareq Hasan
 * @package ERP
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Installer Class
 *
 * @package ERP
 */
class WeDevs_ERP_Installer {

    use \WeDevs\ERP\Framework\Traits\Hooker;

    private $min_php = '5.4.0';

    /**
     * Bind the events
     */
    function __construct() {
        register_activation_hook( WPERP_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( WPERP_FILE, array( $this, 'deactivate' ) );

        $this->action( 'admin_menu', 'welcome_screen_menu' );
        $this->action( 'admin_head', 'welcome_screen_menu_remove' );

        // $this->action( 'activated_plugin', 'welcome_redirect' );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {

        // bail out if the php version is lower than
        if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
                deactivate_plugins( basename( WPERP_FILE ) );

                $error = '<h1>An Error Occured</h1>';
                $error .= '<h2>Your installed PHP Version is: ' . PHP_VERSION . '</h2>';
                $error .= '<p>The <strong>WP ERP</strong> plugin requires PHP version <strong>' . $this->min_php . '</strong> or greater';
                $error .= '<p>The version of your PHP is <a href="http://php.net/supported-versions.php" target="_blank"><strong>unsupported and old</strong></a>. ';
                $error .= 'You should update your PHP software or contact your host regarding this matter.</p>';
                wp_die( $error, 'Plugin Activation Error', array( 'response' => 200, 'back_link' => true ) );
        }

        $this->create_tables();
        $this->create_roles();

        update_option( 'wp_erp_version', erp_get_version() );
    }

    /**
     * Redirect to plugin welcome screen after activation
     *
     * @param  string  $plugin
     *
     * @return void
     */
    public function welcome_redirect( $plugin ) {
        if ( $plugin == 'wp-erp/wp-erp.php' ) {
            wp_safe_redirect( admin_url( 'index.php?page=wp-erp-welcome' ) );
            die();
        }
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    public function welcome_screen_menu() {
        add_dashboard_page( __( 'Welcome to WP ERP', 'wp-erp' ), 'WP ERP', 'read', 'wp-erp-welcome', array( $this, 'welcome_screen_content' ) );
    }

    public function welcome_screen_menu_remove() {
        remove_submenu_page( 'index.php', 'wp-erp-welcome' );
    }

    public function welcome_screen_content() {
        include WPERP_VIEWS . '/welcome-screen.php';
    }

    public function create_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                    $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty($wpdb->collate ) ) {
                    $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = [

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_company_locations` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `company_id` int(11) unsigned DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `address_1` varchar(255) DEFAULT NULL,
                `address_2` varchar(255) DEFAULT NULL,
                `city` varchar(100) DEFAULT NULL,
                `state` varchar(100) DEFAULT NULL,
                `zip` int(6) DEFAULT NULL,
                `country` varchar(5) DEFAULT NULL,
                `fax` varchar(20) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `company_id` (`company_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_depts` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(200) NOT NULL DEFAULT '',
                `description` text,
                `lead` int(11) unsigned DEFAULT '0',
                `parent` int(11) unsigned DEFAULT '0',
                `status` tinyint(1) unsigned DEFAULT '1',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_designations` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(200) NOT NULL DEFAULT '',
                `description` text,
                `status` tinyint(1) DEFAULT '1',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_employees` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                `employee_id` varchar(20) DEFAULT NULL,
                `designation` int(11) unsigned NOT NULL DEFAULT '0',
                `department` int(11) unsigned NOT NULL DEFAULT '0',
                `location` int(10) unsigned NOT NULL DEFAULT '0',
                `hiring_source` varchar(20) NOT NULL,
                `hiring_date` date NOT NULL,
                `termination_date` date NOT NULL,
                `date_of_birth` date NOT NULL,
                `reporting_to` bigint(20) unsigned NOT NULL DEFAULT '0',
                `pay_rate` int(11) unsigned NOT NULL DEFAULT '0',
                `pay_type` varchar(20) NOT NULL DEFAULT '',
                `type` varchar(20) NOT NULL,
                `status` varchar(10) NOT NULL DEFAULT '',
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`user_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_employee_history` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                `module` varchar(20) DEFAULT NULL,
                `category` varchar(20) DEFAULT NULL,
                `type` varchar(20) DEFAULT NULL,
                `comment` text,
                `data` longtext,
                `date` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`user_id`),
                KEY `module` (`module`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_employee_notes` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                `comment` text NOT NULL,
                `comment_by` bigint(20) unsigned NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_leave_policies` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(20) DEFAULT NULL,
                `value` mediumint(5) DEFAULT NULL,
                `color` varchar(7) DEFAULT NULL,
                `department` int(11) NOT NULL,
                `designation` int(11) NOT NULL,
                `gender` varchar(50) NOT NULL,
                `marital` varchar(50) NOT NULL,
                `description` LONGTEXT NOT NULL,
                `location` INT(3) NOT NULL,
                `effective_date` TIMESTAMP NOT NULL,
                `activate` INT(2) NOT NULL,
                `execute_day` INT(11) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_holiday` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar(200) NOT NULL,
                `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                `description` text NOT NULL,
                `range_status` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_leave_entitlements` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `policy_id` int(11) unsigned DEFAULT NULL,
                `days` mediumint(4) DEFAULT NULL,
                `from_date` datetime NOT NULL,
                `to_date` datetime NOT NULL,
                `comments` text,
                `status` tinyint(2) unsigned NOT NULL,
                `created_by` bigint(20) unsigned DEFAULT NULL,
                `created_on` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`user_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_leaves` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `request_id` bigint(20) unsigned NOT NULL,
                `date` date NOT NULL,
                `length_hours` decimal(6,2) unsigned NOT NULL,
                `length_days` decimal(6,2) NOT NULL,
                `start_time` time NOT NULL,
                `end_time` time NOT NULL,
                `duration_type` tinyint(4) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                KEY `request_id` (`request_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_leave_requests` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) unsigned NOT NULL,
                `policy_id` int(11) unsigned NOT NULL,
                `days` tinyint(3) unsigned DEFAULT NULL,
                `start_date` datetime NOT NULL,
                `end_date` datetime NOT NULL,
                `comments` text,
                `reason` text NOT NULL,
                `status` tinyint(2) unsigned DEFAULT NULL,
                `created_by` bigint(20) unsigned DEFAULT NULL,
                `updated_by` bigint(20) unsigned DEFAULT NULL,
                `created_on` datetime NOT NULL,
                `updated_on` datetime DEFAULT NULL,
                `last_date` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`user_id`),
                KEY `policy_id` (`policy_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_work_exp` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) DEFAULT NULL,
                `company_name` varchar(100) DEFAULT NULL,
                `job_title` varchar(100) DEFAULT NULL,
                `from` date DEFAULT NULL,
                `to` date DEFAULT NULL,
                `description` text,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_education` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) unsigned DEFAULT NULL,
                `school` varchar(100) DEFAULT NULL,
                `degree` varchar(100) DEFAULT NULL,
                `field` varchar(100) DEFAULT NULL,
                `finished` int(4) unsigned DEFAULT NULL,
                `notes` text,
                `interest` text,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_dependents` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) DEFAULT NULL,
                `name` varchar(100) DEFAULT NULL,
                `relation` varchar(100) DEFAULT NULL,
                `dob` date DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_hr_employee_performance` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `employee_id` int(11) unsigned DEFAULT NULL,
                `reporting_to` int(11) unsigned DEFAULT NULL,
                `job_knowledge` varchar(100) DEFAULT NULL,
                `work_quality` varchar(100) DEFAULT NULL,
                `attendance` varchar(100) DEFAULT NULL,
                `communication` varchar(100) DEFAULT NULL,
                `dependablity` varchar(100) DEFAULT NULL,
                `reviewer` int(11) unsigned DEFAULT NULL,
                `comments` text,
                `completion_date` datetime DEFAULT NULL,
                `goal_description` text,
                `employee_assessment` text,
                `supervisor` int(11) unsigned DEFAULT NULL,
                `supervisor_assessment` text,
                `type` text,
                `performance_date` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `employee_id` (`employee_id`)
            ) $collate;"

        ];

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

    }

    /**
     * Create user roles and capabilities
     *
     * @return void
     */
    public function create_roles() {

        // Manager role
        add_role( erp_hr_get_manager_role(), __( 'HR Manager', 'wp-erp' ), erp_hr_get_caps_for_role( erp_hr_get_manager_role() ) );

        // Employee role
        add_role( erp_hr_get_employee_role(), __( 'Employee', 'wp-erp' ), erp_hr_get_caps_for_role( erp_hr_get_employee_role() ) );
    }
}

new WeDevs_ERP_Installer();