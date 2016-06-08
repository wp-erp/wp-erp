<div class="wrap erp-ac-bank-account-wrap">
    <h2><?php _e( 'Bank Account', 'erp' ); ?></h2>

    <div class="bank-accounts">
    <?php
    $banks      = erp_ac_get_bank_account();
    $db         = new \WeDevs\ORM\Eloquent\Database();
    $start_date = erp_financial_start_date();
    $end_date   = erp_financial_end_date();

    foreach ( $banks as $key => $bank ) {

        $ledger_id = $bank['id'];

        $transactions = WeDevs\ERP\Accounting\Model\Transaction::select( [ '*', $db->raw( 'DATE( issue_date ) as `created`' ) ] )
                        ->with( [ 'journals' => function( $q ) use( $ledger_id ) {
                            $q->ofLedger( $ledger_id );
                        } ] )->where('issue_date', '>=', $start_date )->get()->groupBy('created');
        $plot_date     = [];
        $plot_data     = [];
        $amount_totals = [];

        foreach ( $transactions as $date => $transaction ) {
            $debit  = [];
            $credit = [];

            foreach ( $transaction as $key => $value ) {
                $value = $value->toArray();

                $journals = is_array ( $value['journals'] ) && count( $value['journals'] ) ? $value['journals'] : false;

                if ( ! $journals ) {
                    continue;
                }

                $debit  = array_sum( wp_list_pluck( $journals, 'debit' ) );
                $credit = array_sum( wp_list_pluck( $journals, 'credit' ) );
                $total  = $debit - $credit;
                $amount_totals[$date][] = $total;
            }

        }

        $plot_date = [];

        foreach ( $amount_totals as $date => $date_val ) {
            $str_date             = strtotime($date)*1000;
            $plot_date[$str_date] = array_sum( $date_val );
        }

        ksort( $plot_date );

        $bank_id             = $bank['id'];
        $price[$bank_id]     =  $plot_date;
        $bank_name[$bank_id] = $bank['name'];

        ?>

        <div class="bank-account postbox">
            <h3 class="hndle">
                <span class="title erp-ac-bank-name" data-bank_id="<?php echo $bank['id']; ?>"><?php echo esc_html( $bank['name'] ); ?></span>
                <span class="erp-ac-label-bank-balance"><?php _e( 'Balance: ' ); echo erp_ac_get_price( array_sum( $plot_date ) ); ?></span>

                <span class="pull-right">
                    <?php
                        if ( erp_ac_create_sales_payment() || erp_ac_publish_sales_payment() ) {
                            ?>
                            <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=erp-accounting-sales&action=new&type=payment&receive_payment=true&bank='.$bank['id']); ?>"><?php _e( 'Receive Money', 'erp' ); ?></a>
                            <?php
                        }
                        if ( erp_ac_create_expenses_voucher() || erp_ac_publish_expenses_voucher() ) {
                            ?>
                            <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=erp-accounting-expense&action=new&type=payment_voucher&spend_money=true&bank='.$bank['id']); ?>"><?php _e( 'Spend Money', 'erp' ); ?></a>
                            <?php
                        }

                    if ( erp_ac_create_bank_transfer() ) {
                        ?>
                        <a class="add-new-h2 erp-ac-transfer-money-btn" href="#"><?php _e( 'Transfer Money', 'erp' ); ?></a>
                        <?php
                    }
                    ?>
                </span>
            </h3>

            <div class="inside">
                <p class="erp-ac-bank-account-number"><?php _e( 'Account No: ' ); echo isset( $bank['bank_details']['account_number'] ) ? $bank['bank_details']['account_number'] : ''; ?></p>

                <div id="placeholder-<?php echo $bank_id; ?>" class="demo-placeholder" style="height: 200px; width: 100%;"></div>

            </div>
        </div>
    <?php } ?>
    </div>
</div>
<?php

$date_attr = json_encode( $price );
$bank_name = json_encode( $bank_name );
$symbol    = json_encode( erp_ac_get_currency_symbol() );
$xlabel = __( 'Date', 'erp' );
$ylabel = __( 'Amount', 'erp' );
?>

<script type="text/javascript">

    jQuery(function($) {
        var data = <?php echo $date_attr; ?>,
            bank_name = <?php echo $bank_name; ?>,
            symbol = <?php echo $symbol; ?>;
            xlabel = "<?php echo $xlabel; ?>",
            ylabel = "<?php echo $ylabel; ?>";


        $.each( data, function( bank_id, data ) {
            var make_array = [];
            $.each( data, function( key, val ) {
                make_array.push([key,val]);
            } );
            erp_ac_drow_plot(bank_id, make_array);
        } );


        function erp_ac_drow_plot(id, data) {

            var d = [{
                //label: bank_name[id],
                data: data,
                yaxis: 2,
                color: '#3498db',
                points: { show: true, radius: 4, lineWidth: 4, fillColor: '#fff', fill: true },
                lines: { show: true, lineWidth: 5, fill: false },
                shadowSize: 0,
                prepend_tooltip: "&#36;"
            }];

            for (var i = 0; i < d.length; ++i) {
                d[i][0] += 60 * 60 * 1000;
            }

            var options = {
                xaxis: {
                    mode: "time",
                    tickLength: 5,
                    color: '#b1d4ea',
                   // axisLabel : xlabel,
                },
                yaxis: {
                   // axisLabel: ylabel,
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                    axisLabelPadding: 2,

                },
                zoom: {
                   // interactive: false
                },
                pan: {
                   interactive: true
                },
                // selection: {
                //     mode: "xy",
                //     color: '#b1d4ea',
                // },
                grid: {
                    color: '#aaa',
                    borderColor: 'transparent',
                    borderWidth: 0,
                    hoverable: true
                },
                tooltip: true,
                tooltipOpts: {
                    defaultTheme: true,
                    content: "<strong>"+symbol+"</strong>%y",
                },


            };

            var plot = $.plot("#placeholder-"+id, d, options);
        }
    });

</script>


