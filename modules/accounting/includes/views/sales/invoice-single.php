<?php
$invoice             = new WeDevs\ERP\Accounting\Statement( $transaction );
$user                = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$payments            = \WeDevs\ERP\Accounting\Model\Payment::where( 'child', $transaction->id )->get()->toArray();
$partials_id         = wp_list_pluck( $payments, 'transaction_id' );
$partial_transaction = \WeDevs\ERP\Accounting\Model\Transaction::whereIn( 'id', $partials_id )->get()->toArray();

$current_user        = wp_get_current_user();
$sender              = $current_user->user_email;
$email_subject       = __( 'Invoice#', 'erp' ) . $transaction->invoice_number . __( ' from ', 'erp' ) . $invoice->company->name;
$link_hash           = erp_ac_get_invoice_link_hash( $transaction );
$readonly_url        = add_query_arg( [ 'query' => 'readonly_invoice', 'trans_id' => $transaction->id, 'auth' => $link_hash ], site_url() );
?>
<div class="wrap">
    <div class="invoice-preview-wrap erp-hide-print clear">
        <h3><?php printf( '%s <span class="faded">#%s</span>', __( 'Invoice', 'erp' ), $invoice->invoice_number ); ?></h3>

        <div class="row invoice-buttons" id="invoice-button-container" data-theme="drop-theme-hubspot-popovers">
            <div>
                <?php
                if ( $transaction->status == 'draft' ) {
                    ?>
                    <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a>
                    <a href="#" class="button button-large erp-ac-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                    <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                    <?php
                } else if ( $transaction->status == 'paid' || $transaction->status == 'closed' ) {
                    ?>
                    <a href="#" class="button button-large erp-ac-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                    <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                    <?php
                } else if ( $transaction->status == 'partial' || $transaction->status == 'awaiting_payment' ) {
                    ?>
                    <a data-type="payment" href="<?php echo erp_ac_get_slaes_payment_url( $transaction->id ); ?>" data-transaction_id=<?php echo $transaction->id; ?> data-due_amount=<?php echo $transaction->due; ?> data-customer_id=<?php echo intval($transaction->user_id); ?> class="button button-primary button-large"><?php _e( 'Add Payment', 'erp' ); ?></a>
                    <!-- <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a> -->
                    <a href="#" class="button button-large erp-ac-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'erp' ); ?></a>
                    <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'erp' ); ?></a>
                    <?php
                } else {
                    ?>
                    <!-- <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Invoice', 'erp' ); ?></a> -->
                    <a href="#" class="button button-large erp-ac-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'accounting' ); ?></a>
                    <a class="button button-large drop-target"><i class="fa fa-cog"></i>&nbsp;<?php _e( 'More Actions', 'accounting' ); ?></a>
                    <?php
                }
                ?>
            </div>

            <template class="more-action-content">
                <ul>
                    <li><a href="<?php echo wp_nonce_url( admin_url( "admin-ajax.php?action=erp-ac-sales-invoice-export&transaction_id={$transaction->id}" ), 'accounting-invoice-export' ); ?>" class="invoice-export-pdf"><?php _e( 'Export as PDF', 'erp' ); ?></a></li>
                    <li id="get-readonly-link"><a href="#" data-title="<?php _e( 'Get Invoice Link', 'erp' ); ?>" class="invoice-get-link"><?php _e( 'Get Link', 'erp' ); ?></a></li>
                    <li id="copy-readonly-link" style="display: none"><input onClick="this.select();" type="text" value="<?php echo esc_url( $readonly_url ); ?>" id="invoice-readonly-link">&nbsp;<a data-clipboard-target="#invoice-readonly-link" class="copy-readonly-invoice" title="<?php _e('Click to copy', 'erp' ); ?>" id="erp-tips-get-link" style="cursor: pointer"><i class="fa fa-copy"></i></a></li>
                    <li><a href="#" data-url="<?php echo esc_url( $readonly_url ); ?>" data-transaction-id="<?php echo $transaction->id; ?>" data-sender="<?php echo $sender; ?>" data-receiver="<?php echo $user->email; ?>" data-subject="<?php echo $email_subject; ?>" data-title="<?php _e( 'Send Invoice', 'erp' ); ?>" data-button="<?php _e( 'Send', 'erp' ); ?>" data-type="invoice" class="invoice-send-email"><?php _e( 'Send Via Email', 'erp' ); ?></a></li>
                </ul>
            </template>
        </div>
    </div>

    <?php $invoice->print_statement(); ?>

    <?php include_once WPERP_ACCOUNTING_VIEWS . '/common/attachment.php'; ?>
    <?php include_once WPERP_ACCOUNTING_VIEWS . '/common/partial-payments.php'; ?>
</div>

