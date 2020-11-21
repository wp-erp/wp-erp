<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get an single invoice
 *
 * @param $invoice_no
 *
 * @return mixed
 */
function erp_acct_get_invoice_for_return( $invoice_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "Select
    voucher.editable,
    voucher.currency,
    invoice.id,
    invoice.voucher_no,
    invoice.customer_id,
    invoice.customer_name,
    invoice.trn_date,
    invoice.due_date,
    invoice.billing_address,
    invoice.amount,
    invoice.discount,
    invoice.discount_type,
    invoice.tax,
    invoice.estimate,
    invoice.attachments,
    invoice.status,
    invoice.particulars,
    invoice.created_at,
    inv_acc_detail.debit,
    inv_acc_detail.credit,
    sales_return.amount as return_amount,
    sales_return.discount as return_discount,
    sales_return.tax as return_tax,
    sales_return.reason as return_reason,
    sales_return.comments as return_comments,
    sales_return.trn_date as return_trn_date

    FROM {$wpdb->prefix}erp_acct_invoices as invoice
    LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON invoice.voucher_no = voucher.id
    LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details as inv_acc_detail ON invoice.voucher_no = inv_acc_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_sales_return as sales_return ON invoice.id = sales_return.invoice_id
    WHERE invoice.voucher_no = %d",
        $invoice_no
    );

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['line_items']  = erp_acct_format_invoice_line_items_for_return( $invoice_no );
    $row['tax_rate_id'] = erp_acct_get_default_tax_rate_name_id();

    // calculate every line total
    foreach ( $row['line_items'] as $key => $value ) {
        $total                                   = ( $value['item_total'] + $value['tax'] ) - $value['discount'];
        $row['line_items'][ $key ]['line_total'] = $total;
    }

    $row['attachments'] = unserialize( $row['attachments'] );
    $row['total_due']   = erp_acct_get_invoice_due( $invoice_no );

    return $row;
}



/**
 * Get formatted line items
 */
function erp_acct_format_invoice_line_items_for_return( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
        inv_detail.id as invoice_details_id,
        inv_detail.product_id,
        inv_detail.qty,
        inv_detail.unit_price,
        inv_detail.discount,
        inv_detail.tax,
        inv_detail.item_total,
        inv_detail.ecommerce_type,
        SUM(inv_detail_tax.tax_rate) as tax_rate,
        product.name,
        product.product_type_id,
        product.category_id,
        product.vendor,
        product.cost_price,
        product.sale_price,
        product.tax_cat_id,
        return_inv_detail.qty as return_qty,
        return_inv_detail.unit_price as return_unit_price,
        return_inv_detail.discount as return_discount,
        return_inv_detail.tax as return_tax

        FROM  {$wpdb->prefix}erp_acct_invoice_details as inv_detail
        LEFT JOIN {$wpdb->prefix}erp_acct_sales_return_details as return_inv_detail ON inv_detail.id = return_inv_detail.invoice_details_id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_details_tax as inv_detail_tax ON inv_detail.id = inv_detail_tax.invoice_details_id
        LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
        WHERE inv_detail.trn_no = %d GROUP BY inv_detail.id",
        $voucher_no
    );

    $results = $wpdb->get_results( $sql, ARRAY_A );

    if ( ! empty( reset( $results )['ecommerce_type'] ) ) {
        // product name should not fetch form `erp_acct_products`
        $results = array_map(
            function ( $result ) {
                $result['name'] = get_the_title( $result['product_id'] );

                return $result;
            },
            $results
        );
    }

    return $results;
}





/**
 * Insert invoice data
 *
 * @param $data
 *
 * @return string
 */
function erp_acct_insert_sales_return( $data ) {
    global $wpdb;


    $user_id = get_current_user_id();

    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $user_id;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $user_id;

    $voucher_no    = null;
    $currency      = erp_get_currency( true );

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            [
                'type'       => 'sales_return',
                'currency'   => $currency,
                'editable'   => 1,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
            ]
        );

        $voucher_no = $wpdb->insert_id;

        $invoice_data = erp_acct_get_formatted_sales_return_data( $data, $voucher_no );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_sales_return',
            [
                'invoice_id'      => $invoice_data['voucher_no'],
                'voucher_no'      => $invoice_data['voucher_no'],
                'customer_id'     => $invoice_data['customer_id'],
                'customer_name'   => $invoice_data['customer_name'],
                'trn_date'        => $invoice_data['return_date'],
                'amount'          => $invoice_data['amount'],
                'discount'        => $invoice_data['discount'],
                'discount_type'   => $invoice_data['discount_type'],
                'tax'             => $invoice_data['tax'],
                'reason'          => $invoice_data['return_reason'],
                'status'          => $invoice_data['status'],
                'created_at'      => $invoice_data['created_at'],
                'created_by'      => $invoice_data['created_by'],
            ]
        );

        foreach( $invoice_data['line_items'] as $item ){

            $totalTax = (float)$item['tax'] * (int)$item['qty'] ;
            $totalDiscount = (float)$item['discount'] * (int)$item['qty'];

            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_sales_return_details',
                [
                    'invoice_details_id' => $item['invoice_details_id'],
                    'trn_no'             => $voucher_no,
                    'product_id'         => $item['product_id'],
                    'qty'                => $item['qty'],
                    'unit_price'         => $item['unit_price'],
                    'discount'           => $totalDiscount,
                    'tax'                => $totalTax,
                    'item_total'         => ( ( $item['unit_price'] * $item['qty'] ) + $totalTax ) - $totalDiscount,
                    'ecommerce_type'     => $item['discount_type'],
                    'created_at'         => $invoice_data['created_at'],
                    'created_by'         => $invoice_data['created_by'],
                ]
            );

        }

        erp_acct_insert_sales_return_data_into_ledger( $invoice_data, $voucher_no );

       // $data['dr'] = $invoice_data['amount'];
      //  $data['cr'] = 0;
       // erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {

        $wpdb->query( 'ROLLBACK' );

        return new WP_error( 'sales-return-exception', $e->getMessage() );
    }

  //  $invoice = erp_acct_get_invoice_for_return($voucher_no) ;

  //  do_action( 'erp_acct_new_transaction_sales_return', $voucher_no, $invoice );

    return $voucher_no;
}




