<?php
$end     = empty( $_GET['end'] ) ? date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) : $_GET['end'];
$ledgers = erp_ac_reporting_query( $end );
$clos_inc_exp_balance = erp_ac_get_closing_income_expense( $end );
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

$debit_total  = $clos_inc_exp_balance->debit;
$credit_total = $clos_inc_exp_balance->credit;

?>

<div class="wrap">
    <h2><?php _e( 'Trial Balance', 'erp' ); ?></h2>
    <div class="erp-ac-trial-report-header-wrap">
       <!--  <p class="erp-ac-report-tax-date">
        <?php
            //printf( '<i class="fa fa-calendar"></i> %1$s', erp_format_date( $end, 'F j, Y' ) );
        ?>
        </p> -->

        <?php erp_ac_report_filter_form(false); ?>
    </div>
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
                    foreach ( $charts as $class ) {
                        $report = 0;
                        ?>

                        <tr class="chart-head">
                            <td colspan="3"><strong><?php echo $class['label'] ?></strong></td>
                        </tr>
                        <?php
                        foreach ( $class['ledgers'] as $ledger ) {
                            $balance = $ledger->credit - $ledger->debit;

                            if ( $balance < 0  ) {
                                $credit = '0.00';
                                $debit = abs( $balance );
                            } else {
                                $credit = abs( $balance );
                                $debit = '0.00';
                            }

                            // if ( $ledger->id == 1 ) {
                            //     $debit  =  floatval( $ledger->debit ) - floatval( $ledger->credit );
                            //     $credit = '0.00';
                            // } else {
                            //     $debit        = floatval( $ledger->debit );
                            //     $credit       = floatval( $ledger->credit );
                            // }

                            // $new_balance = $debit - $credit;

                            // if ( $new_balance >= 0 ) {
                            //     $debit = $new_balance;
                            //     $credit = 0;
                            // } else {
                            //     $credit = abs( $new_balance );
                            //     $debit = 0;
                            // }

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
                    ?>

                    <tr class="chart-head">
                        <td><strong><?php _e( 'Balance', 'erp' ); ?></strong></td>

                        <td class="col-price"><?php echo erp_ac_get_price( $clos_inc_exp_balance->debit ); ?></td>
                        <td class="col-price"><?php echo erp_ac_get_price( $clos_inc_exp_balance->credit ); ?></td>
                    </tr>
                    <?php
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
