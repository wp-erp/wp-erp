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
            $label = __( 'Closed', 'erp' );
            break;

        case 'paid':
            $label = __( 'Paid', 'erp' );
            break;

        case 'awaiting_payment':
            $label = __( 'Awaiting Payment', 'erp' );
            break;

        case 'overdue':
            $label = __( 'Overdue', 'erp' );
            break;

        case 'partial':
            $label = __( 'Partially Paid', 'erp' );
            break;

        case 'draft':
            $url   = admin_url( 'admin.php?page='.$slug.'&action=new&type=' . $items->form_type . '&transaction_id=' . $items->id );
            $label = sprintf( '%1s<a href="%2s">%3s</a>', __( 'Draft', 'erp' ), $url, __( ' (Edit)', 'accounting') );
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
        'cancel'        => __( 'Cancel', 'erp' ),
        'error'         => __( 'Error!', 'erp' ),
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

function pr($value) {
    echo '<pre>'; print_r( $value ); echo '</pre>';
}





