<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Upload attachments
 *
 * @return array
 */
function erp_acct_upload_attachments( $files ) {
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    $attachments = [];
    $movefiles   = [];

    // Formatting request for upload
    for ( $i = 0; $i < count( $files['name'] ); $i++ ) {
        $attachments[] = [
            'name'     => wp_unslash( $files['name'][ $i ] ),
            'type'     => $files['type'][ $i ],
            'tmp_name' => wp_unslash( $files['tmp_name'][ $i ] ),
            'error'    => $files['error'][ $i ],
            'size'     => $files['size'][ $i ],
        ];
    }

    foreach ( $attachments as $attachment ) {
        $movefiles[] = wp_handle_upload( $attachment, [ 'test_form' => false ] );
    }

    return $movefiles;
}

/**
 * Get payable data for given month
 *
 * @param $from
 * @param $to
 *
 * @return array|null|object
 */
function erp_acct_get_payables( $from, $to ) {
    global $wpdb;

    $from_date = date( 'Y-m-d', strtotime( $from ) );
    $to_date   = date( 'Y-m-d', strtotime( $to ) );

    $purchases             = $wpdb->prefix . 'erp_acct_purchase';
    $purchase_acct_details = $wpdb->prefix . 'erp_acct_purchase_account_details';

    $purchase_query = $wpdb->prepare(
        "Select voucher_no, SUM(ad.debit - ad.credit) as due, due_date
        FROM $purchases LEFT JOIN $purchase_acct_details as ad
        ON ad.purchase_no = voucher_no  where due_date
        BETWEEN %s and %s Group BY voucher_no Having due < 0 ",
        $from_date,
        $to_date
    );

    $purchase_results = $wpdb->get_results( $purchase_query, ARRAY_A );

    $bills             = $wpdb->prefix . 'erp_acct_bills';
    $bill_acct_details = $wpdb->prefix . 'erp_acct_bill_account_details';
    $bills_query       = $wpdb->prepare(
        "Select voucher_no, SUM(ad.debit - ad.credit) as due, due_date
        FROM $bills LEFT JOIN $bill_acct_details as ad
        ON ad.bill_no = voucher_no  where due_date
        BETWEEN %s and %s Group BY voucher_no Having due < 0",
        $from_date,
        $to_date
    );

    $bill_results = $wpdb->get_results( $bills_query, ARRAY_A );

    if ( ! empty( $purchase_results ) && ! empty( $bill_results ) ) {
        return array_merge( $bill_results, $purchase_results );
    }

    if ( empty( $bill_results ) ) {
        return $purchase_results;
    }

    if ( empty( $purchase_results ) ) {
        return $bill_results;
    }

}

/**
 * Get Payable overview data
 *
 * @return array
 */
function erp_acct_get_payables_overview() {
    // get dates till coming 90 days
    $from_date = date( 'Y-m-d' );
    $to_date   = date( 'Y-m-d', strtotime( '+90 day', strtotime( $from_date ) ) );

    $data   = [];
    $amount = [
        'first'  => 0,
        'second' => 0,
        'third'  => 0,
    ];

    $result = erp_acct_get_payables( $from_date, $to_date );

    if ( ! empty( $result ) ) {
        $from_date = new DateTime( $from_date );

        foreach ( $result as $item_data ) {
            $item  = (object) $item_data;
            $later = new DateTime( $item->due_date );
            $diff  = $later->diff( $from_date )->format( '%a' );

            //segment by date difference
            switch ( $diff ) {
                case ( 0 === $diff ):
                    $data['first'][] = $item_data;
                    $amount['first'] = $amount['first'] + abs( $item->due );
                    break;

                case ( $diff <= 30 ):
                    $data['first'][] = $item_data;
                    $amount['first'] = $amount['first'] + abs( $item->due );
                    break;

                case ( $diff <= 60 ):
                    $data['second'][] = $item_data;
                    $amount['second'] = $amount['second'] + abs( $item->due );
                    break;

                case ( $diff <= 90 ):
                    $data['third'][] = $item_data;
                    $amount['third'] = $amount['third'] + abs( $item->due );
                    break;

                default:
            }
        }
    }

    return [
		'data'   => $data,
		'amount' => $amount,
	];
}

/**
 * Insert check data
 *
 * @param array $check_data
 *
 * @return void
 */
function erp_acct_insert_check_data( $check_data ) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_expense_checks',
        array(
			'trn_no'       => $check_data['voucher_no'],
			'check_no'     => $check_data['check_no'],
			'voucher_type' => $check_data['voucher_type'],
			'amount'       => $check_data['amount'],
			'bank'         => $check_data['bank'],
			'name'         => $check_data['name'],
			'pay_to'       => $check_data['pay_to'],
			'created_at'   => $check_data['created_at'],
			'created_by'   => $check_data['created_by'],
			'updated_at'   => $check_data['updated_at'],
			'updated_by'   => $check_data['updated_by'],
        )
    );
}

