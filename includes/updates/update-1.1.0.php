<?php
/**
 * Create tables
 *
 * @package WPERP/Accounting
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ac_create_table() {
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

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_chart_classes` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_chart_types` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(60) NOT NULL DEFAULT '',
          `class_id` tinyint(3) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `class_id` (`class_id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_journals` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `ledger_id` int(11) unsigned NOT NULL,
          `transaction_id` bigint(20) unsigned NOT NULL,
          `type` varchar(20) DEFAULT NULL,
          `debit` DECIMAL(13,4) unsigned DEFAULT NULL,
          `credit` DECIMAL(13,4) unsigned DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `ledger_id` (`ledger_id`),
          KEY `transaction_id` (`transaction_id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_ledger` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `code` varchar(10) DEFAULT NULL,
          `name` varchar(100) DEFAULT NULL,
          `description` text,
          `parent` int(11) unsigned NOT NULL DEFAULT '0',
          `type_id` int(3) unsigned NOT NULL DEFAULT '0',
          `currency` varchar(10) DEFAULT '',
          `tax` bigint(20) DEFAULT NULL,
          `cash_account` tinyint(2) unsigned NOT NULL DEFAULT '0',
          `reconcile` tinyint(2) unsigned NOT NULL DEFAULT '0',
          `system` tinyint(2) unsigned NOT NULL DEFAULT '0',
          `active` tinyint(2) unsigned NOT NULL DEFAULT '1',
          `created_by` bigint(20) DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `code` (`code`),
          KEY `type_id` (`type_id`),
          KEY `parent` (`parent`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_banks` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `ledger_id` int(10) unsigned DEFAULT NULL,
          `account_number` varchar(20) DEFAULT NULL,
          `bank_name` varchar(30) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `ledger_id` (`ledger_id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_transactions` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `type` varchar(10) DEFAULT NULL,
          `form_type` varchar(20) DEFAULT NULL,
          `status` varchar(20) DEFAULT NULL,
          `user_id` bigint(20) unsigned DEFAULT NULL,
          `billing_address` tinytext,
          `ref` varchar(50) DEFAULT NULL,
          `summary` text,
          `issue_date` date DEFAULT NULL,
          `due_date` date DEFAULT NULL,
          `currency` varchar(10) DEFAULT NULL,
          `conversion_rate` decimal(2,2) unsigned DEFAULT NULL,
          `sub_total` DECIMAL(13,4) DEFAULT '0.00',
          `total` DECIMAL(13,4) DEFAULT '0.00',
          `due` DECIMAL(13,4) unsigned DEFAULT '0.00',
          `trans_total` DECIMAL(13,4) DEFAULT '0.00',
          `files` varchar(255) DEFAULT NULL,
          `parent` bigint(20) unsigned NOT NULL DEFAULT '0',
          `created_by` int(11) unsigned DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `type` (`type`),
          KEY `status` (`status`),
          KEY `issue_date` (`issue_date`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_transaction_items` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `transaction_id` bigint(20) unsigned DEFAULT NULL,
          `journal_id` bigint(20) unsigned DEFAULT NULL,
          `product_id` int(10) unsigned DEFAULT NULL,
          `description` text,
          `qty` tinyint(5) unsigned NOT NULL DEFAULT '1',
          `unit_price` DECIMAL(13,4) unsigned NOT NULL DEFAULT '0.00',
          `discount` tinyint(3) unsigned NOT NULL DEFAULT '0',
          `tax` tinyint(3) unsigned NOT NULL DEFAULT '0',
          `tax_rate` DECIMAL(13,4) NOT NULL,
          `tax_journal` BIGINT(20) NOT NULL,
          `line_total` DECIMAL(13,4) unsigned NOT NULL DEFAULT '0.00',
          `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `transaction_id` (`transaction_id`),
          KEY `journal_id` (`journal_id`),
          KEY `product_id` (`product_id`)
        ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_payments` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `transaction_id` int(11) NOT NULL,
        `parent` int(11) NOT NULL,
        `child` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_tax` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        `tax_number` varchar(255) DEFAULT NULL,
        `is_compound` varchar(5) DEFAULT NULL,
        `created_by` bigint(20) unsigned NOT NULL,
         PRIMARY KEY (`id`)
      ) $collate;",

      "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_ac_tax_items` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `tax_id` bigint(20) NOT NULL,
        `component_name` varchar(255) DEFAULT NULL,
        `agency_name` varchar(255) DEFAULT NULL,
        `tax_rate` float NOT NULL,
        PRIMARY KEY (`id`)
      ) $collate;",

    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}

/**
 * Populate all table datas
 *
 * @package WPERP/Accounting
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ac_populate_data() {
    global $wpdb;

    // check if classes exists
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_ac_chart_classes` LIMIT 0, 1" ) ) {
        $sql = "INSERT INTO `{$wpdb->prefix}erp_ac_chart_classes` (`id`, `name`)
                VALUES (1,'Assets'), (2,'Liabilities'), (3,'Expenses'), (4,'Income'), (5,'Equity');";

        $wpdb->query( $sql );
    }

    // check if chart types exists
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_ac_chart_types` LIMIT 0, 1" ) ) {
        $sql = "INSERT INTO `{$wpdb->prefix}erp_ac_chart_types` (`id`, `name`, `class_id`)
                VALUES (1,'Current Asset',1), (2,'Fixed Asset',1), (3,'Inventory',1),
                    (4,'Non-current Asset',1), (5,'Prepayment',1), (6,'Bank & Cash',1), (7,'Current Liability',2),
                    (8,'Liability',2), (9,'Non-current Liability',2), (10,'Depreciation',3),
                    (11,'Direct Costs',3), (12,'Expense',3), (13,'Revenue',4), (14,'Sales',4),
                    (15,'Other Income',4), (16,'Equity',5);";

        $wpdb->query( $sql );
    }

    // check if ledger exists
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_ac_ledger` LIMIT 0, 1" ) ) {

        $sql = "INSERT INTO `{$wpdb->prefix}erp_ac_ledger` (`id`, `code`, `name`, `description`, `parent`, `type_id`, `currency`, `tax`, `cash_account`, `reconcile`, `system`, `active`)
                    VALUES
                    (1,'120','Accounts Receivable',NULL,0,1,'',NULL,0,0,1,1),
                    (2,'140','Inventory',NULL,0,3,'',NULL,0,0,1,1),
                    (3,'150','Office Equipment',NULL,0,2,'',NULL,0,0,1,1),
                    (4,'151','Less Accumulated Depreciation on Office Equipment',NULL,0,2,'',NULL,0,0,1,1),
                    (5,'160','Computer Equipment',NULL,0,2,'',NULL,0,0,1,1),
                    (6,'161','Less Accumulated Depreciation on Computer Equipment',NULL,0,2,'',NULL,0,0,1,1),
                    (7,'090','Petty Cash',NULL,0,6,'',NULL,1,1,0,1),
                    (8,'200','Accounts Payable',NULL,0,7,'',NULL,0,0,1,1),
                    (9,'205','Accruals',NULL,0,7,'',NULL,0,0,0,1),
                    (10,'210','Unpaid Expense Claims',NULL,0,7,'',NULL,0,0,1,1),
                    (11,'215','Wages Payable',NULL,0,7,'',NULL,0,0,1,1),
                    (12,'216','Wages Payable - Payroll',NULL,0,7,'',NULL,0,0,0,1),
                    (13,'220','Sales Tax',NULL,0,7,'',NULL,0,0,1,1),
                    (14,'230','Employee Tax Payable',NULL,0,7,'',NULL,0,0,0,1),
                    (15,'235','Employee Benefits Payable',NULL,0,7,'',NULL,0,0,0,1),
                    (16,'236','Employee Deductions payable',NULL,0,7,'',NULL,0,0,0,1),
                    (17,'240','Income Tax Payable',NULL,0,7,'',NULL,0,0,0,1),
                    (18,'250','Suspense',NULL,0,7,'',NULL,0,0,0,1),
                    (19,'255','Historical Adjustments',NULL,0,7,'',NULL,0,0,1,1),
                    (20,'260','Rounding',NULL,0,7,'',NULL,0,0,1,1),
                    (21,'835','Revenue Received in Advance',NULL,0,7,'',NULL,0,0,0,1),
                    (22,'855','Clearing Account',NULL,0,7,'',NULL,0,0,0,1),
                    (23,'290','Loan',NULL,0,9,'',NULL,0,0,0,1),
                    (24,'500','Costs of Goods Sold',NULL,0,11,'',NULL,0,0,1,1),
                    (25,'600','Advertising',NULL,0,12,'',NULL,0,0,0,1),
                    (26,'605','Bank Service Charges',NULL,0,12,'',NULL,0,0,0,1),
                    (27,'610','Janitorial Expenses',NULL,0,12,'',NULL,0,0,0,1),
                    (28,'615','Consulting & Accounting',NULL,0,12,'',NULL,0,0,0,1),
                    (29,'620','Entertainment',NULL,0,12,'',NULL,0,0,0,1),
                    (30,'624','Postage & Delivary',NULL,0,12,'',NULL,0,0,0,1),
                    (31,'628','General Expenses',NULL,0,12,'',NULL,0,0,0,1),
                    (32,'632','Insurance',NULL,0,12,'',NULL,0,0,0,1),
                    (33,'636','Legal Expenses',NULL,0,12,'',NULL,0,0,0,1),
                    (34,'640','Utilities',NULL,0,12,'',NULL,0,0,1,1),
                    (35,'644','Automobile Expenses',NULL,0,12,'',NULL,0,0,0,1),
                    (36,'648','Office Expenses',NULL,0,12,'',NULL,0,0,1,1),
                    (37,'652','Printing & Stationary',NULL,0,12,'',NULL,0,0,0,1),
                    (38,'656','Rent',NULL,0,12,'',NULL,0,0,1,1),
                    (39,'660','Repairs & Maintenance',NULL,0,12,'',NULL,0,0,0,1),
                    (40,'664','Wages & Salaries',NULL,0,12,'',NULL,0,0,0,1),
                    (41,'668','Payroll Tax Expense',NULL,0,12,'',NULL,0,0,0,1),
                    (42,'672','Dues & Subscriptions',NULL,0,12,'',NULL,0,0,0,1),
                    (43,'676','Telephone & Internet',NULL,0,12,'',NULL,0,0,0,1),
                    (44,'680','Travel',NULL,0,12,'',NULL,0,0,0,1),
                    (45,'684','Bad Debts',NULL,0,12,'',NULL,0,0,0,1),
                    (46,'700','Depreciation',NULL,0,10,'',NULL,0,0,1,1),
                    (47,'710','Income Tax Expense',NULL,0,12,'',NULL,0,0,0,1),
                    (48,'715','Employee Benefits Expense',NULL,0,12,'',NULL,0,0,0,1),
                    (49,'800','Interest Expense',NULL,0,12,'',NULL,0,0,0,1),
                    (50,'810','Bank Revaluations',NULL,0,12,'',NULL,0,0,1,1),
                    (51,'815','Unrealized Currency Gains',NULL,0,12,'',NULL,0,0,1,1),
                    (52,'820','Realized Currency Gains',NULL,0,12,'',NULL,0,0,1,1),
                    (53,'825','Sales Discount',NULL,0,12,'',NULL,0,0,1,1),
                    (54,'400','Sales',NULL,0,13,'',NULL,0,0,0,1),
                    (55,'460','Interest Income',NULL,0,13,'',NULL,0,0,0,1),
                    (56,'470','Other Revenue',NULL,0,13,'',NULL,0,0,0,1),
                    (57,'475','Purchase Discount',NULL,0,13,'',NULL,0,0,1,1),
                    (58,'300','Owners Contribution',NULL,0,16,'',NULL,0,0,0,1),
                    (59,'310','Owners Draw',NULL,0,16,'',NULL,0,0,0,1),
                    (60,'320','Retained Earnings',NULL,0,16,'',NULL,0,0,1,1),
                    (61,'330','Common Stock',NULL,0,16,'',NULL,0,0,0,1),
                    (62,'092','Savings Account',NULL,0,6,'',NULL,1,1,0,1);";

        $wpdb->query( $sql );
    }

    // check if banks exists
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_ac_banks` LIMIT 0, 1" ) ) {
        $sql = "INSERT INTO `{$wpdb->prefix}erp_ac_banks` (`id`, `ledger_id`, `account_number`, `bank_name`)
                VALUES  (1,7,'',''), (2,62,'012345689','ABC Bank');";

        $wpdb->query( $sql );
    }
}

/**
 * Update existing tables
 *
 * @package WPERP/Accounting
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ac_table_update() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_ac_transactions';
    $cols = $wpdb->get_col( "DESC " . $table );

    if ( ! in_array( 'sub_total', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table ADD `sub_total` DECIMAL(13,4) NOT NULL AFTER `conversion_rate`" );
    }

    $ledger = $wpdb->prefix . 'erp_ac_ledger';

    $cols = $wpdb->get_col( "DESC " . $ledger );

    if ( ! in_array( 'created_by', $cols ) ) {
        $wpdb->query( "ALTER TABLE $ledger ADD `created_by` bigint(20) NOT NULL AFTER `active`" );
    }

    $item_table = $wpdb->prefix . 'erp_ac_transaction_items';
    $item_cols  = $wpdb->get_col( "DESC " . $item_table );

    if ( ! in_array( 'tax_rate',$item_cols ) ) {
        $wpdb->query( "ALTER TABLE $item_table ADD `tax_rate` DECIMAL(13,4) NOT NULL AFTER `tax`" );
    }

    if ( ! in_array( 'type', $item_cols ) ) {
        $wpdb->query( "ALTER TABLE $item_table ADD `type` varchar(20) DEFAULT NULL AFTER `product_id`" );
    }

    if ( ! in_array( 'tax_journal', $item_cols ) ) {
        $wpdb->query( "ALTER TABLE $item_table ADD `tax_journal` BIGINT(20) NOT NULL AFTER `tax_rate`" );
    }

    $account_table = $wpdb->prefix . 'erp_ac_banks';

    $wpdb->update( $account_table, array( 'ledger_id' => 62 ), array( 'id' => 2, 'ledger_id' => 60 ), array( '%d' ), array( '%d', '%d' ) );
}

/**
 * Update manager capabilities
 *
 * @package WPERP/Accounting
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ac_update_manager_capabilities() {
  remove_role( 'erp_ac_manager' );

	$installer = new \WeDevs_ERP_Installer();
	$installer->create_roles();
	$installer->set_role();
}

/**
 * Update existing tables
 *
 * @package WPERP/CRM
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_crm_update_table_column() {
    global $wpdb;

    $save_search_tb     = $wpdb->prefix . 'erp_crm_save_search';
    $people_tb          = $wpdb->prefix . 'erp_peoples';
    $save_search_tb_col = $wpdb->get_col( "DESC " . $save_search_tb );
    $people_tb_col      = $wpdb->get_col( "DESC " . $people_tb );

    if ( ! in_array( 'type', $save_search_tb_col ) ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_crm_save_search ADD `type` VARCHAR(255) AFTER `id`" );
    }

    if ( ! in_array( 'created_at', $save_search_tb_col ) ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_crm_save_search ADD `created_at` DATETIME DEFAULT NULL" );
    }

    if ( ! in_array( 'updated_at', $save_search_tb_col ) ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_crm_save_search ADD `updated_at` DATETIME DEFAULT NULL" );
    }

    if ( ! in_array( 'created_by', $people_tb_col ) ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_peoples ADD `created_by` BIGINT(20) AFTER `currency`" );
    }
}

/**
 * Update some data for newly added column
 *
 * @package WPERP/Accounting
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_crm_update_column_data() {
    global $wpdb;

    $super_user_email = get_option('admin_email');
    $user_info        = get_user_by('email', $super_user_email );

    $wpdb->query( "UPDATE {$wpdb->prefix}erp_crm_save_search SET `type`='contact' WHERE `search_val` LIKE '%first_name%' OR `search_val` LIKE '%last_name%'" );
    $wpdb->query( "UPDATE {$wpdb->prefix}erp_crm_save_search SET `type`='company' WHERE `search_val` LIKE '%company%'" );

    if ( ! $user_info ) {
        return;
    }
    $user_id = $user_info->ID;
    $wpdb->query( "UPDATE {$wpdb->prefix}erp_peoples SET `created_by`='$user_id'" );
}

/**
 * Set active module
 *
 * @package WPERP/Accounting
 *
 * @since 1.1.0
 *
 * @return void
 */
function erp_ac_active_module() {
    $module = get_option( 'erp_modules' );

    if ( ! array_key_exists( 'accounting', $module ) ) {
        $module['accounting'] = [
            'title'       => __( 'Accountig Management', 'erp' ),
            'slug'        => 'erp-accounting',
            'description' => __( 'Accountig Management', 'erp' ),
            'callback'    => '\WeDevs\ERP\Accounting\Accountig',
            'modules'     => apply_filters( 'erp_accounting_modules', [ ] )
        ];
    }

    update_option( 'erp_modules', $module );
}


// Update all accounting releated function
erp_ac_create_table();
erp_ac_populate_data();
erp_ac_table_update();
erp_ac_active_module();
erp_ac_update_manager_capabilities();

// Update all CRM related function
erp_crm_update_table_column();
erp_crm_update_column_data();
