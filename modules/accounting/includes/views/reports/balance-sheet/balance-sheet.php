<?php
$ledgers = erp_ac_reporting_query();

foreach ($ledgers as $ledger) {
    $charts[$ledger->class_id][$ledger->id][] = $ledger;
}

$assets      = isset( $charts[1] ) ? $charts[1] : [];
$liabilities = isset( $charts[2] ) ? $charts[2] : [];
$equities    = isset( $charts[5] ) ? $charts[5] : [];

$sales_total   = erp_ac_get_sales_total_without_tax( $charts ) + erp_ac_get_sales_tax_total( $charts );
$goods_sold    = erp_ac_get_good_sold_total_amount( $charts );
$expense_total = erp_ac_get_expense_total_without_tax( $charts );
$expense_total = $expense_total - $goods_sold;
$tax_total     = erp_ac_get_sales_tax_total( $charts ) + erp_ac_get_expense_tax_total( $charts );
$gross         = $sales_total - $goods_sold;
$operating     = $gross - $expense_total;
$net_income    = $operating - $tax_total;

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

