/**
 * Get formatted invoice data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_sales_return_data( $data, $voucher_no ) {
    $invoice_data = [];

    // We can pass the name from view... to reduce DB query load
    if ( empty( $data['customer_name'] ) ) {
        $customer      = erp_get_people( $data['customer_id'] );
        $customer_name = $customer->first_name . ' ' . $customer->last_name;
    } else {
        $customer_name = $data['customer_name'];
    }

    $invoice_data['sales_voucher_no']= isset( $data['sales_voucher_no'] ) ? $data['sales_voucher_no'] : null;
    $invoice_data['voucher_no']      = ! empty( $voucher_no ) ? $voucher_no : 0;
    $invoice_data['customer_id']     = isset( $data['customer_id'] ) ? $data['customer_id'] : null;
    $invoice_data['customer_name']   = $customer_name;
    $invoice_data['return_date']     = isset( $data['return_date'] ) ? $data['return_date'] : date( 'Y-m-d' );
    $invoice_data['amount']          = isset( $data['amount'] ) ? $data['amount'] : 0;
    $invoice_data['discount']        = isset( $data['discount'] ) ? $data['discount'] : 0;
    $invoice_data['discount_type']   = isset( $data['discount_type'] ) ? $data['discount_type'] : null;
    $invoice_data['tax_rate_id']     = isset( $data['tax_rate_id'] ) ? $data['tax_rate_id'] : 0;
    $invoice_data['line_items']      = isset( $data['line_items'] ) ? $data['line_items'] : [];
    $invoice_data['tax']             = isset( $data['tax'] ) ? $data['tax'] : 0;
    $invoice_data['attachments']     = ! empty( $data['attachments'] ) ? $data['attachments'] : '';
    $invoice_data['status']          = isset( $data['status'] ) ? $data['status'] : 1;

    $invoice_data['return_reason'] = ! empty( $data['return_reason'] ) ? $data['return_reason'] : sprintf( __( 'Invoice created with voucher no %s', 'erp' ), $voucher_no );
    $invoice_data['created_at']  = isset( $data['created_at'] ) ? $data['created_at'] : null;
    $invoice_data['created_by']  = isset( $data['created_by'] ) ? $data['created_by'] : null;
    $invoice_data['updated_at']  = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $invoice_data['updated_by']  = isset( $data['updated_by'] ) ? $data['updated_by'] : null;

    return $invoice_data;
}


/**
 * Insert invoice/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_insert_sales_return_data_into_ledger( $invoice_data, $voucher_no = 0, $contra = false ) {
    global $wpdb;

    $user_id = get_current_user_id();
    $date    = date( 'Y-m-d H:i:s' );

    $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();

    $sales_return_ledger_id          = $ledger_map->get_ledger_id_by_slug( 'sales_returns_and_allowance' );
    $sales_return_tax_ledger_id          = $ledger_map->get_ledger_id_by_slug( 'sales_return_tax' );
    $sales_return_discount_ledger_id = $ledger_map->get_ledger_id_by_slug( 'sales_return_discount' );


    // insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        [
            'ledger_id'   => $sales_return_ledger_id,
            'trn_no'      => $voucher_no,
            'particulars' => "Sales returned with voucher no ".$voucher_no,
            'debit'       => $invoice_data['amount'],
            'credit'      => 0,
            'trn_date'    => $invoice_data['return_date'],
            'created_at'  => $date,
            'created_by'  => $user_id,
            'updated_at'  => $date,
            'updated_by'  => $user_id,
        ]
    );

    if( $invoice_data['tax'] >  0 ){

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $sales_return_tax_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => "Sales returned with voucher no ".$voucher_no,
                'debit'       => $invoice_data['tax'],
                'credit'      => 0,
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );

    }


    if( $invoice_data['discount'] >  0 ){

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
                'ledger_id'   => $sales_return_discount_ledger_id,
                'trn_no'      => $voucher_no,
                'particulars' => "Sales returned with voucher no ".$voucher_no,
                'debit'       => $invoice_data['discount'],
                'credit'      => 0,
                'trn_date'    => $invoice_data['return_date'],
                'created_at'  => $date,
                'created_by'  => $user_id,
                'updated_at'  => $date,
                'updated_by'  => $user_id,
            ]
        );

    }


}



