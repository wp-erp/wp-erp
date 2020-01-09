<?php
$company     = new \WeDevs\ERP\Company();
$user        = new \WeDevs\ERP\People( intval( $transaction['customer_id'] ) );
$transaction = erp_acct_get_transaction( $transaction_id );
?>

<html>

<head>
    <title><?php printf( '%s %s %s', esc_attr( $transaction['type'] ), esc_attr__( 'from', 'erp' ), esc_attr( $company->name ) ); ?></title>
    <link rel="stylesheet" href="<?php echo esc_url( WPERP_ASSETS . '/css/invoice-front.css' ); ?>">
    <?php do_action( 'erp_readonly_invoice_header' ); ?>
</head>

<body>

<div id="wrap">

    <?php do_action( 'erp_readonly_invoice_body', $company, $user, $transaction_id, $transaction ); ?>

    <div class="main-container">

        <div class="summary">
            <span> <?php printf( '<span class="invoice-summary-key">%s</span> %s', esc_attr__( 'Transaction Type: ', 'erp' ), esc_html( $transaction['type'] ) ); ?></span>
            <div class="readonly-invoice-separator"></div>
            <span> <?php printf( '<span class="invoice-summary-key">%s</span> %s', esc_attr__( 'Amount Due: ', 'erp' ), esc_html( erp_acct_get_price( $transaction['total_due'] ) ) ); ?></span>
            <div class="readonly-invoice-separator"></div>
            <span> <?php printf( '<span class="invoice-summary-key">%s</span> %s', esc_attr__( 'Date Due: ', 'erp' ), strtotime( $transaction['due_date'] ) < 0 ? '&mdash;' : esc_html( erp_format_date( $transaction['due_date'] ) ) ); ?> </span>
        </div>

        <div class="invoice">
            <div class="invoice-name">
                <h2><?php esc_html_e( 'INVOICE', 'erp' ); ?></h2>
            </div>
            <div class="invoice-header">
                <div class="logo">
                    <?php echo wp_kses_post( $company->get_logo() ); ?>
                </div>
                <div class="invoice-summary">
                    <table style="width:auto" id="summary-table">
                        <tbody>
                        <tr>
                            <th class="table-row-title"><?php esc_html_e( 'Voucher Number', 'erp' ); ?>:</th>
                            <td><?php echo esc_html( $transaction_id ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php esc_html_e( 'Date', 'erp' ); ?>:</th>
                            <td><?php echo strtotime( $transaction['trn_date'] ) < 0 ? '&mdash;' : esc_html( erp_format_date( $transaction['trn_date'] ) ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php esc_html_e( 'Due Date', 'erp' ); ?>:</th>
                            <td><?php echo strtotime( $transaction['due_date'] ) < 0 ? '&mdash;' : esc_html( erp_format_date( $transaction['due_date'] ) ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php esc_html_e( 'Total', 'erp' ); ?>:</th>
                            <td><?php echo esc_html( erp_acct_get_price( $transaction['amount'] + $transaction['tax'] - $transaction['discount'] ) ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="invoice-address">
                <div class="company-address">
                    <h3 class="invoice-address-direction"><?php esc_html_e( 'From', 'erp' ); ?></h3>
                    <b><?php echo esc_html( $company->name ); ?></b>
                    <div class="formatted-address">
                        <?php echo wp_kses_post( $company->get_formatted_address() ); ?>
                    </div>
                </div>
                <div class="bill-to">
                    <h3 class="invoice-address-direction"><?php esc_html_e( 'To', 'erp' ); ?></h3>
                    <strong><?php echo esc_html( $user->get_full_name() ); ?></strong>
                    <div class="billing-address"><?php echo wp_kses_post( nl2br( $transaction['billing_address'] ) ); ?></div>
                </div>
            </div>

            <div class="invoice-table">
                <table class="invoice-item-list">
                    <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e( 'Product', 'erp' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Quantity', 'erp' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Unit Price', 'erp' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Amount', 'erp' ); ?></th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ( $transaction['line_items'] as $line ) { ?>
                        <tr>
                            <td scope="row" class="align-left">
                                <strong><?php echo esc_html( $line['name'] ); ?></strong>
                            </td>
                            <td class="align-right"><?php echo esc_html( $line['qty'] ); ?></td>
                            <td class="align-right"><?php echo esc_html( erp_acct_get_price( $line['unit_price'] ) ); ?></td>
                            <td class="align-right"><?php echo esc_html( erp_acct_get_price( $line['line_total'] ) ); ?></td>
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
                            <th class="table-row-title"><?php esc_html_e( 'Discount', 'erp' ); ?></th>
                            <td class="align-right"><?php echo esc_html( erp_acct_get_price( $transaction['discount'] ) ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php esc_html_e( 'Tax', 'erp' ); ?></th>
                            <td class="align-right"><?php echo esc_html( erp_acct_get_price( $transaction['tax'] ) ); ?></td>
                        </tr>
                        <tr>
                            <th class="table-row-title"><?php esc_html_e( 'Total', 'erp' ); ?></th>
                            <td><?php echo esc_html( erp_acct_get_price( $transaction['amount'] + $transaction['tax'] - $transaction['discount'] ) ); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="invoice-extra">

            </div>
        </div>

        <div class="footer">
            <span><?php printf( '%s %s', esc_html__( 'Powered By ', 'erp' ), esc_html( $company->name ) ); ?> </span>
        </div>
    </div>
</div>


<?php do_action( 'erp_readonly_invoice_footer', $company, $user, $transaction_id, $transaction['type'] ); ?>
</body>

</html>


