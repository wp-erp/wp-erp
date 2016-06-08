<?php
global $wpdb;
$tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
$tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
$tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
$tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
$tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

$financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
$financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

$sql = "SELECT led.id, led.code, led.name, led.type_id, types.name as type_name, types.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
FROM $tbl_ledger as led
LEFT JOIN $tbl_type as types ON types.id = led.type_id
LEFT JOIN $tbl_class as class ON class.id = types.class_id
LEFT JOIN $tbl_journals as jour ON jour.ledger_id = led.id
LEFT JOIN $tbl_transaction as tran ON tran.id = jour.transaction_id
WHERE tran.status IS NULL OR tran.status != 'draft' AND ( tran.issue_date >= '$financial_start' AND tran.issue_date <= '$financial_end' )
GROUP BY led.id";

$ledgers = $wpdb->get_results( $sql );
$charts  = [];

if ( $ledgers ) {
    foreach ($ledgers as $ledger) {

        if ( ! isset( $charts[ $ledger->class_id ] ) ) {
            $charts[ $ledger->class_id ]['label'] = $ledger->class_name;
            $charts[ $ledger->class_id ]['ledgers'][] = $ledger;
        } else {
            $charts[ $ledger->class_id ]['ledgers'][] = $ledger;
        }
    }
}

$debit_total = 0.00;
$credit_total = 0.00;
?>

<div class="wrap">
    <h2><?php _e( 'Trial Balance', 'erp' ); ?></h2>

    <table class="table widefat striped">
        <thead>
            <tr>
                <th><?php _e( 'Account Name', 'erp' ); ?></th>
                <th class="col-price"><?php _e( 'Debit Total', 'erp' ); ?></th>
                <th class="col-price"><?php _e( 'Credit Total', 'erp' ); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php if ( $charts ) {

                    foreach ($charts as $class) {
                        $report = 0;
                        ?>

                        <tr class="chart-head">
                                <td colspan="3"><strong><?php echo $class['label'] ?></strong></td>
                        </tr>
                        <?php
                        foreach ( $class['ledgers'] as $ledger ) {

                            if ( $ledger->id == 1 ) {
                                $debit  =  floatval( $ledger->debit ) - floatval( $ledger->credit );
                                $credit = '0.00';
                            } else {
                                $debit        = floatval( $ledger->debit );
                                $credit       = floatval( $ledger->credit );
                            }

                            $new_balance = $debit - $credit;

                            if ( $new_balance >= 0 ) {
                                $debit = $new_balance;
                                $credit = 0;
                            } else {
                                $credit = abs( $new_balance );
                                $debit = 0;
                            }

                            $debit_total  += $debit;
                            $credit_total += $credit;
                            $content = sprintf( '&nbsp; &nbsp; &nbsp;%s (%s)', $ledger->name, $ledger->code );
                            $ledger_individul_url = erp_ac_get_account_url( $ledger->id, $content ); //admin_url( 'admin.php?page=erp-accounting-charts&action=view&id=' . $ledger->id );

                            if ( $debit == 0 && $credit == 0 ) {
                                continue;
                            }
                            $report = $report + 1;
                            ?>

                            <tr>
                                <td>
                                    <?php echo $ledger_individul_url; ?>
                                </td>
                                <td class="col-price"><?php echo erp_ac_get_price( $debit ); ?></td>
                                <td class="col-price"><?php echo erp_ac_get_price( $credit ); ?></td>
                            </tr>
                            <?php
                        }

                        if ( $report == 0 ) {
                            ?>
                                <tr><td colspan="3"><?php _e( 'No Data Found!' , 'erp'); ?></td></tr>
                            <?php
                        }
                    }

                } else { ?>
                    <tr><td colspan="3"><?php _e( 'No Data Found!' , 'erp'); ?></td></tr><?php
                } ?>
        </tbody>

        <tfoot>
            <tr>
                <th><?php _e( 'Total', 'erp' ); ?></th>
                <th class="col-price"><?php echo erp_ac_get_price( $debit_total ); ?></th>
                <th class="col-price"><?php echo erp_ac_get_price( $credit_total ); ?></th>
            </tr>
        </tfoot>
    </table>

</div>

<style>
    td.col-price,
    th.col-price {
        text-align: right;
    }
</style>