<?php

namespace WeDevs\ERP\HRM\Update;

/*
 * workflow:
 * 1. create new dbtables with new suffix
 * 2. migrate old data to new db tables
 * 3. delete old db tables
 * 4. rename new db tables removing new suffix
 *
 */
// this task not yet complete.

class ERP_1_6_0 {

    /**
     * Database tables to create.
     *
     * @var array
     */
    protected $db_tables = [];

    /**
     * Old database tables to delete.
     *
     * @var array
     */
    protected $db_tables_old = [];

    public function __construct() {
        global $wpdb;

        $this->db_tables = [
            "{$wpdb->prefix}erp_hr_leaves"                     => "{$wpdb->prefix}erp_hr_leaves_new",
            "{$wpdb->prefix}erp_hr_leave_policies"             => "{$wpdb->prefix}erp_hr_leave_policies_new",
            "{$wpdb->prefix}erp_hr_leave_policies_segregation" => "{$wpdb->prefix}erp_hr_leave_policies_segregation_new",
            "{$wpdb->prefix}erp_hr_leave_entitlements"         => "{$wpdb->prefix}erp_hr_leave_entitlements_new",
            "{$wpdb->prefix}erp_hr_leave_requests"             => "{$wpdb->prefix}erp_hr_leave_requests_new",
            "{$wpdb->prefix}erp_hr_leave_request_details"      => "{$wpdb->prefix}erp_hr_leave_request_details_new",
            "{$wpdb->prefix}erp_hr_leave_approval_status"      => "{$wpdb->prefix}erp_hr_leave_approval_status_new",
            "{$wpdb->prefix}erp_hr_leave_encashment_requests"  => "{$wpdb->prefix}erp_hr_leave_encashment_requests_new",
            "{$wpdb->prefix}erp_hr_leaves_unpaid"              => "{$wpdb->prefix}erp_hr_leaves_unpaid_new",
            "{$wpdb->prefix}erp_hr_financial_years"            => "{$wpdb->prefix}erp_hr_financial_years_new",
        ];

        $this->db_tables_old = [
            "{$wpdb->prefix}erp_hr_leave_entitlements"  => "{$wpdb->prefix}erp_hr_leave_entitlements_old",
            "{$wpdb->prefix}erp_hr_leave_policies"      => "{$wpdb->prefix}erp_hr_leave_policies_old",
            "{$wpdb->prefix}erp_hr_leave_requests"      => "{$wpdb->prefix}erp_hr_leave_requests_old",
            "{$wpdb->prefix}erp_hr_leaves"              => "{$wpdb->prefix}erp_hr_leaves_old",
        ];
    }

