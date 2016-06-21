<?php

$company     = new \WeDevs\ERP\Company();
$transaction = WeDevs\ERP\Accounting\Model\Transaction::find( $transaction_id );
$user        = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$theme_color = erp_get_option( 'erp_ac_pdf_theme_color', false, '#9e9e9e' );

//Create a new instance
$invoice = new WeDevs\ERP\PDF_Invoicer("A4","$","en");

//Set theme color
$invoice->set_theme_color( $theme_color );

//Set your logo
$logo_id = (int) $company->logo;

if ( $logo_id ) {
    $image = wp_get_attachment_image_src( $logo_id, 'medium' );
    $url   = $image[0];
    $invoice->set_logo( $url );
}

//Set type
$invoice->set_type( __( 'PAYMENT', 'erp' ) );

// Set barcode
if ( $transaction->invoice_number ) {
    $invoice->set_barcode( $transaction->invoice_number );
}

// Set reference
if ( $transaction->invoice_number ) {
    $invoice->set_reference( $transaction->invoice_number, __( 'PAYMENT NUMBER', 'erp' ) );
}

// Set VAT No
//$invoice->set_reference( '2034802394', __( 'VAT NO', 'accounting' ) );

// Set Issue Date
if ( $transaction-> issue_date ) {
    $invoice->set_reference( erp_format_date( $transaction->issue_date ), __( 'PAYMENT DATE', 'erp' ) );
}

// Set Due Date
if ( $transaction->due_date ) {
    $invoice->set_reference( erp_format_date( $transaction->due_date ), __( 'DUE DATE', 'erp' ) );
}

// Set Due Amount
if ( $transaction->due ) {
    $invoice->set_reference( html_entity_decode( erp_ac_get_price( $transaction->due ) ), __( 'AMOUNT DUE', 'erp' ) );
}

// Set from Address
$from_address = explode( '<br/>', $company->get_formatted_address() );
array_unshift( $from_address, $company->name );

$invoice->set_from_title( __( 'FROM', 'erp' ) );
$invoice->set_from( $from_address );

// Set to Address
$to_address = explode( PHP_EOL , $transaction->billing_address );
array_unshift( $to_address, $user->get_full_name());

$invoice->set_to_title( __( 'TO', 'erp' ) );
$invoice->set_to_address( $to_address );


// Set Column Headers
$invoice->set_table_headers( [__( 'PRODUCT', 'erp' ), __( 'QUANTITY', 'erp' ), __( 'UNIT PRICE', 'erp' ), __( 'DISCOUNT', 'erp' ), __( 'TAX(%)', 'erp' ), __( 'AMOUNT', 'erp' )] );
$invoice->set_first_column_width(60);

// Add Table Items
foreach ( $transaction->items as $line ) {
    $invoice->add_item( [$line->journal->ledger->name], $line->qty, html_entity_decode( erp_ac_get_price( $line->unit_price ) ), $line->discount, '', html_entity_decode( erp_ac_get_price( $line->line_total ) ) );
}

// Subtotal and Total
$total_paid = floatval( $transaction->total ) - floatval( $transaction->due );

$invoice->add_total( __( 'SUB TOTAL', 'erp' ), html_entity_decode( erp_ac_get_price( $transaction->sub_total ) ) );
$invoice->add_total( __( 'TOTAL', 'erp' ), html_entity_decode( erp_ac_get_price( $transaction->total ) ), true );
$invoice->add_total( __( 'TOTAL RP', 'erp' ), html_entity_decode( erp_ac_get_price( $total_paid ) ), true );

//Add Badge
$invoice->add_badge(__( 'PAID', 'erp' ) );
//Add Title
//$invoice->add_title("Payment information");
//Add Paragraph
//$invoice->add_paragraph("Make all cheques payable to weDevs.\nIf you have any questions concerning this invoice, contact our sales department at sales@envato.com.\n\nThank you for your business.");
//Set footer note
//$invoice->set_footer_note("http://www.wedevs.com");
//Render the PDF
$file_name = sprintf( '%s_%s.pdf', $transaction->invoice_number, date( 'd-m-Y' ) );
$invoice->render( $file_name, $output_method );
