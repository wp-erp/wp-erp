<?php
$transaction = WeDevs\ERP\Accounting\Model\Transaction::find( $transaction_id );
$invoice     = new WeDevs\ERP\Accounting\Statement( $transaction, 'front-end' );
?>
<html>
    <head>
        <title><?php printf( '%s <span class="faded">#%s</span>', __( 'Invoice', 'erp' ), $invoice->invoice_number ); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="<?php echo WPERP_ASSETS . '/css/invoice-front.css' ?>">
        <?php do_action( 'erp_readonly_invoice_header' ) ?>
    </head>

    <body>
        <div id="wrap">
            <?php do_action( 'erp_readonly_invoice_body', $invoice ); ?>

            <div class="main-container">
                <?php //$invoice->print_statement(); ?>
                <?php include WPERP_ACCOUNTING_VIEWS . '/sales/invoice.php'; ?>
            </div>
        </div>


        <?php do_action( 'erp_readonly_invoice_footer', $invoice ); ?>
    </body>
</html>


