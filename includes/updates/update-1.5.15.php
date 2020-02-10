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


class ERP_1_5_15 {
    /**
     * Database tables to create.
     *
     * @var array
     */
    protected $db_tables     = array();

    /**
     * Old database tables to delete.
     *
     * @var array
     */
    protected $db_tables_old = array();

    public function __construct() {
        global $wpdb;

        $this->db_tables = array(
            "{$wpdb->prefix}erp_hr_leaves"                => "{$wpdb->prefix}erp_hr_leaves_new",
            "{$wpdb->prefix}erp_hr_leave_policies"        => "{$wpdb->prefix}erp_hr_leave_policies_new",
            "{$wpdb->prefix}erp_hr_leave_policies_segregation" => "{$wpdb->prefix}erp_hr_leave_policies_segregation_new",
            "{$wpdb->prefix}erp_hr_leave_entitlements"    => "{$wpdb->prefix}erp_hr_leave_entitlements_new",
            "{$wpdb->prefix}erp_hr_leave_requests"        => "{$wpdb->prefix}erp_hr_leave_requests_new",
            "{$wpdb->prefix}erp_hr_leave_request_details" => "{$wpdb->prefix}erp_hr_leave_request_details_new",
            "{$wpdb->prefix}erp_hr_leave_approval_status" => "{$wpdb->prefix}erp_hr_leave_approval_status_new",
            "{$wpdb->prefix}erp_hr_leave_encashment_requests" => "{$wpdb->prefix}erp_hr_leave_encashment_requests_new",
            "{$wpdb->prefix}erp_hr_leaves_unpaid"         => "{$wpdb->prefix}erp_hr_leaves_unpaid_new",
        );

        $this->db_tables_old = array(
            "{$wpdb->prefix}erp_hr_leave_entitlements",
            "{$wpdb->prefix}erp_hr_leave_policies",
            "{$wpdb->prefix}erp_hr_leave_requests",
            "{$wpdb->prefix}erp_hr_leaves",
        );
    }

    public function create_db_tables() {
        global $wpdb;

        $charset = 'CHARSET=utf8mb4';
        $collate = 'COLLATE=utf8mb4_unicode_ci';

        $charset_collate = $charset . ' ' . $collate;

        $table_schema = array(
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
                  old_policy_id int(11) UNSIGNED NOT NULL,
                  description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  days tinyint(3) UNSIGNED NOT NULL,
                  color varchar(10) DEFAULT NULL,
                  department_id int(11) NOT NULL DEFAULT '-1',
                  location_id int(11) NOT NULL DEFAULT '-1',
                  designation_id int(11) NOT NULL DEFAULT '-1',
                  f_year smallint(5) UNSIGNED NOT NULL,
                  forward_status tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  encashment_status tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  gender enum('-1','male','female','other') NOT NULL DEFAULT '-1',
                  marital enum('-1','single','married','widowed') NOT NULL DEFAULT '-1',
                  applicable_from_days smallint(5) UNSIGNED NOT NULL DEFAULT '0',
                  accrued_amount decimal(5,2) NOT NULL DEFAULT '0.00',
                  accrued_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_policies_segregation_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_policy_id bigint(20) UNSIGNED NOT NULL,
                  jan tinyint(3) UNSIGNED DEFAULT '0',
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
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_entitlements_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  created_by bigint(20) UNSIGNED NOT NULL,
                  trn_id bigint(20) UNSIGNED NOT NULL,
                  trn_type enum('leave_policies','leave_approval_status','leave_encashment_requests','leave_entitlements','unpaid_leave','leave_encashment', 'manual_leave_policies', 'Accounts', 'others') NOT NULL DEFAULT 'leave_policies',
                  day_in tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  day_out tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  f_year smallint(6) NOT NULL,
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_requests_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  day_status_id smallint(5) UNSIGNED NOT NULL DEFAULT '1',
                  days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  start_date int(11) NOT NULL,
                  end_date int(11) NOT NULL,
                  reason text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  created_at int(11) DEFAULT NULL,
                  updated_at int(11) DEFAULT NULL,
                  PRIMARY KEY  (id),
                  KEY user_id (user_id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_request_details_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_request_id bigint(20) UNSIGNED NOT NULL,
                  leave_approval_status_id bigint(20) UNSIGNED NOT NULL,
                  workingday_status tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_date int(11) NOT NULL,
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_approval_status_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_request_id bigint(20) UNSIGNED NOT NULL,
                  approval_status_id tinyint(3) UNSIGNED NOT NULL,
                  approved_by bigint(20) UNSIGNED NOT NULL,
                  approved_date int(11) NOT NULL,
                  forward_to bigint(20) UNSIGNED DEFAULT NULL,
                  message text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leave_encashment_requests_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  approved_by bigint(20) UNSIGNED NOT NULL,
                  approval_status_id tinyint(3) UNSIGNED NOT NULL,
                  encash_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  forward_days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  amount decimal(20,2) NOT NULL DEFAULT '0.00',
                  total decimal(20,2) NOT NULL DEFAULT '0.00',
                  f_year smallint(5) UNSIGNED NOT NULL,
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

            "CREATE TABLE {$wpdb->prefix}erp_hr_leaves_unpaid_new (
                  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  leave_id smallint(6) UNSIGNED NOT NULL,
                  leave_request_id bigint(20) UNSIGNED NOT NULL,
                  leave_approval_status_id bigint(20) UNSIGNED NOT NULL,
                  user_id bigint(20) UNSIGNED NOT NULL,
                  days tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  amount decimal(20,2) NOT NULL DEFAULT '0.00',
                  total decimal(20,2) NOT NULL DEFAULT '0.00',
                  status tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
                  created_at int(11) NOT NULL,
                  updated_at int(11) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate;",

        );

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }

        return true;
    }