    /**
     * This method will create all required db tables.
     *
     * @since 1.6.0
     *
     * @return bool
     */
    public function create_db_tables() {
        global $wpdb;

        $charset = 'CHARSET=utf8mb4';
        $collate = 'COLLATE=utf8mb4_unicode_ci';

        $charset_collate = $charset . ' ' . $collate;

        $table_schema = [
            "CREATE TABLE {$wpdb->prefix}erp_hr_leaves_new (
                  id smallint(6) NOT NULL AUTO_INCREMENT,
                  name varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                  description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id)
              ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_policies_new (
                  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_id smallint(5) UNSIGNED NOT NULL,
                  old_policy_id int(11) UNSIGNED DEFAULT NULL,
                  description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  color varchar(10) DEFAULT NULL,
                  apply_limit tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  employee_type enum('-1','permanent','parttime','contract','temporary','trainee') NOT NULL DEFAULT 'permanent',
                  department_id int(11) NOT NULL DEFAULT '-1',
                  location_id int(11) NOT NULL DEFAULT '-1',
                  designation_id int(11) NOT NULL DEFAULT '-1',
                  gender enum('-1','male','female','other') NOT NULL DEFAULT '-1',
                  marital enum('-1','single','married','widowed') NOT NULL DEFAULT '-1',
                  f_year smallint(5) UNSIGNED DEFAULT NULL,
                  apply_for_new_users tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  carryover_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  carryover_uses_limit tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  encashment_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  encashment_based_on enum('pay_rate','basic','gross') DEFAULT NULL,
                  forward_default enum('encashment','carryover') NOT NULL DEFAULT 'encashment',
                  applicable_from_days smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                  accrued_amount decimal(10,2) NOT NULL DEFAULT '0.00',
                  accrued_max_days smallint(4) UNSIGNED NOT NULL DEFAULT '0',
                  halfday_enable tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY leave_id (leave_id),
                  KEY f_year (f_year)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_policies_segregation_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_policy_id bigint(20) UNSIGNED NOT NULL,
                  jan tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  feb tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  mar tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  apr tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  may tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  jun tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  jul tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  aug tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  sep tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  oct tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  nov tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  decem tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY leave_policy_id (leave_policy_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_entitlements_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  created_by bigint(20) UNSIGNED DEFAULT NULL,
                  trn_id bigint(20) UNSIGNED NOT NULL,
                  trn_type enum('leave_policies','leave_approval_status','leave_encashment_requests','leave_entitlements','unpaid_leave','leave_encashment', 'leave_carryforward', 'manual_leave_policies', 'Accounts', 'others', 'leave_accrual', 'carry_forward_leave_expired') NOT NULL DEFAULT 'leave_policies',
                  day_in decimal(5,1) UNSIGNED NOT NULL DEFAULT '0.0',
                  day_out decimal(5,1) UNSIGNED NOT NULL DEFAULT '0.0',
                  description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  f_year smallint(6) NOT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY comp_key_1 (user_id,leave_id,f_year,trn_type),
                  KEY trn_id (trn_id),
                  KEY leave_id (leave_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_requests_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  leave_entitlement_id bigint(20) UNSIGNED NOT NULL default '0',
                  day_status_id smallint(5) UNSIGNED NOT NULL DEFAULT '1',
                  days decimal(5,1) UNSIGNED NOT NULL DEFAULT '0.0',
                  start_date int(11) NOT NULL,
                  end_date int(11) NOT NULL,
                  reason text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  last_status smallint(6) UNSIGNED NOT NULL DEFAULT '2',
                  created_by bigint(20) UNSIGNED DEFAULT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY user_id (user_id),
                  KEY user_leave (user_id,leave_id),
                  KEY user_entitlement (user_id,leave_entitlement_id),
                  KEY last_status (last_status),
                  KEY leave_entitlement_id (leave_entitlement_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_approval_status_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_request_id bigint(20) UNSIGNED NOT NULL,
                  approval_status_id tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  approved_by bigint(20) UNSIGNED DEFAULT NULL,
                  message text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY leave_request_id (leave_request_id),
                  KEY approval_status_id (approval_status_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_request_details_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_request_id bigint(20) UNSIGNED NOT NULL,
                  leave_approval_status_id bigint(20) UNSIGNED NOT NULL,
                  workingday_status tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
                  user_id bigint(20) UNSIGNED NOT NULL,
                  f_year smallint(6) NOT NULL,
                  leave_date int(11) NOT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY leave_request_id (leave_request_id),
                  KEY user_id (user_id),
                  KEY user_fyear_leave (user_id,f_year,leave_date)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_encashment_requests_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  approved_by bigint(20) UNSIGNED DEFAULT NULL,
                  approval_status_id tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
                  encash_days decimal(4,1) UNSIGNED NOT NULL DEFAULT '0.0',
                  forward_days decimal(4,1) UNSIGNED NOT NULL DEFAULT '0.0',
                  amount decimal(20,2) NOT NULL DEFAULT '0.00',
                  total decimal(20,2) NOT NULL DEFAULT '0.00',
                  f_year smallint(5) UNSIGNED NOT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY user_id (user_id),
                  KEY leave_id (leave_id),
                  KEY f_year (f_year)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leaves_unpaid_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  leave_request_id bigint(20) UNSIGNED NOT NULL,
                  leave_approval_status_id bigint(20) UNSIGNED NOT NULL,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  days decimal(4,1) UNSIGNED NOT NULL DEFAULT '0.0',
                  amount decimal(20,2) NOT NULL DEFAULT '0.00',
                  total decimal(20,2) NOT NULL DEFAULT '0.00',
                  f_year smallint(5) UNSIGNED NOT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY user_id (user_id),
                  KEY leave_id (leave_id),
                  KEY f_year (f_year),
                  KEY leave_request_id (leave_request_id),
                  KEY leave_approval_status_id (leave_approval_status_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_financial_years_new (
                  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  fy_name varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  start_date int(11) DEFAULT NULL,
                  end_date int(11) DEFAULT NULL,
                  description varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  created_by bigint(20) UNSIGNED DEFAULT NULL,
                  updated_by bigint(20) UNSIGNED DEFAULT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY (id),
                  KEY year_search (start_date,end_date),
                  KEY start_date (start_date),
                  KEY end_date (end_date)
            ) $charset_collate;",
        ];

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

        return true;
    }

    /**
     * This method will start data migration process.
     *
     * @since 1.6.0
     */
    public function migrate_data() {
        global $wpdb;
        global $bg_progess_hr_leaves_entitlements;
        global $bg_progess_hr_leave_requests;

        $already_done = get_option( 'policy_migrate_data_1_6_0', 0 );

        if ( $already_done ) {
            return;
        }

        update_option( 'policy_migrate_data_1_6_0', 1 );

        /**
         * Get all leave entitlements from old db and add them to process queue.
         */
        $entitlement_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements ORDER BY id ASC" );

        if ( is_array( $entitlement_ids ) && ! empty( $entitlement_ids ) ) {
            foreach ( $entitlement_ids as $entitlement_id ) {
                $bg_progess_hr_leaves_entitlements->push_to_queue(
                    [
                        'task' => 'task_required_data',
                        'id'   => $entitlement_id,
                    ]
                );
            }
        } else {
            $bg_progess_hr_leaves_entitlements->push_to_queue(
                [
                    'task' => 'exit_from_here',
                ]
            );
        }

        $bg_progess_hr_leaves_entitlements->save();

        /**
         * Get all leave requests from old db and add them to process queue
         */
        $request_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_leave_requests ORDER BY id ASC" );

        if ( is_array( $request_ids ) && ! empty( $request_ids ) ) {
            foreach ( $request_ids as $request_id ) {
                $bg_progess_hr_leave_requests->push_to_queue(
                    [
                        'task' => 'leave_request',
                        'id'   => $request_id,
                    ]
                );
            }
        } else {
            $bg_progess_hr_leave_requests->push_to_queue(
                [
                    'task' => 'exit_from_here',
                ]
            );
        }

        $bg_progess_hr_leave_requests->save();

        /*
         * Run the queue, starting with leave entitlements data
         */
        $bg_progess_hr_leaves_entitlements->dispatch();
    }

    /**
     * Call this method after migrating old data
     */
    public function alter_old_db_tables() {
        global $wpdb;
        $queries = 'RENAME TABLE ';

        foreach ( $this->db_tables_old as $old_name => $new_name ) {
            $queries .= "$old_name TO $new_name";

            if ( next( $this->db_tables_old ) !== false ) {
                $queries .= ', ';
            } else {
                $queries .= ';';
            }
        }

        if ( $wpdb->query( $queries ) === false ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            // query error, log this to db
            error_log(
                print_r(
                    [
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => '(Query error) Old Table renaming failed: ' . $wpdb->last_error,
                    ],
                    true
                )
            );
        } else {
            return true;
        }
    }

    /**
     * Call this method after migrating all datas
     */
    public function alter_new_db_tables() {
        global $wpdb;
        $queries = 'RENAME TABLE ';

        foreach ( $this->db_tables as $new_name => $old_name ) {
            $queries .= "$old_name TO $new_name";

            if ( next( $this->db_tables ) !== false ) {
                $queries .= ', ';
            } else {
                $queries .= ';';
            }
        }

        if ( $wpdb->query( $queries ) === false ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            // query error, log this to db
            error_log(
                print_r(
                    [
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => '(Query error) Table renaming failed: ' . $wpdb->last_error,
                    ],
                    true
                )
            );
        } else {
            return true;
        }
    }
}

$erp_update_1_6_0 = new ERP_1_6_0();

if ( $erp_update_1_6_0->create_db_tables() ) {
    $erp_update_1_6_0->migrate_data();
}