/**
 * Update check data
 *
 * @param array $check_data
 * @param $check_no
 *
 * @return void
 */
function erp_acct_update_check_data( $check_data, $check_no ) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_expense_checks',
        array(
			'trn_no'       => $check_data['voucher_no'],
			'voucher_type' => $check_data['voucher_type'],
			'amount'       => $check_data['amount'],
			'bank'         => $check_data['bank'],
			'name'         => $check_data['name'],
			'pay_to'       => $check_data['pay_to'],
			'created_at'   => $check_data['created_at'],
			'created_by'   => $check_data['created_by'],
			'updated_at'   => $check_data['updated_at'],
			'updated_by'   => $check_data['updated_by'],
        ),
        array(
			'check_no' => $check_no,
        )
    );
}

/**
 * Get people name, email by id
 *
 * @param $people_id
 *
 * @return array
 */
function erp_acct_get_people_info_by_id( $people_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT first_name, last_name, email FROM {$wpdb->prefix}erp_peoples WHERE id = %d LIMIT 1", $people_id ) );

    return $row;
}

/**
 * Get ledger name, slug by id
 *
 * @param $ledger_id
 *
 * @return array
 */
function erp_acct_get_ledger_by_id( $ledger_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name, slug, code FROM {$wpdb->prefix}erp_acct_ledgers WHERE id = %d LIMIT 1", $ledger_id ) );

    return $row;
}

/**
 * Get product type by id
 *
 * @param $product_type_id
 *
 * @return array
 */
function erp_acct_get_product_type_by_id( $product_type_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_product_types WHERE id = %d LIMIT 1", $product_type_id ) );

    return $row;
}

/**
 * Get product category by id
 *
 * @param $cat_id
 *
 * @return array
 */
function erp_acct_get_product_category_by_id( $cat_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_product_categories WHERE id = %d LIMIT 1", $cat_id ) );

    return $row;
}

/**
 * Get tax agency name by id
 *
 * @param $agency_id
 *
 * @return array
 */
function erp_acct_get_tax_agency_by_id( $agency_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_tax_agencies WHERE id = %d LIMIT 1", $agency_id ) );

    return $row->name;
}

/**
 * Get tax category by id
 *
 * @param $cat_id
 *
 * @return array
 */
function erp_acct_get_tax_category_by_id( $cat_id ) {
    global $wpdb;

    if ( null !== $cat_id ) {
        return $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_tax_categories WHERE id = %d", $cat_id ) );
    }

    return '';
}

/**
 * Get transaction status by id
 *
 * @param $trn_id
 *
 * @return string
 */
function erp_acct_get_trn_status_by_id( $trn_id ) {
    global $wpdb;

    if ( ! $trn_id ) {
        return 'pending';
    }

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT type_name FROM {$wpdb->prefix}erp_acct_trn_status_types WHERE id = %d", $trn_id ) );

    return ucfirst( str_replace( '_', ' ', $row->type_name ) );
}

/**
 * Get payment method by id
 *
 * @param $trn_id
 *
 * @return array
 */
function erp_acct_get_payment_method_by_id( $method_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_payment_methods WHERE id = %d LIMIT 1", $method_id ) );

    return $row;
}

/**
 * Get payment method by id
 *
 * @param $trn_id
 *
 * @return string
 */
function erp_acct_get_payment_method_name_by_id( $method_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_payment_methods WHERE id = %d LIMIT 1", $method_id ) );

    return $row->name;
}

/**
 * Get check transaction type by id
 *
 * @param $trn_id
 *
 * @return array
 */
function erp_acct_get_check_trn_type_by_id( $trn_type_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_check_trn_tables WHERE id = %d LIMIT 1", $trn_type_id ) );

    return $row;
}


/**
 * Get Accounting Quick Access Menus
 *
 * @return array
 */
