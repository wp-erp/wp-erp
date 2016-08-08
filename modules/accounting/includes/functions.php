<?php

function erp_ac_get_expense_form_types() {
    $form_types = [
        'payment_voucher' => [
            'name'        => 'payment_voucher',
            'label'       => __( 'Payment Voucher', 'erp' ),
            'description' => __( 'A purchase that has been made through bank or cash.', 'erp' ),
            'type'        => 'credit'
        ],
        'vendor_credit' => [
            'name'        => 'vendor_credit',
            'label'       => __( 'Vendor Credit', 'erp' ),
            'description' => __( 'A purchase that has been made as credit from vendor.', 'erp' ),
            'type'        => 'credit'
        ],
        // 'deleted' => [
        //     'name'        => 'trash',
        //     'label'       => __( 'Trash', 'erp' ),
        //     'description' => __( '', 'erp' ),
        //     'type'        => 'debit'
        // ],
    ];

    return apply_filters( 'erp_ac_get_expense_form_types', $form_types );
}

function erp_ac_get_sales_form_types() {
    $form_types = [
        'payment' => [
            'name'        => 'payment',
            'label'       => __( 'Payment', 'erp' ),
            'description' => __( '', 'erp' ),
            'type'        => 'debit'
        ],
        'invoice' => [
            'name'        => 'invoice',
            'label'       => __( 'Invoice', 'erp' ),
            'description' => __( '', 'erp' ),
            'type'        => 'debit'
        ],
        // 'deleted' => [
        //     'name'        => 'trash',
        //     'label'       => __( 'Trash', 'erp' ),
        //     'description' => __( '', 'erp' ),
        //     'type'        => 'debit'
        // ],
    ];

    return apply_filters( 'erp_ac_get_sales_form_types', $form_types );
}

function erp_ac_get_bank_form_types() {
    $form_types = [
        'bank' => [
            'name'        => 'bank',
            'label'       => __( 'Bank', 'erp' ),
            'description' => __( '', 'erp' ),
            'type'        => 'credit'
        ],
    ];

    return apply_filters( 'erp_ac_get_bank_form_types', $form_types );
}

/**
 * Get transaction status label
 *
 * @param  string  $status
 *
 * @return string
 */
function erp_ac_get_status_label( $items, $slug ) {
    $label  = '';
    $status = $items->status;

    switch ( $status ) {
        case 'closed':
            $label = __( 'Paid', 'erp' );
            break;

        case 'paid':
            $label = __( 'Paid', 'erp' );
            break;

        case 'awaiting_payment':
            $url   = admin_url( 'admin.php?page='.$slug.'&action=new&type=' . $items->form_type . '&transaction_id=' . $items->id );
            $label = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Awaiting for Payment', 'erp' ) );
            //$label = __( 'Awaiting for Payment', 'erp' );
            break;

        case 'overdue':
            $label = __( 'Overdue', 'erp' );
            break;

        case 'deleted':
            $label = __( 'Trash', 'erp' );
            break;

        case 'partial':
            $label = __( 'Partially Paid', 'erp' );
            break;

        case 'void':
            $label = __( 'Void', 'erp' );
            break;

        case 'pending':
            $url   = admin_url( 'admin.php?page='.$slug.'&action=new&type=' . $items->form_type . '&transaction_id=' . $items->id );
            $label = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Awaiting for approval', 'erp' ) );
            break;

        case 'draft':
            $url   = admin_url( 'admin.php?page='.$slug.'&action=new&type=' . $items->form_type . '&transaction_id=' . $items->id );
            $label = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Draft', 'erp' ) );
            break;
    }

    return apply_filters( 'erp_ac_status_labels', $label, $status );
}

/**
 * Get currency symbol
 *
 * @since 0.1
 *
 * @return string
 */
function erp_ac_get_currency_symbol() {
    $currency = erp_ac_get_currency();

    return $currency ? erp_get_currency_symbol( $currency ) : '$';
}

/**
 * Get currency
 *
 * @since 0.1
 *
 * @return string
 */
