<?php
$company     = new \WeDevs\ERP\Company();
$user        = new \WeDevs\ERP\People( intval( $transaction['user_id'] ) );
$transaction = WeDevs\ERP\Accounting\Model\Transaction::find( $transaction_id );
$invoice     = isset( $transaction['invoice_number'] ) && ! empty( $transaction['invoice_number'] ) ? $transaction['invoice_number'] : $transaction['id'];
?>

<html>

<head>
    <title><?php printf( '%s %s %s', $invoice, __( 'from', 'erp' ), $company->name ); ?></title>
    <link rel="stylesheet" href="<?php echo WPERP_ASSETS . '/css/invoice-front.css' ?>">
    <?php do_action( 'erp_readonly_invoice_header' ) ?>
</head>

<body>

<div id="wrap">

    <?php do_action( 'erp_readonly_invoice_body', $company, $user, $transaction, $invoice ) ?>

    <div class="main-container">

        <div class="title">
            <h1><?php printf( '%s %s', _e( 'Request for Payment from', 'erp' ), $company->name ); ?></h1>
        </div>

        <div class="summary">
            <span> <?php printf( '<span class="invoice-summary-key">%s</span> %s', __( 'Invoice: ', 'erp' ), $invoice ); ?></span>
            <div class="readonly-invoice-separator"></div>
            <span> <?php printf( '<span class="invoice-summary-key">%s</span> %s', __( 'Amount Due: ', 'erp' ), erp_ac_get_price( $transaction['due'] ) ); ?></span>
            <div class="readonly-invoice-separator"></div>
            <span> <?php printf( '<span class="invoice-summary-key">%s</span> %s', __( 'Date Due: ', 'erp'), strtotime( $transaction['due_date'] ) < 0 ? '&mdash;' : erp_format_date( $transaction['due_date'] ) ); ?> </span>
        </div>

        <div class="invoice">
            <div class="invoice-name">
                <h2><?php _e( 'INVOICE', 'erp' ); ?></h2>
            </div>
            <div class="invoice-header">
                <div class="logo">
                    <?php echo $company->get_logo(); ?>
                </div>
                <div class="invoice-summary">
                    <table style="width:auto" id="summary-table">
                        <tbody>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Invoice Number', 'erp' ); ?>:</th>
                            <td><?php echo $invoice; ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Invoice Date', 'erp' ); ?>:</th>
                            <td><?php echo strtotime( $transaction->issue_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->issue_date ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Due Date', 'erp' ); ?>:</th>
                            <td><?php echo strtotime( $transaction->due_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->due_date ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Amount Due', 'erp' ); ?>:</th>
                            <td><?php echo erp_ac_get_price( $transaction->due ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="invoice-address">
                <div class="company-address">
                    <h3 class="invoice-address-direction"><?php _e( 'From', 'erp' );?></h3>
                    <b><?php echo $company->name; ?></b>
                    <div class="formatted-address">
                        <?php echo $company->get_formatted_address(); ?>
                    </div>
                </div>
                <div class="bill-to">
                    <h3 class="invoice-address-direction"><?php _e( 'To', 'erp' );?></h3>
                    <strong><?php echo $user->get_full_name(); ?></strong>
                    <div class="billing-address"><?php echo nl2br( $transaction['billing_address'] ); ?></div>
                </div>
            </div>

            <div class="invoice-table">
                <table class="invoice-item-list">
                    <thead>
                    <tr>
                        <th scope="col"><?php _e( 'Product', 'erp' ) ?></th>
                        <th scope="col"><?php _e( 'Quantity', 'erp' ) ?></th>
                        <th scope="col"><?php _e( 'Unit Price', 'erp' ) ?></th>
                        <th scope="col"><?php _e( 'Discount', 'erp' ) ?></th>
                        <th scope="col"><?php _e( 'Tax (%)', 'erp' ) ?></th>
                        <th scope="col"><?php _e( 'Amount', 'erp' ) ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ( $transaction['items'] as $line ) {  ?>
                        <tr>
                            <td scope="row" class="align-left">
                                <strong><?php echo $line->journal->ledger->name; ?></strong>
                                <div class="product-desc"><?php echo $line->description; ?></div>
                            </td>
                            <td class="align-right"><?php echo $line->qty; ?></td>
                            <td class="align-right"><?php echo erp_ac_get_price( $line->unit_price ); ?></td>
                            <td class="align-right"><?php echo $line->discount; ?></td>
                            <td class="align-right"><?php echo ( $line->tax_rate * $line->line_total ) / 100; ?></td>
                            <td class="align-right"><?php echo erp_ac_get_price( $line->line_total ); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="invoice-subtotal">
                <div class="subtotal">
                    <table style="width: auto" id="table-subtotal">
                        <tbody>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Sub Total', 'erp' ); ?></th>
                            <td><?php echo erp_ac_get_price( $transaction->sub_total ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Total', 'erp' ); ?></th>
                            <td><?php echo erp_ac_get_price( $transaction->total ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php _e( 'Total Related Payments', 'erp' ); ?></th>
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

            <div class="invoice-extra">

            </div>
        </div>

        <div class="footer">
            <span><?php printf( '%s %s', _e( 'Powered By ', 'erp' ), $company->name ); ?> </span>
        </div>
    </div>
</div>


<?php do_action( 'erp_readonly_invoice_footer', $company, $user, $transaction, $invoice ) ?>
</body>

</html>


