<?php
$company = new \WeDevs\ERP\Company();
$user    = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$status  = $transaction->status == 'draft' ? false : true;
$url     = admin_url( 'admin.php?page=erp-accounting-sales&action=new&type=invoice&transaction_id=' . $transaction->id );
$taxinfo = erp_ac_get_tax_info();

$current_user  = wp_get_current_user();
$sender        = $current_user->user_email;
$email_subject = __( 'Payment#', 'erp' ) . $transaction->invoice_number . __( ' from ', 'erp' ) . $company->name;
?>
<div class="wrap">

    <h2><?php _e( 'Payment', 'erp' ); ?></h2>

    <div class="invoice-preview-wrap">

        <div class="erp-grid-container">
            <div class="row payment-buttons erp-hide-print" id="payment-button-container" data-theme="drop-theme-hubspot-popovers">
                <div class="col-6">
                    <?php if ( $status ) {
                        ?>
                        <a href="#" class="button button-large erp-ac-print erp-hide-print"><?php _e( 'Print', 'erp' ); ?></a>
                        <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'erp' ); ?></a>
                        <?php
                    } else {
                        ?>
                        <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a>
                        <?php
                    }
                    ?>
                </div>

                <template class="more-action-content">
                    <ul>
                        <li><a href="#" class="payment-duplicate"><?php _e( 'Duplicate', 'erp' ); ?></a></li>
                        <li><a href="<?php echo wp_nonce_url( admin_url( "admin-ajax.php?action=erp-ac-sales-payment-export&transaction_id={$transaction->id}" ), 'accounting-payment-export' ); ?>" class="payment-export-pdf"><?php _e( 'Export as PDF', 'erp' ); ?></a></li>
                        <li><a href="#" data-transaction-id="<?php echo $transaction->id; ?>" data-sender="<?php echo $sender; ?>" data-receiver="<?php echo $user->email; ?>" data-subject="<?php echo $email_subject; ?>" data-title="<?php _e( 'Send Bill', 'erp' ); ?>" data-button="<?php _e( 'Send', 'erp' ); ?>" data-type="payment" class="payment-send-email"><?php _e( 'Send Via Email', 'erp' ); ?></a></li>
                    </ul>
                </template>

            </div>
            <div class="row">
                <div class="invoice-number">
                    <?php

                        $invoice = isset( $transaction->invoice_number ) ? erp_ac_get_invoice_number( $transaction->invoice_number, $transaction->invoice_format ) : $transaction->id;
                        printf( __( 'Payment: <strong>%s</strong>', 'erp' ), $invoice );

                    ?>
                </div>
            </div>

            <div class="page-header">
                <div class="row">
                    <div class="col-3 company-logo">
                        <?php echo $company->get_logo(); ?>
                    </div>

                    <div class="col-3 align-right">
                        <strong><?php echo $company->name ?></strong>
                        <div><?php echo $company->get_formatted_address(); ?></div>
                    </div>
                </div><!-- .row -->
            </div><!-- .page-header -->

            <hr>

            <div class="row">
                <div class="col-3">
                    <div class="bill-to"><?php _e( 'Bill to:', 'erp' ); ?></div>
                    <strong><?php echo $user->get_full_name(); ?></strong>
                    <div class="billing-address"><?php echo nl2br( $transaction->billing_address ); ?></div>
                </div>
                <div class="col-3 align-right">
                    <table class="table info-table">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Payment Number', 'erp' ); ?>:</th>
                                <td><?php echo $invoice; ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Payment Date', 'erp' ); ?>:</th>
                                <td><?php echo erp_format_date( $transaction->issue_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Due Date', 'erp' ); ?>:</th>
                                <td><?php echo erp_format_date( $transaction->due_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Amount Due', 'erp' ); ?>:</th>
                                <td><?php echo erp_ac_get_price( $transaction->due ); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- .row -->

            <hr>

            <div class="row align-right">
                <table class="table fixed striped">
                    <thead>
                        <tr>
                            <th class="align-left product-name"><?php _e( 'Product', 'erp' ) ?></th>
                            <th><?php _e( 'Quantity', 'erp' ) ?></th>
                            <th><?php _e( 'Unit Price', 'erp' ) ?></th>
                            <th><?php _e( 'Discount', 'erp' ) ?></th>
                            <th><?php _e( 'Tax(%)', 'erp' ); ?></th>
                            <th><?php _e( 'Amount', 'erp' ) ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ( $transaction->items as $line ) { ?>
                            <tr>
                                <td class="align-left product-name">
                                    <strong><?php echo $line->journal->ledger->name; ?></strong>
                                    <div class="product-desc"><?php echo $line->description; ?></div>
                                </td>
                                <td><?php echo $line->qty; ?></td>
                                <td><?php echo erp_ac_get_price( $line->unit_price ); ?></td>
                                <td><?php echo $line->discount; ?></td>
                                <td><?php echo $line->tax ? $taxinfo[$line->tax]['name'] .' ('. $taxinfo[$line->tax]['rate'] .'%)' : '0.00'; ?></td>
                                <td><?php echo erp_ac_get_price( $line->line_total ); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div><!-- .row -->

            <div class="row">
                <div class="col-3">
                    <?php echo $transaction->summary; ?>
                </div>
                <div class="col-3">
                    <table class="table info-table align-right">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Sub Total', 'erp' ); ?></th>
                                <td><?php echo erp_ac_get_price( $transaction->sub_total ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Total', 'erp' ); ?></th>
                                <td><?php echo erp_ac_get_price( $transaction->total ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Total Paid', 'erp' ); ?></th>
                                <td>
                                    <?php
                                    $total_paid = floatval( $transaction->total ) - floatval( $transaction->due );
                                    echo erp_ac_get_price( $total_paid );
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- .erp-grid-container -->
    </div>

    <?php include_once WPERP_ACCOUNTING_VIEWS . '/common/attachment.php'; ?>

</div>

