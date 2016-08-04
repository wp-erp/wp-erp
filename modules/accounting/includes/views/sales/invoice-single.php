<?php
$company             = new \WeDevs\ERP\Company();
$user                = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$payments            = \WeDevs\ERP\Accounting\Model\Payment::where('child', '=', $transaction->id )->get()->toArray();
$partials_id         = wp_list_pluck( $payments, 'transaction_id' );
$partial_transaction = \WeDevs\ERP\Accounting\Model\Transaction::whereIn( 'id', $partials_id )->get()->toArray();

$url                 = admin_url( 'admin.php?page=erp-accounting-sales&action=new&type=invoice&transaction_id=' . $transaction->id );
$more_details_url    = erp_ac_get_slaes_payment_invoice_url( $transaction->id );
$taxinfo             = erp_ac_get_tax_info();

$current_user        = wp_get_current_user();
$sender              = $current_user->user_email;
$email_subject       = __( 'Invoice#', 'erp' ) . $transaction->invoice_number . __( ' from ', 'erp' ) . $company->name;
$link_hash           = erp_ac_get_invoice_link_hash( $transaction );
$readonly_url        = add_query_arg( [ 'query' => 'readonly_invoice', 'trans_id' => $transaction->id, 'auth' => $link_hash ], site_url() );
?>
<div class="wrap">

    <h2>
    <?php
        _e( 'Invoice ', 'erp' );
        if ( isset( $popup_status ) ) {
            printf( '<a href="%1$s" class="erp-ac-more-details">%2$s &rarr;</a>', $more_details_url, __('More Details','erp') );
        }
    ?>
    </h2>

    <div class="invoice-preview-wrap">

        <div class="erp-grid-container">
            <?php
            if ( ! isset( $popup_status ) ) {
                ?>
                <div class="row invoice-buttons erp-hide-print" id="invoice-button-container" data-theme="drop-theme-hubspot-popovers">
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
                            <a data-type="payment" title="hello" href="#" data-transaction_id=<?php echo $transaction->id; ?> data-due_amount=<?php echo $transaction->due; ?> data-customer_id=<?php echo intval($transaction->user_id); ?> class="button button-primary button-large add-invoice-payment erp-hide-print"><?php _e( 'Add Payment', 'erp' ); ?></a>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'erp' ); ?></a>
                            <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'erp' ); ?></a>
                            <?php
                        } else {
                            ?>
                            <a data-type="payment" href="#" data-transaction_id=<?php echo $transaction->id; ?> data-due_amount=<?php echo $transaction->due; ?> data-customer_id=<?php echo intval($transaction->user_id); ?> class="button button-primary button-large add-invoice-payment erp-hide-print"><?php _e( 'Add Payment', 'accounting' ); ?></a>
                            <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                            <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                            <?php
                        } 
                        ?>
                    </div>
                </div>

                <template class="more-action-content">
                    <ul>
<!--                        <li><a href="#" class="invoice-duplicate">--><?php //_e( 'Duplicate', 'erp' ); ?><!--</a></li>-->
                        <li><a href="<?php echo wp_nonce_url( admin_url( "admin-ajax.php?action=erp-ac-sales-invoice-export&transaction_id={$transaction->id}" ), 'accounting-invoice-export' ); ?>" class="invoice-export-pdf"><?php _e( 'Export as PDF', 'erp' ); ?></a></li>
                        <li id="get-readonly-link"><a href="#" data-title="<?php _e( 'Get Invoice Link', 'erp' ); ?>" class="invoice-get-link"><?php _e( 'Get Link', 'erp' ); ?></a></li>
                        <li id="copy-readonly-link" style="display: none"><input onClick="this.select();" type="text" value="<?php echo esc_url( $readonly_url ); ?>" id="invoice-readonly-link">&nbsp;<a data-clipboard-target="#invoice-readonly-link" class="copy-readonly-invoice" title="<?php _e('Click to copy', 'erp' ); ?>" id="erp-tips-get-link" style="cursor: pointer"><i class="fa fa-copy"></i></a></li>
                        <li><a href="#" data-url="<?php echo esc_url( $readonly_url ); ?>" data-transaction-id="<?php echo $transaction->id; ?>" data-sender="<?php echo $sender; ?>" data-receiver="<?php echo $user->email; ?>" data-subject="<?php echo $email_subject; ?>" data-title="<?php _e( 'Send Invoice', 'erp' ); ?>" data-button="<?php _e( 'Send', 'erp' ); ?>" data-type="invoice" class="invoice-send-email"><?php _e( 'Send Via Email', 'erp' ); ?></a></li>
                    </ul>
                </template>

                <?php
            }
            ?>
            <div class="row">
                <div class="invoice-number">
                    <?php
                        $ivoice = isset( $transaction->invoice_number ) ? erp_ac_get_invoice_number( $transaction->invoice_number, $transaction->invoice_format ) : $transaction->id;
                        printf( __( 'Invoice: <strong>%s</strong>', 'erp' ), $ivoice );
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
                                <th><?php _e( 'Invoice Number', 'erp' ); ?>:</th>
                                <td><?php echo $ivoice; ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Invoice Date', 'erp' ); ?>:</th>
                                <td><?php echo strtotime( $transaction->issue_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->issue_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Due Date', 'erp' ); ?>:</th>
                                <td><?php echo strtotime( $transaction->due_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->due_date ); ?></td>
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

                        <?php foreach ( $transaction->items as $line ) {  ?>
                            <tr>
                                <td class="align-left product-name">
                                    <strong><?php echo $line->journal->ledger->name; ?></strong>
                                    <div class="product-desc"><?php echo $line->description; ?></div>
                                </td>
                                <td><?php echo $line->qty; ?></td>
                                <td><?php echo erp_ac_get_price( $line->unit_price ); ?></td>
                                <td><?php echo $line->discount; ?></td>

                                <td><?php //echo $taxinfo[$line->tax]['name'] .' ('. $taxinfo[$line->tax]['rate'] .'%)'; ?></td>
                                <td><?php echo ( $line->tax_rate * $line->line_total ) / 100; ?></td>
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

