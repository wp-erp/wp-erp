<?php
$query = erp_ac_reporting_query();
 
     echo '<pre>'; print_r( $query ); echo '</pre>'; die();
    
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

$charts = [];

foreach ($ledgers as $ledger) {

    $charts[$ledger->class_id][$ledger->id][] = $ledger;
}

$assets      = isset( $charts[1] ) ? $charts[1] : [];
$liabilities = isset( $charts[2] ) ? $charts[2] : [];
$equities    = isset( $charts[5] ) ? $charts[5] : [];

$sales_total   = erp_ac_get_sales_total();
$goods_sold    = erp_ac_get_good_sold_total_amount();
$expense_total = erp_ac_get_expense_total();
$tax_total     = erp_ac_get_tax_total();
$gross         = $sales_total - $goods_sold;
$operating     = $gross - $expense_total;
$net_income    = $operating - $tax_total;
//$income        =  $sales_total-$expense_total;
?>

<div class="warp erp-ac-balance-sheet-wrap">
<h1><?php _e( 'Accounting Reports: Balance Sheet', 'erp' ); ?></h1>
<p class="erp-ac-report-tax-date">
<?php
$start = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) );
$end   = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) );
printf( '<i class="fa fa-calendar"></i> %1$s %2$s %3$s %4$s', __( 'From', 'erp' ), $start, __( 'to', 'erp' ),  $end); ?>
</p>

    <div class="metabox-holder">

        <div class="postbox ">
            <h2 class="hndle"><span><?php _e( 'Assets', 'erp' ); ?></span></h2>
            <div class="inside">
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th><strong><?php _e( 'Accounts', 'erp' ); ?></strong></th>
                            <th><strong><?php _e( 'Balance', 'erp' ); ?></strong></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $assets_total_balance = 0;

                            foreach ( $assets as $key => $asset ) {
                                $account   = reset( $asset );
                                $debit     = array_sum( wp_list_pluck( $asset, 'debit' ) );
                                $credit    = array_sum( wp_list_pluck( $asset, 'credit' ) );
                                $balance   = $debit - $credit;
                                $ac_amount = erp_ac_get_price( $balance, [ 'symbol' => false ] );

                                if ( $balance == 0 ) {
                                    continue;
                                }

                                $assets_total_balance = $assets_total_balance + $balance;

                                ?>
                                    <tr>
                                        <td><?php echo erp_ac_get_account_url( $account->id, $account->name ); ?></td>
                                        <td><?php echo $ac_amount; ?></td>
                                    </tr>
                                <?php
                            }

                        ?>
                    </tbody>
                </table>
            </div>

            <div class="erp-ac-total-count">
                <table>
                    <tr>
                        <td><strong><?php _e( 'Total', 'erp'); ?></strong></td>
                        <td><strong><?php echo erp_ac_get_price( $assets_total_balance, [ 'symbol' => false ] ); ?></strong></td>
                    </tr>
                </table>

            </div>
        </div>

        <div class="postbox ">
            <h2 class="hndle"><?php _e( 'Liabilities', 'erp' ); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th><strong><?php _e( 'Accounts', 'erp' ); ?></strong></th>
                            <th><strong><?php _e( 'Balance', 'erp' ); ?></strong></th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                            $liabilitie_total_balance = 0;
                            $ac_amount = 0;
                            foreach ( $liabilities as $key => $liabilitie ) {
                                $account = reset( $liabilitie );
                                $debit   = array_sum( wp_list_pluck( $liabilitie, 'debit' ) );
                                $credit  = array_sum( wp_list_pluck( $liabilitie, 'credit' ) );
                                $balance = $credit - $debit;
                                $ac_amount = erp_ac_get_price( $balance, [ 'symbol' => false ] );

                                if ( $balance == 0 ) {
                                    continue;
                                }

                                $liabilitie_total_balance = $liabilitie_total_balance + $balance;

                                ?>
                                    <tr>
                                        <td><?php echo erp_ac_get_account_url( $account->id, $account->name ); ?></td>
                                        <td><?php echo $ac_amount; ?></td>
                                    </tr>
                                <?php
                            }

                        ?>
                    </tbody>
                </table>
            </div>

            <div class="erp-ac-total-count">
                <table>
                    <tr>
                        <td><strong><?php _e( 'Total', 'erp' ); ?></strong></td>
                        <td><strong><?php echo erp_ac_get_price( $liabilitie_total_balance, [ 'symbol' => false ] ); ?></strong></td>
                    </tr>
                </table>

            </div>
        </div>

        <div class="postbox ">
            <h2 class="hndle"><?php _e( 'Equity', 'erp' ); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat">
                    <thead>
                        <tr>
                            <th><strong><?php _e( 'Accounts', 'erp' ); ?></strong></th>
                            <th><strong><?php _e( 'Balance', 'erp' ); ?></strong></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $liabilitie_total_balance = $net_income;

                            foreach ( $equities as $key => $equity ) {
                                $account   = reset( $equity );
                                $debit     = array_sum( wp_list_pluck( $equity, 'debit' ) );
                                $credit    = array_sum( wp_list_pluck( $equity, 'credit' ) );
                                $balance   = $credit - $debit;
                                $ac_amount = erp_ac_get_price( $balance, [ 'symbol' => false ] );

                                if ( $balance == 0 ) {
                                    continue;
                                }

                                $liabilitie_total_balance = $liabilitie_total_balance + $balance;

                                ?>
                                    <tr>
                                        <td><?php echo erp_ac_get_account_url( $account->id, $account->name ); ?></td>
                                        <td><?php echo $ac_amount; ?></td>
                                    </tr>
                                <?php
                            }

                        ?>

                        <tr>
                            <td><?php _e( 'Net Income', 'erp' ) ?></td>
                            <td><?php echo erp_ac_get_price( $net_income, [ 'symbol' => false ] ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="erp-ac-total-count">
                <table>
                    <tr>
                        <td><strong><?php _e( 'Total', 'erp' ); ?></strong></td>
                        <td><strong><?php echo erp_ac_get_price( $liabilitie_total_balance, [ 'symbol' => false ] ); ?></strong></td>
                    </tr>
                </table>

            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<?php

















