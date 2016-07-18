<?php

wp_head();
$company = new \WeDevs\ERP\Company();
$user    = new \WeDevs\ERP\People( intval( $transaction['user_id'] ) );
$invoice  = isset( $transaction['invoice_number'] ) && ! empty( $transaction['invoice_number'] ) ? $transaction['invoice_number'] : $transaction['id'];
?>

<div id="wrap">

    <div class="main-container">

        <div class="title">
            <h1>Request for Payment from <?php echo $company->name ?></h1>
        </div>

        <div class="summary">
            <span>Invoice, Due Amount, Due Date</span>
        </div>

        <div class="actions">
            <span>pdf, print, payment method</span>
        </div>

        <div class="invoice">
            <div class="invoice-header">
                <div class="logo">
                    <?php echo $company->get_logo(); ?>
                </div>
                <div class="company-address">
                    <strong><?php echo $company->name; ?></strong>
                    <div class="formatted-address">
                        <?php echo $company->get_formatted_address(); ?>
                    </div>
                </div>
            </div>

            <div class="invoice-address">
                <div class="bill-to">
                    <div><?php _e( 'Bill to:', 'erp' ); ?></div>
                    <strong><?php echo $user->get_full_name(); ?></strong>
                    <div class="billing-address"><?php echo nl2br( $transaction['billing_address'] ); ?></div>
                </div>
                <div class="invoice-summary">
                    <?php _e( 'Invoice Number', 'erp' ); ?>
                    <?php echo $invoice; ?><br>
                    <?php _e( 'Invoice Date', 'erp' ); ?>
                    <?php echo strtotime( $transaction['issue_date'] ) < 0 ? '&mdash;' : erp_format_date( $transaction['issue_date'] ); ?>
                    <br>
                    <?php _e( 'Due Date', 'erp' ); ?>
                    <?php echo strtotime( $transaction['due_date'] ) < 0 ? '&mdash;' : erp_format_date( $transaction['due_date'] ); ?>
                    <br>
                    <?php _e( 'Amount Due', 'erp' ); ?>
                    <?php echo erp_ac_get_price( $transaction['due'] ); ?>
                </div>
            </div>

            <div class="invoice-table">
                <p>Invoice Table</p>
            </div>

            <div class="invoice-subtotal">
                <div class="subtotal">
                    <p>Subtotal</p>
                </div>
            </div>

            <div class="invoice-extra">

            </div>
        </div>

        <div class="footer">
            <span>Powered by weDevs</span>
        </div>
    </div>
</div>



