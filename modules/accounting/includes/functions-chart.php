<?php

/**
 * Get all chart classes
 *
 * @return array
 */
function erp_ac_get_chart_classes() {
    $classes = [
        1 => __( 'Assets', 'erp' ),
        2 => __( 'Liabilities', 'erp' ),
        3 => __( 'Expenses', 'erp' ),
        4 => __( 'Income', 'erp' ),
        5 => __( 'Equity', 'erp' ),
    ];

    return $classes;
}

/**
 * Get all chart
 *
 * @param $args array
 *
 * @return array
 */
function erp_ac_get_all_chart( $args = [] ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
        'class_id'   => '-1'
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-ac-chart-all-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $condition = '';
        $limit = '';

        if ( $args['number'] != '-1' && ! empty( $args['number'] ) ) {
            $limit = "LIMIT {$args['offset']}, {$args['number']}";
        }

        if ( $args['class_id'] != '-1' && ! empty( $args['class_id'] ) ) {
            $class_id = $args['class_id'];
            $condition .= "WHERE ct.class_id = $class_id";
        }

        $sql = "SELECT ch.*, ct.class_id, ct.name as type_name, count(jour.ledger_id) as entries
            FROM {$wpdb->prefix}erp_ac_ledger AS ch
            LEFT JOIN {$wpdb->prefix}erp_ac_chart_types AS ct ON ct.id = ch.type_id
            LEFT JOIN {$wpdb->prefix}erp_ac_journals as jour ON jour.ledger_id = ch.id
            $condition
            GROUP BY ch.id
            ORDER BY {$args['orderby']} {$args['order']}
            $limit";

        $items = $wpdb->get_results( $sql );

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

function erp_ac_get_bank_account() {
    return WeDevs\ERP\Accounting\Model\Ledger::bank()->with( ['bank_details'] )->get()->toArray();
}

function erp_ac_get_individual_bank_balance( $bank_id ) {
    $get_journals = \WeDevs\ERP\Accounting\Model\Journal::ofledger( $bank_id )->get()->toArray();
    $debit        = array_sum( wp_list_pluck( $get_journals, 'debit' ) );
    $credit       = array_sum( wp_list_pluck( $get_journals, 'credit' ) );
    $total_amount = $debit - $credit;

    return erp_ac_get_price( $total_amount );
}


/**
 * Fetch all chart from database
 *
 * @return array
 */
function erp_ac_get_chart_count() {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'erp_ac_ledger' );
}

/**
 * Fetch a single chart from database
 *
 * @param int   $id
 *
 * @return array
 */
function erp_ac_get_chart( $id = 0 ) {
    return \WeDevs\ERP\Accounting\Model\Ledger::with('bank_details')->find( $id );
}

/**
 * Insert a new chart
 *
 * @param array $args
 */
function erp_ac_insert_chart( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'          => null,
        'name'        => '',
        'description' => '',
        'type_id'     => '',
        'active'      => 1,
        'parent'      => 0,
        'system'      => 0,
        'created_by'  => get_current_user_id()
     );

    $args       = wp_parse_args( $args, $defaults );

    $table_name = $wpdb->prefix . 'erp_ac_ledger';

    // some basic validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No Name provided.', 'erp' ) );
    }

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $row_id ) {
        if ( ! erp_ac_create_account() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }

        $ledger = WeDevs\ERP\Accounting\Model\Ledger::create( $args );

        if ( $ledger->id ) {
            return $ledger->id;
        }

    } else {

        if ( ! erp_ac_edit_account() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }

        // don't allow to change account type
        unset( $args['type_id'] );

        // do update method here
        if ( $wpdb->update( $table_name, $args, array( 'id' => $row_id ) ) ) {

            return $row_id;
        }
    }

    return false;
}

/**
 * Get all chart types
 *
 * @param $args array
 *
 * @return array
 */
function erp_ac_get_all_chart_types() {
    global $wpdb;

    $cache_key = 'erp-ac-chart-type-all';
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'erp_ac_chart_types ORDER BY class_id ASC' );

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * Get all chart types by class id
 *
 * @param int $class_id
 *
 * @return array
 */
function erp_ac_get_chart_types_by_class_id( $class_id ) {
    global $wpdb;

    $cache_key = 'erp-ac-chart-type-by-class-id';
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'erp_ac_chart_types WHERE class_id = '. $class_id .' ORDER BY class_id ASC' );

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * Get chart type as array
 *
 * @return array
 */
function erp_ac_get_all_chart_types_array() {
    $classes   = erp_ac_get_chart_classes();
    $all_types = erp_ac_get_all_chart_types();
    $types     = [];

    foreach ($all_types as $type) {
        $types[ $type->class_id ][ $type->id ] = $type->name;
    }

    return $types;
}


