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

    private $min_php = '5.4.0';

    function __construct() {
        register_activation_hook( WPERP_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( WPERP_FILE, array( $this, 'deactivate' ) );
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

        update_option( 'wp_erp_version', WPERP_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

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

        $table_schema = "CREATE TABLE `{$wpdb->prefix}erp_company_locations` (
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
            `created` datetime NOT NULL,
            `updated` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `company_id` (`company_id`)
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_depts` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL DEFAULT '',
            `description` text,
            `lead` int(11) unsigned DEFAULT '0',
            `parent` int(11) unsigned DEFAULT '0',
            `status` tinyint(1) unsigned DEFAULT '1',
            PRIMARY KEY (`id`)
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_designations` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(200) NOT NULL DEFAULT '',
            `description` text,
            `status` tinyint(1) DEFAULT '1',
            PRIMARY KEY (`id`)
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_employees` (
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
            PRIMARY KEY (`id`),
            KEY `employee_id` (`user_id`)
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_employee_history` (
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
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_employee_notes` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
            `comment` text NOT NULL,
            `comment_by` bigint(20) unsigned NOT NULL,
            `created_on` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_leave_policies` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(20) DEFAULT NULL,
            `unit` varchar(20) DEFAULT NULL,
            `value` mediumint(5) DEFAULT NULL,
            `color` varchar(7) DEFAULT NULL,
            `created_on` datetime NOT NULL,
            `updated_on` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_leave_entitlements` (
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
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_leaves` (
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
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_leave_requests` (
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
        ) $collate;

        CREATE TABLE `{$wpdb->prefix}erp_hr_work_exp` (
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
        ) $collate";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $table_schema );
    }

    /**
     * Create user roles and capabilities
     *
     * @return void
     */
    public function create_roles() {

        // Employee role
        add_role( 'employee', __( 'Employee', 'wp-erp' ), array(
            'read'                      => true,
            'edit_posts'                => false,
            'delete_posts'              => false
        ) );
    }
}

new WeDevs_ERP_Installer();