    public function migrate_data() {
        global $wpdb;
        global $bg_progess_hr_leaves_1_5_15;
        global $bg_progess_hr_leaves_entitlements;
        global $bg_progess_hr_leave_requests;

        $already_done = get_option('policy_migrate_data', 0);

        if ( $already_done ) {
            return;
        }

        update_option( 'policy_migrate_data', 1 );

        /**
         * Leave policies BG process save
         * 
         * get all leave policies from old db
         */
        $policies = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_leave_policies ORDER BY id ASC" );

        if ( is_array( $policies ) && ! empty( $policies ) ) {
            foreach ( $policies as $policy ) {
                $bg_progess_hr_leaves_1_5_15->push_to_queue( $policy );
            }
        } else {
            error_log( print_r(
                array(
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => 'No policies found.'
                ), true )
            );
            // todo: add some functionality if no policies is found.
        }

        $bg_progess_hr_leaves_1_5_15->save();



        /**
         * Leave entitlements BG process save
         * 
         * get all leave entitlements from old db
         */
        $entitlement_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements ORDER BY id ASC" );

        if ( is_array( $entitlement_ids ) && ! empty( $entitlement_ids ) ) {
            foreach ( $entitlement_ids as $entitlement_id ) {
                $bg_progess_hr_leaves_entitlements->push_to_queue( $entitlement_id );
            }
        } else {
            // todo: add some functionality if no policies is found.
        }

        $bg_progess_hr_leaves_entitlements->save();



        /**
         * Leave requests BG process save
         *
         * get all leave requests from old db
         */
        $request_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_leave_requests ORDER BY id ASC" );

        if ( is_array( $request_ids ) && ! empty( $request_ids ) ) {
            foreach ( $request_ids as $request_id ) {
                $bg_progess_hr_leave_requests->push_to_queue( array(
                    'task'  => 'leave_request',
                    'id'    => $request_id
                ) );
            }
        } else {
            // todo: add some functionality if no leave request is found.
        }

        $bg_progess_hr_leave_requests->save();



        /**
         * Boot leaves data migration
         */
        $bg_progess_hr_leaves_1_5_15->dispatch();
    }

    /**
     * Call this methode after migrating old data
     */
    public function delete_old_db_tables() {
        global $wpdb;
        if ( $wpdb->query( 'DROP TABLE ' . implode( ', ', $this->db_tables_old ) . ';' ) === false ) {
            // todo: mysql query error, store this error to log
            error_log( print_r(
                array(
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => '(Query error) Table drop failed.'
                ), true )
            );
        }
        else {
            return true;
        }
    }

    /**
     *
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

        if ( $wpdb->query( $queries ) === false ) {
            // query error, log this to db
            error_log( print_r(
                array(
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => '(Query error) Table renaming failed.'
                ), true )
            );
        }
        else {
            return true;
        }
    }

}

$erp_update_1_5_15 = new ERP_1_5_15();
if ( $erp_update_1_5_15->create_db_tables() ) {
    $erp_update_1_5_15->migrate_data();
}