function erp_ac_get_currency() {
    $currency = erp_get_option( 'erp_ac_currency', false, 'AUD' );

    return $currency ? $currency : 'AUD';
}

/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function erp_ac_get_price_format() {
    $currency_pos = erp_get_option( 'erp_ac_currency_position', false, 'left' );
    $format = '%1$s%2$s';

    switch ( $currency_pos ) {
        case 'left' :
            $format = '%1$s%2$s';
        break;
        case 'right' :
            $format = '%2$s%1$s';
        break;
        case 'left_space' :
            $format = '%1$s&nbsp;%2$s';
        break;
        case 'right_space' :
            $format = '%2$s&nbsp;%1$s';
        break;
    }

    return apply_filters( 'erp_ac_price_format', $format, $currency_pos );
}

/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function erp_ac_get_symbol_format_for_label( $label ) {
    $currency_pos = erp_get_option( 'erp_ac_currency_position', false, 'left' );
    $format = '(%1$s)%2$s';

    switch ( $currency_pos ) {
        case 'left' :
            $format = '(%1$s)%2$s';
        break;
        case 'right' :
            $format = '%2$s(%1$s)';
        break;
        case 'left_space' :
            $format = '(%1$s)&nbsp;%2$s';
        break;
        case 'right_space' :
            $format = '%2$s&nbsp;(%1$s)';
        break;
    }

    $print_label = sprintf( $format, erp_ac_get_currency_symbol() , $label );

    return apply_filters( 'erp_ac_label_symbol_format', $print_label, $format, $label );
}

/**
 * Return the thousand separator for prices.
 *
 * @since  2.3
 *
 * @return string
 */
function erp_ac_get_price_thousand_separator() {
    $separator = stripslashes( erp_get_option( 'erp_ac_th_separator', false, ',' ) );
    return $separator;
}

/**
 * Return the decimal separator for prices.
 *
 * @since  2.3
 *
 * @return string
 */
function erp_ac_get_price_decimal_separator() {
    $separator = stripslashes( erp_get_option( 'erp_ac_de_separator', false, '.' ) );
    return $separator ? $separator : '.';
}

/**
 * Return the number of decimals after the decimal point.
 *
 * @since  2.3
 *
 * @return int
 */
function erp_ac_get_price_decimals() {
    return absint( erp_get_option( 'erp_ac_nm_decimal', false, 2 ) );
}

/**
 * Format the price with a currency symbol.
 *
 * @param float $price
 *
 * @param array $args (default: array())
 *
 * @return string
 */
function erp_ac_get_price( $main_price, $args = array() ) {
    extract( apply_filters( 'erp_ac_price_args', wp_parse_args( $args, array(
        'currency'           => erp_ac_get_currency(),
        'decimal_separator'  => erp_ac_get_price_decimal_separator(),
        'thousand_separator' => erp_ac_get_price_thousand_separator(),
        'decimals'           => erp_ac_get_price_decimals(),
        'price_format'       => erp_ac_get_price_format(),
        'symbol'             => true,
        'currency_symbol'    => erp_ac_get_currency_symbol()
    ) ) ) );

    $price           = number_format( abs( $main_price ), $decimals, $decimal_separator, $thousand_separator );
    $formatted_price = $symbol ? sprintf( $price_format, $currency_symbol, $price ) : $price;
    $formatted_price = ( $main_price < 0 ) ? '(' . $formatted_price . ')' : $formatted_price;

    return apply_filters( 'erp_ac_price', $formatted_price, $price, $args );
}

function erp_ac_get_price_for_field( $price, $args = array() ) {
    extract( apply_filters( 'erp_ac_price_args', wp_parse_args( $args, array(
        'currency'           => erp_ac_get_currency(),
        'decimal_separator'  => erp_ac_get_price_decimal_separator(),
        'thousand_separator' => '',
        'decimals'           => erp_ac_get_price_decimals(),
        'price_format'       => erp_ac_get_price_format(),
        'symbol'             => true,
        'currency_symbol'    => erp_ac_get_currency_symbol()
    ) ) ) );

    $price  = number_format( $price, $decimals, $decimal_separator, $thousand_separator );

    $formatted_price = $symbol ? sprintf( $price_format, $currency_symbol, $price ) : $price;
    return apply_filters( 'erp_ac_price', $formatted_price, $price, $args );
}