function erp_ac_get_charts() {
    $raw = [
        1 => [],
        2 => [],
        3 => [],
        4 => [],
        5 => [],
    ];

    $cache_key = 'erp_account_charts';
    $accounts  = wp_cache_get( $cache_key );

    if ( false === $accounts ) {
        $accounts = \WeDevs\ERP\Accounting\Model\Chart_Of_Accounts::all()->toArray();
        wp_cache_set( $cache_key, $accounts );
    }

    // group by account_type_id
    foreach ($accounts as $ac) {
        $raw[ $ac['type_id'] ][] = $ac;
    }

    return $raw;
}

function erp_ac_get_bank_accounts() {
    return WeDevs\ERP\Accounting\Model\Ledger::bank()->active()->get()->toArray();
}

function erp_ac_get_bank_dropdown() {
    $accounts = [];
    $banks    = erp_ac_get_bank_accounts();

    if ( $banks ) {
        foreach ($banks as $bank) {
            $accounts[ $bank['id'] ] = sprintf( '%s - %s', $bank['code'], $bank['name'] );
        }
    }

    return $accounts;
}

function erp_ac_get_chart_dropdown( $args = [] ) {
    $account_charts = [];
    $defaults       = [
        'select_text' => __( '&#8212; Select Account &#8212;', 'textdomain' ),
        'selected'    => '0',
        'name'        => 'chart-of-accounts',
        'exclude'     => false,
        'class'       => 'erp-select2',
        'required'    => false,
    ];
    $args         = wp_parse_args( $args, $defaults );

    global $wpdb;

    $cache_key = 'erp-account-ledgers-list';
    $ledgers = wp_cache_get( $cache_key, 'erp' );

    if ( false === $ledgers ) {
        $sql = "SELECT led.id, led.code, led.name, class.name as class_name, class.id as class_id from {$wpdb->prefix}erp_ac_ledger as led
            LEFT JOIN {$wpdb->prefix}erp_ac_chart_types as types on led.type_id = types.id
            LEFT JOIN {$wpdb->prefix}erp_ac_chart_classes as class on class.id = types.class_id
            ORDER BY led.id ASC";

        $ledgers = $wpdb->get_results( $sql );
        wp_cache_set( $cache_key, $ledgers, 'erp' );
    }

    // build the array
    if ( $ledgers ) {
        foreach ($ledgers as $ledger) {
            if ( ! isset( $account_charts[ $ledger->class_id ] ) ) {
                $account_charts[ $ledger->class_id ]['label'] = $ledger->class_name;
                $account_charts[ $ledger->class_id ]['options'][] = $ledger;
            } else {
                $account_charts[ $ledger->class_id ]['options'][] = $ledger;
            }

        }
    }

    // exclude charts
    if ( is_array( $args['exclude'] ) && $args['exclude'] ) {
        foreach ($args['exclude'] as $class_id) {
            if ( array_key_exists( $class_id, $account_charts ) ) {
                unset( $account_charts[ $class_id ] );
            }
        }
    }

    return $account_charts;
}

function erp_ac_render_account_dropdown_html( $account_charts = [], $args = [] ) {

    $defaults       = [
        'select_text' => __( '&#8212; Select &#8212;', 'erp' ),
        'selected'    => '0',
        'name'        => 'chart-of-accounts',
        'class'       => 'erp-select2',
        'required'    => true,
    ];
    $args         = wp_parse_args( $args, $defaults );

    $dropdown = sprintf( '<select name="%1$s" id="%1$s" class="%2$s"%3$s>', $args['name'], $args['class'], $args['required'] == true ? ' required="required"' : '' );

    $dropdown .= '<option value="">' . $args['select_text'] . '</option>';

    if ( $account_charts ) {

        foreach( $account_charts as $chart ) {
            $dropdown .= '<optgroup label="' . $chart['label'] . '">';
            foreach ($chart['options'] as $ledger) {
                $dropdown .= sprintf( '<option value="%s" %s>%s - %s</option>', $ledger->id, selected( $args['selected'], $ledger->id, false ), $ledger->code, $ledger->name );
            }
            $dropdown .= '</optgroup>';
        }
    }

    $dropdown .= '</select>';

    return $dropdown;
}

function erp_ac_get_journals_by_class_id( $id ) {
    global $wpdb;

    $expense      = new \WeDevs\ERP\Accounting\Model\Ledger();
    $type_table   = $wpdb->prefix . 'erp_ac_chart_types';
    $class_table  = $wpdb->prefix . 'erp_ac_chart_classes';
    $ledger_table = $wpdb->prefix . 'erp_ac_ledger';
    $journal_table = $wpdb->prefix . 'erp_ac_journals';

    global $wpdb;

    $cache_key = 'erp_ac_get_journals_by_class' . $id;
    //$items     = get_transient( $cache_key );

    //if ( false === $items ) {
        $sql = "SELECT led.id, led.code, led.name, led.type_id, types.name as type_name, types.class_id,
        class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit, jour.type as jour_type
            FROM $ledger_table as led
            LEFT JOIN $type_table as types ON types.id = led.type_id
            LEFT JOIN $class_table as class ON class.id = types.class_id
            LEFT JOIN $journal_table as jour ON jour.ledger_id = led.id
            WHERE class.id = $id
            GROUP BY led.id";

        $items = $wpdb->get_results( $sql );
        //set_transient( $cache_key, $items, 12 * HOUR_IN_SECONDS );
   // }

    return $items;
}

