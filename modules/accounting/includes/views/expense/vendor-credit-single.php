<?php
$company             = new \WeDevs\ERP\Company();
$user                = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$payments            = \WeDevs\ERP\Accounting\Model\Payment::where('child', '=', $transaction->id )->get()->toArray();
$partials_id         = wp_list_pluck( $payments, 'transaction_id' );
$partial_transaction = \WeDevs\ERP\Accounting\Model\Transaction::whereIn( 'id', $partials_id )->get()->toArray();
$symbol              = erp_ac_get_currency_symbol();

$status              = $transaction->status == 'draft' ? false : true;
$url                 = erp_ac_get_vendor_credit_edit_url( $transaction->id );
$taxinfo             = erp_ac_get_tax_info();
?>
<div class="wrap">

    <h2><?php _e( 'Vendor Credit', 'erp' ); ?></h2>

    <div class="invoice-preview-wrap">

        <div class="erp-grid-container">
            <div class="row invoice-buttons erp-hide-print">
                <div class="col-6">
                    <?php
                        if ( $transaction->status == 'draft' || $transaction->status == 'pending'  ) {
                            ?>
                            <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                            <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                            <?php   
                        } else if ( $transaction->status == 'paid' || $transaction->status == 'closed' ) {
                            ?>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                            <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                            <?php
                        } else if ( $transaction->status == 'partial' ) {
                            ?>
                            <a href="#" data-type="payment_voucher" data-transaction_id="<?php echo $transaction->id; ?>" data-due_amount="<?php echo $transaction->due; ?>" data-customer_id="<?php echo intval($transaction->user_id); ?>" class="button button-primary button-large add-invoice-payment erp-hide-print"><?php _e( 'Add Payment', 'accounting' ); ?></a>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                            <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                            <?php
                        } else {
                            ?>
                            <a href="#" data-type="payment_voucher" data-transaction_id=<?php echo $transaction->id; ?> data-due_amount=<?php echo $transaction->due; ?> data-customer_id=<?php echo intval($transaction->user_id); ?> class="button button-primary button-large add-invoice-payment erp-hide-print"><?php _e( 'Add Payment', 'accounting' ); ?></a>
                            <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                            <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                            <?php
                        } 
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="invoice-number">
                    <?php 
                        printf( __( 'Credit Number: <strong>%s</strong>', 'erp' ), $transaction->id ); 
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
                                <th><?php _e( 'Referance Number', 'erp' ); ?>:</th>
                                <td><?php echo $transaction->ref; ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Credit Date', 'erp' ); ?>:</th>
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
                                <th><?php _e( 'Total Related Payments', 'erp' ); ?></th>
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
    <?php include_once WPERP_ACCOUNTING_VIEWS . '/common/partial-payments.php'; ?>

</div>