function erp_ac_format_decimal( $number ) {
    $locale   = localeconv();

    $decimals = array( erp_ac_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

    // Remove locale from string
    if ( ! is_float( $number ) ) {
        $number = sanitize_text_field( str_replace( $decimals, '.', $number ) );
    }

    if ( is_float( $number ) ) {
        $number = sanitize_text_field( str_replace( $decimals, '.', strval( $number ) ) );
    }

    return $number;
}

function erp_ac_get_customer($id) {
    return erp_get_people( $id );
}

function erp_ac_message() {
    $message = array(
        'confirm'       => __( 'Are you sure!', 'erp' ),
        'new_customer'  => __( 'New Customer', 'erp' ),
        'new_vendor'    => __( 'New Vendor', 'erp' ),
        'new'           => __( 'Create New', 'erp' ),
        'transaction'   => __( 'Transaction History', 'erp' ),
        'processing'    => __( 'Processing please wait!', 'erp' ),
        'new_tax'       => __( 'Tax Rates', 'erp' ),
        'tax_item'      => __( 'Tax item details', 'erp' ),
        'tax_update'    => __( 'Tax Update', 'erp' ),
        'tax_deleted'   => __( 'Your tax record has been deleted successfully', 'erp' ),
        'delete'        => __( 'Yes, delete it!', 'erp' ),
        'void'          => __( 'Yes, void it!', 'erp' ),
        'restore'       => __( 'Yes, restore it!', 'erp' ),
        'cancel'        => __( 'Cancel', 'erp' ),
        'error'         => __( 'Error!', 'erp' ),
        'alreadyExist'  => __( 'Already exists as a customer or vendor', 'erp' ),
        'transaction_status' => __( 'Transaction Status', 'erp' ),
        'submit'        => __( 'Submit', 'erp' ),
        'redo'          => __( 'Yes, redo it!', 'erp' ),
    );

    return apply_filters( 'erp_ac_message', $message );
}

function erp_ac_get_version() {
    return wperpac()->version;
}

function erp_ac_pagination( $total, $limit, $pagenum ) {
    $num_of_pages = ceil( $total / $limit );
    $page_links = paginate_links( array(
        'base'      => add_query_arg( 'pagenum', '%#%' ),
        'format'    => '',
        'prev_text' => __( '&laquo;', 'aag' ),
        'next_text' => __( '&raquo;', 'aag' ),
        'add_args'  => false,
        'total'     => $num_of_pages,
        'current'   => $pagenum
    ) );

    if ( $page_links ) {
        echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
    }
}

/**
 * Get invoice prefix
 * 
 * @param  string $form_type
 * @param  int $id  
 *
 * @since  1.1.2
 * 
 * @return string
 */
function erp_ac_get_auto_generated_invoice( $form_type ) {
    $invoice_number = erp_ac_generate_invoice_id( $form_type );
    $invoice_number = erp_ac_invoice_num_str_pad( $invoice_number );
    $prefix         = erp_ac_get_invoice_format( $form_type ); 
    return str_replace( '{id}', $invoice_number, $prefix );
}

/**
 * Get invoice prefix
 * 
 * @param  string $form_type
 *
 * @since  1.1.2
 * 
 * @return string
 */
function erp_ac_get_invoice_format( $form_type ) {
    if ( $form_type == 'invoice' ) {
        return erp_get_option( 'erp_ac_invoice', false, 'INV-{id}' );
    } else if ( $form_type == 'payment' ) {
        return erp_get_option( 'erp_ac_payment', false, 'SPN-{id}' );
    } else if ( $form_type == 'journal' ) {
        return erp_get_option( 'erp_ac_journal', false, 'JRNN-{id}' );
    }

    return false;
}

/**
 * Check invoice existance, in exist then generate new
 * 
 * @param  string $form_type
 * @param  string $invoice  
 *
 * @since  1.1.2
 * 
 * @return string
 */
function erp_ac_check_invoice_existance( $type, $invoice_number, $prefix ) {
    
    $invoice_number = erp_ac_invoice_num_str_pad( $invoice_number );
    $invoice        = str_replace( '{id}', $invoice_number, $prefix );
    $form_type = '';

    if ( $type == 'erp_ac_invoice' ) {
        $form_type = 'invoice';
    } else if ( $type == 'erp_ac_payment' ) {
        $form_type = 'payment';
    }


    $check_number = \WeDevs\ERP\Accounting\Model\Transaction::select(['invoice_number'])->where( 'invoice_number', '=', $invoice )->where('form_type', '=', $form_type )->get()->toArray();

    if ( $check_number ) {
        $check_number = \WeDevs\ERP\Accounting\Model\Transaction::select(['invoice_number'])->where('form_type', '=', $form_type )->get()->toArray(); 
        $check_number = wp_list_pluck( $check_number, 'invoice_number' );
        $check_status = true;

        while ( $check_status ) {
            $invoice_number = $invoice_number + 1;
            $invoice_number = erp_ac_invoice_num_str_pad( $invoice_number );
            $invoice        = str_replace( '{id}', $invoice_number, $prefix );
            
            if ( ! in_array( $invoice, $check_number ) ) {
                $check_status = false;
            }
        }
    }

    return $invoice;
}

/**
 * Str pad for invoice number
 * 
 * @param  int $invoice_number  
 *
 * @since  1.1.2
 * 
 * @return string
 */
function erp_ac_invoice_num_str_pad( $invoice_number ) {
    return str_pad( $invoice_number, 4, '0', STR_PAD_LEFT );
}

/**
 * Generate invoice id
 * 
 * @param  string $form_type 
 * 
 * @return int
 */
function erp_ac_generate_invoice_id( $form_type = '' ) {

    $invoice_number = false;

    if ( $form_type == 'invoice' ) {
        $invoice_number = get_option( 'erp_ac_sales_invoice_number', 1 );
    
    } else if ( $form_type == 'payment' ) {
        $invoice_number = get_option( 'erp_ac_sales_payment_number', 1 );
    
    } else if ( $form_type == 'journal' ) {
        $invoice_number = get_option( 'erp_ac_journal_number', 1 );
    
    } 

    return $invoice_number; //str_pad( $invoice_number, 4, '0', STR_PAD_LEFT );
}

/**
 * Update Invoice number 
 * 
 * @param  string $form_type 
 * 
 * @return void
 */
function erp_ac_update_invoice_number( $form_type ) {
    
    $invoice_number = '';
    if ( $form_type == 'invoice' ) {
        $invoice_number = get_option( 'erp_ac_sales_invoice_number', 1 );
    } else if ( $form_type == 'payment' ) {
        $invoice_number = get_option( 'erp_ac_sales_payment_number', 1 );
    }
    $get_invoice_number = WeDevs\ERP\Accounting\Model\Transaction::select('invoice_number')
        ->where( 'form_type', '=', $form_type )
        ->where( 'invoice_number', '>=', $invoice_number )
        ->get()->toArray();
    $get_invoice_number = wp_list_pluck( $get_invoice_number, 'invoice_number' );
    $status = true;

    while( $status ) {
        if ( in_array( $invoice_number, $get_invoice_number ) ) {
            $invoice_number = $invoice_number + 1;
        } else {
            $status = false;
        }
    }

    if ( $form_type == 'invoice' ) {
        update_option( 'erp_ac_sales_invoice_number', $invoice_number );
    } else if ( $form_type == 'payment' ) {
        update_option( 'erp_ac_sales_payment_number', $invoice_number );
    }
        
}

/**
 * Get default invoice prefix
 *
 * @param  string $type
 *
 * @since  1.1.2
 *
 * @return mixed string or array
 */
function erp_ac_get_default_invoice_prefix( $type = false ) {

    $prefix = [
        'erp_ac_payment'         => 'SPN-{id}',
        'erp_ac_invoice'         => 'INV-{id}',
        'erp_ac_payment_voucher' => 'EVN-{id}',
        'erp_ac_vendor_credit'   => 'ECN-{id}',
        'erp_ac_journal'         => 'JRNN-{id}'
    ];

    return $type ? $prefix[$type] : $prefix;
}

/**
 * Get unique transaction hash for sharing to customer
 *
 * @param object $transaction
 * @param string $algo
 * @since 1.1.2
 * @return string
 */
function erp_ac_get_invoice_link_hash( $transaction = '', $algo = 'sha256' ) {

    if ( $transaction ) {

        $to_hash     = $transaction->id . $transaction->form_type . $transaction->invoice_number;
        $hash_string = hash( $algo, $to_hash );
    }

    return $hash_string;
}

/**
 * Varify transaction hash
 *
 * @param object $transaction
 * @param string $hash_to_verify
 * @since 1.1.2
 * @return bool
 */
function erp_ac_verify_invoice_link_hash( $transaction = '', $hash_to_verify = '',  $algo = 'sha256' ) {

    if ( $transaction && $hash_to_verify ) {

        $to_hash       = $transaction['id'] . $transaction['form_type'] . $transaction['invoice_number'];
        $hash_original = hash( $algo, $to_hash );

        if ( $hash_original === $hash_to_verify ) {
            return true;
        }
    }

    return false;
}

/**
 * Callback to template_redirect hook
 * Shows template when invoice readonly link is called
 *
 * @since 1.1.2
 * @return mixed
 */
function erp_ac_readonly_invoice_template() {

    $query          = isset( $_REQUEST['query'] ) ? esc_attr( $_REQUEST['query'] ) : '';
    $transaction_id = isset( $_REQUEST['trans_id'] ) ? intval( $_REQUEST['trans_id'] ) : '';
    $auth_id        = isset( $_REQUEST['auth'] ) ? esc_attr( $_REQUEST['auth'] ) : '';
    $verified       = false;

    if ( !$query || !$transaction_id || !$auth_id ) {
        return;
    }

    $transaction = erp_ac_get_transaction( $transaction_id );

    if ( $transaction ) {
        $verified = erp_ac_verify_invoice_link_hash( $transaction, $auth_id );
    }

    if ( $verified ) {
        include WPERP_ACCOUNTING_VIEWS . '/sales/template-invoice-readonly.php';
        exit();
    }

    return;
}

/**
 * Get invoice number and format fron transaction submit value
 *
 * @param  string $submit_invoice
 * @param  string $invoice_format
 * 
 * @return array
 */
function erp_ac_get_invoice_num_fromat_from_submit_invoice( $submit_invoice, $invoice_format ) {
    //was found
    $pattern = str_replace( '{id}', '([0-9]+)', $invoice_format ); // INV-([0-9])+-INV
    
    preg_match( "/${pattern}/", $submit_invoice, $match );
 
    $id            = isset( $match[1] ) ? $match[1] : false;
    $check_invoice = false;
    
    if ( $id === false ) {
        return 0;
    } 

    $check_invoice = str_replace( '{id}', $id, $invoice_format );

    $invoice_number = $check_invoice == $submit_invoice ? intval( $id ) : 0;

    return $invoice_number;
}

/**
 * Get invoice number
 *
 * @param  int $invoice_number
 * @param  string $invoice_number
 * 
 * @return string
 */
function erp_ac_get_invoice_number( $invoice_number, $invoice_format ) {
    if ( $invoice_number != 0 ) {
        return  str_replace( '{id}', erp_ac_invoice_num_str_pad( $invoice_number ), $invoice_format );
    } else {
        return $invoice_format;
    }
}