function erp_acct_quick_access_menu() {
    $menus = [
        'invoice'         => [
            'title' => 'Invoice',
            'slug'  => 'invoice',
            'url'   => 'invoices/new',
        ],
        'estimate'        => [
            'title' => 'Estimate',
            'slug'  => 'estimate',
            'url'   => 'estimates/new',
        ],
        'rec_payment'     => [
            'title' => 'Receive Payment',
            'slug'  => 'payment',
            'url'   => 'payments/new',
        ],
        'bill'            => [
            'title' => 'Bill',
            'slug'  => 'bill',
            'url'   => 'bills/new',
        ],
        'pay_bill'        => [
            'title' => 'Pay Bill',
            'slug'  => 'pay_bill',
            'url'   => 'pay-bills/new',
        ],
        'purchase-order'  => [
            'title' => 'Purchase Order',
            'slug'  => 'purchase-orders',
            'url'   => 'purchase-orders/new',
        ],
        'purchase'        => [
            'title' => 'Purchase',
            'slug'  => 'purchase',
            'url'   => 'purchases/new',
        ],
        'pay_purchase'    => [
            'title' => 'Pay Purchase',
            'slug'  => 'pay_purchase',
            'url'   => 'pay-purchases/new',
        ],
        'expense'         => [
            'title' => 'Expense',
            'slug'  => 'expense',
            'url'   => 'expenses/new',
        ],
        'check'           => [
            'title' => 'Check',
            'slug'  => 'check',
            'url'   => 'checks/new',
        ],
        'journal'         => [
            'title' => 'Journal',
            'slug'  => 'journal',
            'url'   => 'transactions/journals/new',
        ],
        'tax_rate'        => [
            'title' => 'Tax Payment',
            'slug'  => 'pay_tax',
            'url'   => 'pay-tax',
        ],
        'opening_balance' => [
            'title' => __( 'Opening Balance', 'erp' ),
            'slug'  => 'opening_balance',
            'url'   => 'opening-balance',
        ],
    ];

    return apply_filters( 'erp_acct_quick_menu', $menus );
}

/**
 * Change a string to slug
 */
function slugify( $str ) {
    // replace non letter or digits by _
    $str = preg_replace( '~[^\pL\d]+~u', '_', $str );

    return strtolower( $str );
}


/**
 * Check voucher edit state
 *
 * @param int $id
 * @return bool
 */
function erp_acct_check_voucher_edit_state( $id ) {
    global $wpdb;

    $res = $wpdb->get_var( $wpdb->prepare( "SELECT editable FROM {$wpdb->prefix}erp_acct_voucher_no WHERE id = %d", $id ) );

    return ! empty( $res ) ? true : false;
}

/**
 * Check if people exists (customer/vendor)
 *
 * @param string $email
 * @return bool
 */
function erp_acct_check_people_exists( $email ) {
    $people = erp_get_people_by( 'email', $email );

    // this $email belongs to nobody
    if ( ! $people ) {
        return false;
    }

    if ( in_array( 'customer', $people->types, true ) || in_array( 'vendor', $people->types, true ) ) {
        return true;
    }

    return false;
}

/**
 * Get transaction id by status slug
 *
 * @param string $slug
 * @return int
 */
function erp_acct_trn_status_by_id( $slug ) {
    global $wpdb;

    return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_trn_status_types WHERE slug = %s", $slug ) );
}

/**
 * Get all transaction statuses
 *
 * @return array
 */
function erp_acct_get_all_trn_statuses() {
    global $wpdb;

    return $wpdb->get_results( "SELECT id,type_name as name, slug FROM {$wpdb->prefix}erp_acct_trn_status_types", ARRAY_A );
}

// accounting customer auto create
add_action( 'erp_crm_save_contact_data', 'erp_acct_customer_create_from_crm', 10, 3 );
add_action( 'erp_crm_contact_created', 'erp_acct_customer_auto_create_from_crm', 10, 2 );

/**
 * Auto create customer when creating CRM contact/company
 *
 * @param  $customer
 * @param  $customer_id
 * @param  $data
 *
 * @since  1.2.7
 *
 */
function erp_acct_customer_create_from_crm( $customer, $customer_id, $data ) {

    $customer_auto_import = (int) erp_get_option( 'customer_auto_import', false, 0 );
    $crm_user_type        = erp_get_option( 'crm_user_type', false, [] ); // Contact or Company

    if ( ! $customer_auto_import ) {
        return;
    }

    if ( ! count( $crm_user_type ) ) {
        return;
    }

    // no need to add wordpress `user id` again
    // [ `user id` already added when contact is created ]
    $data['is_wp_user'] = false;
    $data['wp_user_id'] = '';

    if ( ! isset( $data['company'] ) ) {
        // the created crm user type is NOT a company
        if ( ! in_array( 'contact', $crm_user_type ) ) {
            return;
        }
    } else {
        if ( ! in_array( 'company', $crm_user_type ) ) {
            return;
        }
    }

    $data['people_id'] = $customer_id;
    $data['type']      = 'customer';

    erp_convert_to_people( $data );
}

/**
 * Accounting customer auto create from crm
 *
 * @since  1.2.8
 *
 * @param  int $contact_id
 * @param  object $data
 *
 */
function erp_acct_customer_auto_create_from_crm( $contact_id, $data ) {
    erp_acct_customer_create_from_crm( [], $contact_id, $data );
}