function erp_ac_get_ledger_by_class_id( $id ) {
    global $wpdb;

    $expense      = new \WeDevs\ERP\Accounting\Model\Ledger();
    $type_table   = $wpdb->prefix . 'erp_ac_chart_types';
    $class_table  = $wpdb->prefix . 'erp_ac_chart_classes';
    $ledger_table = $wpdb->prefix . 'erp_ac_ledger';

    global $wpdb;

    $cache_key = 'erp_ac_get_ledger_by_class' . $id;
    $items     = get_transient( $cache_key );

    if ( false === $items ) {
        $sql = "SELECT led.id, led.code, led.name, led.type_id, types.name as type_name, types.class_id, class.name as class_name
            FROM $ledger_table as led
            LEFT JOIN $type_table as types ON types.id = led.type_id
            LEFT JOIN $class_table as class ON class.id = types.class_id
            WHERE class.id = $id
            GROUP BY led.id";

        $items = $wpdb->get_results( $sql );
        set_transient( $cache_key, $items, 12 * HOUR_IN_SECONDS );
    }

    return $items;
}

function erp_ac_get_bank_journals() {
    return \WeDevs\ERP\Accounting\Model\Ledger::bank()->with('journals')->get()->toArray();
}

/**
 * Print the chart table
 *
 * @param  string  $title
 * @param  array   $charts
 *
 * @return void
 */
function erp_ac_chart_print_table( $title, $charts = [] ) {
    $edit_url = admin_url( 'admin.php?page=erp-accounting-charts&action=edit&id=' );

    include dirname( __FILE__ ) . '/views/accounts/chart-table.php';
}

function erp_ac_bank_journal( $bank_id ) {
    return \WeDevs\ERP\Accounting\Model\Journal::where( 'ledger_id', $bank_id )->get()->toArray();
}

function erp_ac_bank_credit_total_amount( $bank_id ) {
    $db = new \WeDevs\ORM\Eloquent\Database();
    $dc = [];

    $dc['debit'] =  \WeDevs\ERP\Accounting\Model\Journal::select( array( $db->raw( 'SUM(debit) as debit_sum') ) )->where( 'ledger_id', $bank_id )->pluck('debit_sum');

    $dc['credit'] =  \WeDevs\ERP\Accounting\Model\Journal::select( array( $db->raw( 'SUM(credit) as credit_sum') ) )->where( 'ledger_id', $bank_id )->pluck('credit_sum');

    return $dc;
}

function erp_ac_exclude_chart( $dropdown = [], $accounts_id = [] ) {
    foreach ( $dropdown as $key => $account ) {
        foreach ( $account['options'] as $key_opt => $acc_opt ) {
            if ( in_array( $acc_opt->id, $accounts_id ) ) {
                unset( $dropdown[$key]['options'][$key_opt] );
            }
        }
    }

    return $dropdown;
}

// Inside customer included Assets and Expenses
// Inside vendor included Liabilities and Equity
function chart_grouping() {
    $chart_classes =  \WeDevs\ERP\Accounting\Model\Chart_Classes::with(['types'])->get()->toArray();

    // Inside customer included Assets and Expenses
    // Inside vendor included Liabilities and Equity
    $group['customer'] = [];
    $group['vendor'] = [];

    foreach ( $chart_classes as $key => $chart_classe ) {
        if ( $chart_classe['id'] == 1 || $chart_classe['id'] == 3 ) {
            $group['customer'] = array_merge( $group['customer'], wp_list_pluck( $chart_classe['types'], 'id' ) );
        }

        if ( $chart_classe['id'] == 2 || $chart_classe['id'] == 5 ) {
            $group['vendor'] = array_merge( $group['vendor'], wp_list_pluck( $chart_classe['types'], 'id' ) );
        }
    }

    return $group;
}

function erp_ac_delete_chart( $chart_id ) {

    if ( ! erp_ac_delete_account() ) {
        return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
    }

    $chart = erp_ac_bank_journal( $chart_id );

    if ( $chart ) {
        return new WP_Error( 'error', __('The account record can not be deleted as it contains one or more transactions.', 'erp') );
    }

    $delete = \WeDevs\ERP\Accounting\Model\Chart_Of_Accounts::find( $chart_id )->delete();

    if ( $delete ) {
        return true;
    }

    return new WP_Error( 'error', __('Unexpected error!', 'erp') );
}






