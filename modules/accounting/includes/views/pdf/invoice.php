<?php
$company     = new WeDevs\ERP\Company();
$user        = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$theme_color = erp_get_option( 'erp_ac_pdf_theme_color', false, '#9e9e9e' );

//Create a new instance
$invoice = new WeDevs\ERP\PDF_Invoicer("A4","$","en");

// Set Theme Color
$invoice->set_theme_color( $theme_color );

//Set your logo
$logo_id = (int) $company->logo;

if ( $logo_id ) {
    $image = wp_get_attachment_image_src( $logo_id, 'full' );
    $url   = $image[0];
    $invoice->set_logo( $url );
}

//Set type
$invoice->set_type( __( 'INVOICE', 'erp' ) );

// Set barcode
if ( $transaction->invoice_number ) {
    $invoice->set_barcode( $transaction->invoice_number );
}

// Set Invoice Number
if ( $transaction->invoice_number ) {
    $invoice->set_reference( $transaction->invoice_number, __( 'INVOICE NUMBER', 'erp' ) );
}

// Set reference
if ( $transaction->ref ) {
    $invoice->set_reference( $transaction->ref, __( 'REFERENCE', 'erp' ) );
}

// Set VAT No
//$invoice->set_reference( '2034802394', __( 'VAT NO', 'erp' ) );

// Set Issue Date
if ( $transaction-> issue_date ) {
    $invoice->set_reference( $transaction->issue_date, __( 'ISSUE DATE', 'erp' ) );
}

// Set Due Date
if ( $transaction->due_date ) {
    $invoice->set_reference( $transaction->due_date, __( 'DUE DATE', 'erp' ) );
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

// Set Table Column Headers
$invoice->set_table_headers( [__( 'PRODUCT', 'erp'), __( 'QUANTITY', 'erp'), __( 'UNIT PRICE', 'erp'), __( 'DISCOUNT', 'erp'), __( 'TAX(%)', 'erp'), __( 'AMOUNT', 'erp')] );
$invoice->set_first_column_width(60);

// Add Table Items
foreach ( $transaction->items as $line ) {
    $invoice->add_item( [$line->journal->ledger->name], $line->qty, html_entity_decode( erp_ac_get_price( $line->unit_price ) ), $line->discount, '', html_entity_decode( erp_ac_get_price( $line->line_total ) ) );
}

// Subtotal and Total
$invoice->add_total( __( 'SUB TOTAL', 'erp' ), html_entity_decode( erp_ac_get_price( $transaction->sub_total ) ) );
$invoice->add_total( __( 'TOTAL', 'erp' ), html_entity_decode( erp_ac_get_price( $transaction->total ) ), true );
//$invoice->add_total( __( 'TOTAL RP', 'erp' ), html_entity_decode( erp_ac_get_price( $total_paid ) ), true );

//Add Badge
//$invoice->add_badge( __( 'DUE', 'erp' ) );

//Add Title
//$invoice->add_title("Payment information");
//Add Paragraph
//$invoice->add_paragraph("Make all cheques payable to Envato Inc.\nIf you have any questions concerning this invoice, contact our sales department at sales@envato.com.\n\nThank you for your business.");
//Set footer note
//$invoice->set_footer_note("http://www.wedevs.com");

//Render the PDF
//$file_name = sprintf( '%s_%s.pdf', $transaction->invoice_number, date( 'd-m-Y' ) );
//wp_send_json_success($file_name);
$invoice->render( $file_name, $output_method );
