<?php
$ledgers = erp_ac_reporting_query();

foreach ($ledgers as $ledger) {
    $charts[$ledger->class_id][$ledger->id][] = $ledger;
}

$sales_total   = erp_ac_get_sales_total_without_tax( $charts ) + erp_ac_get_sales_tax_total( $charts );
$goods_sold    = erp_ac_get_good_sold_total_amount( $charts );
$expense_total = erp_ac_get_expense_total_without_tax( $charts );
$expense_total = $expense_total - $goods_sold;
$tax_total     = erp_ac_get_sales_tax_total( $charts ) + erp_ac_get_expense_tax_total( $charts );

?>
<div class="wrap erp-ac-income-satement-wrap">

    <h1><?php _e( 'Accounting Reports: Income Statement', 'erp' ); ?></h1>

    <p class="erp-ac-report-tax-date">
    <?php
    $start = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) );
    $end   = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) );
    printf( '<i class="fa fa-calendar"></i> %1$s %2$s %3$s %4$s', __( 'From', 'erp' ), $start, __( 'to', 'erp' ),  $end);
    ?>
    </p>


    <div class="metabox-holder">
        <div class="postbox">
            <h2 class="hndle"><span><?php _e( 'Income Statement', 'erp' ); ?></span></h2>
            <div class="inside">

                <table cellpadding="10" class="erp-ac-report-income-statement-table">
                    <tr>
                        <td><?php _e( 'Revenue', 'erp' ); ?></td>
                        <td><?php echo erp_ac_get_sales_url( erp_ac_get_price( $sales_total ) ); ?></td>
                    </tr>
                    <tr class="erp-ac-even erp-ac-even-first">
                        <td><?php _e( 'Cost of goods sold', 'erp' ); ?></td>
                        <td><?php echo erp_ac_get_account_url( 24, erp_ac_get_price( $goods_sold ) ); ?></td>
                    </tr>
                    <tr class="erp-ac-odd">
                        <td><strong><?php _e( 'Gross income', 'erp' ); ?></strong></td>
                        <td>
                            <?php $gross = $sales_total - $goods_sold; ?>
                            <strong><?php echo  erp_ac_get_price( ( $gross ) ); ?></strong>
                        </td>
                    </tr>
                    <tr class="erp-ac-even">
                        <td><?php _e( 'Overhead ', 'erp' ); ?><span title="Selling, General and Administrative Expenses">(SG&A)</span></td>
                        <td><?php echo erp_ac_get_expense_url( erp_ac_get_price( $expense_total ) ); ?></td>
                    </tr>
                    <tr class="erp-ac-odd">
                        <td><strong><?php _e( 'Operating income', 'erp' ); ?></strong></td>
                        <td>
                            <?php $operating = $gross - $expense_total; ?>
                            <strong><?php echo erp_ac_get_price( $operating ); ?></strong>
                        </td>
                    </tr>
                    <tr class="erp-ac-even">
                        <td><?php _e( 'Tax', 'erp' ); ?></td>
                        <td><?php echo erp_ac_get_price( $tax_total ); ?></td>
                    </tr>
                    <tr class="erp-ac-odd">
                        <td><strong><?php _e( 'Net income', 'erp' ); ?></strong></td>
                        <td>
                            <?php $net = $operating - $tax_total; ?>
                            <strong><?php echo erp_ac_get_price( $net ); ?></strong>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

    </div>
</div>




