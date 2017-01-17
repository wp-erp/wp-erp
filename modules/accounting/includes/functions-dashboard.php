<?php
/**
 * Accounting dashboard widgets for left column
 *
 * @return void
 */
function erp_ac_dashboard_left_column() {
    erp_admin_dash_metabox( __( 'Income & Expenses', 'erp' ), 'erp_ac_dashboard_income_expense' );
    erp_admin_dash_metabox( __( 'Cash & Bank Balance', 'erp' ), 'erp_ac_dashboard_banks', 'bank-balance' );
    erp_admin_dash_metabox( __( 'Invoice payable to you', 'erp' ), 'erp_ac_dashboard_invoice_payable' );
}

/**
 * Accounting dashboard widgets for right column
 *
 * @return void
 */
function erp_ac_dashboard_right_column() {
    erp_admin_dash_metabox( __( 'Business Expense', 'erp' ), 'erp_ac_dashboard_expense_chart' );
    erp_admin_dash_metabox( __( 'Revenues', 'erp' ), 'erp_ac_dashboard_net_income', 'bank-balance' );
    erp_admin_dash_metabox( __( 'Bills you need to pay', 'erp' ), 'erp_ac_dashboard_bills_payable' );
}

/**
 * Dashboard cash and bank
 *
 * @since  1.0
 *
 * @return void
 */
function erp_ac_dashboard_banks() {
    $bank_journals = erp_ac_get_bank_journals();
    $transactions  = erp_ac_get_all_transaction([
        'type'   => ['expense', 'sales', 'journal', 'transfer'],
        'status' => array( 'not_in' => array( 'draft', 'void', 'awaiting_approval' ) ),
        'number' => -1
    ]);

    $transactions_id = wp_list_pluck( $transactions, 'id' );
    $all_journals    = wp_list_pluck( $transactions, 'id' );

    foreach ( $bank_journals as $main_key => $bank_journal ) {

        foreach ( $bank_journal['journals'] as $key => $bank_jour ) {

            if ( ! in_array( $bank_jour['transaction_id'], $transactions_id ) ) {
                unset( $bank_journals[$main_key]['journals'][$key] );
            }
        }
    }

    $ledgers_data = [];
    foreach ( $bank_journals as $key => $bank_journal ) {
        $bank_id          = $bank_journal['id'];
        $labels[$bank_id] = $bank_journal['name'];
        $debit  = array_sum( wp_list_pluck( $bank_journal['journals'], 'debit' ) );
        $credit = array_sum( wp_list_pluck( $bank_journal['journals'], 'credit' ) );
        $total  = $debit - $credit;
        $bank_journals[$key]['total_journal'] = $total;
    }

    $total   = 0;
    $symbole = erp_ac_get_currency_symbol();

    ?>
    <ul>
        <?php foreach ( $bank_journals as $id => $journal ) {
            $total = $total + $journal['total_journal'];
            $bank_url = erp_ac_get_account_url( $journal['id'], $journal['name'] ); //admin_url( 'admin.php?page=erp-accounting-charts&action=view&id=' . $journal['id'] );
            $total_journal = erp_ac_get_account_url( $journal['id'], erp_ac_get_price( $journal['total_journal'] ) );
            ?>
            <li>
                <span class="account-title">
                    <?php echo $bank_url; ?>
                </span>
                <span class="price">
                    <?php echo $total_journal; ?>
                </span>
            </li>
            <?php
        }
        ?>

        <li class="total">
            <span class="account-title"><?php _e( 'Total', 'erp' ); ?></span> <span class="price"><a href="#"><?php echo erp_ac_get_price( $total ); ?></a></span>
        </li>
    </ul>
    <?php
}

/**
 * Dashboard invoice payable
 *
 * @since  1.0
 *
 * @return void
 */
function erp_ac_dashboard_invoice_payable() {
    $first = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $last  = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

    $incomes_args = [
        'start_date' => $first,
        'end_date'   => $last,
        'form_type'  => 'invoice',
        'type'       => 'sales',
        'status'     => ['in' => ['awaiting_payment', 'partial'] ],
        'number'     => -1
    ];

    $invoices = erp_ac_get_all_transaction( $incomes_args );

    $priv_day = date( 'Y-m-d', strtotime( '-1 day', strtotime( current_time( 'mysql' ) ) ) );
    $second   = date( 'Y-m-d', strtotime( '-29 day', strtotime( $priv_day ) ) );
    $third    = date( 'Y-m-d', strtotime( '-30 day', strtotime( $second ) ) );
    $forth    = date( 'Y-m-d', strtotime( '-30 day', strtotime( $third ) ) );

    $first_price  = 0;
    $second_price = 0;
    $third_price  = 0;
    $forth_price  = 0;
    $fifty_price  = 0;
    $symbol       = erp_ac_get_currency_symbol();

    foreach ( $invoices as $key => $invoice ) {

        //Comming due
        if ( $priv_day < $invoice->due_date ) {
            $first_price = $first_price +  $invoice->due;
        }

        //1-30 days overdue
        if ( $priv_day >= $invoice->due_date && $second <= $invoice->due_date ) {
            $second_price = $second_price + $invoice->due;
        }

        //31-60 days overdue
        if ( $second > $invoice->due_date && $third <= $invoice->due_date ) {
            $third_price = $third_price + $invoice->due;
        }

        //61-90 days overdue
        if ( $third > $invoice->due_date && $forth <= $invoice->due_date ) {
            $forth_price = $forth_price + $invoice->due;
        }

        //> 90 days overdue
        if ( $forth > $invoice->due_date  ) {
            $fifty_price = $fifty_price + $invoice->due;
        }
    }

    ?>
    <table>
        <tr>
            <td><?php _e( 'Coming Due', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $first_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '1-30 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $second_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '31-60 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $third_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '61-90 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $forth_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '> 90 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $fifty_price ); ?></td>
        </tr>
    </table>
    <?php
}

/**
 * Dashboard net income
 *
 * @since  1.0
 *
 * @return void
 */
function erp_ac_dashboard_net_income() {

    $trans         = erp_ac_get_transaction_group_by_calss_id( [3,4] );
    $expenses      = isset( $trans[3] ) ? $trans[3] : [];
    $incomes       = isset( $trans[4] ) ? $trans[4] : [];
    $sales_total   = 0;
    $expense_total = 0;

    foreach ( $expenses as $exp ) {
        $expense_total = $expense_total + ( $exp->debit - $exp->credit );
    }

    foreach ( $incomes as $incom ) {
        $sales_total = $sales_total + ( $incom->credit - $incom->debit );
    }

    $net_income = $sales_total - $expense_total;

    ?>
    <ul>
        <li><span class="account-title"><?php _e( 'Income', 'erp' ); ?></span> <span class="price"><a href="<?php echo erp_ac_get_sales_menu_url(); ?>"><?php echo erp_ac_get_price( $sales_total ); ?></a></span></li>
        <li><span class="account-title"><?php _e( 'Expense', 'erp' ); ?></span> <span class="price"><a href="<?php echo erp_ac_get_expense_url(); ?>"><?php echo erp_ac_get_price( $expense_total ); ?></a></span></li>
        <li class="total">
            <span class="account-title"><?php _e( 'Revenues', 'erp' ); ?></span> <span class="price"><?php echo erp_ac_get_price( $net_income ); ?></span>
        </li>
    </ul>
    <?php
}

/**
 * Dashboard income expense bar chart
 *
 * @since  1.0
 *
 * @return void
 */
function erp_ac_dashboard_income_expense() {

    $class_id = [3,4];

    $trans        = erp_ac_get_transaction_group_by_month_from_calss_id( $class_id );
    $current_year = date( 'Y', strtotime( current_time( 'mysql' ) ) );
    $prev_year    = date( 'Y', strtotime( '-1 year', strtotime( current_time( 'mysql' ) ) ) );
    $expenses     = isset( $trans[3] ) ? $trans[3] : [];
    $incomes      = isset( $trans[4] ) ? $trans[4] : [];
    $expense_data = [];
    $income_data  = [];
    $total        = 0;


    foreach ( $expenses as $month => $expense ) {
        $date_ex = strtotime( date( 'Y-' .$month ) ) * 1000;
        $total   = 0;

        foreach ( $expense as $exp ) {
            $total   = $total + ( $exp->debit - $exp->credit );
        }

        $expense_data[$date_ex] = $total;
    }

    $total = 0;

    foreach ( $incomes as $month => $income ) {
        $date_in  = strtotime( date( 'Y-' . $month ) ) * 1000;
        $total    = 0;

        foreach ( $income as $incom ) {
            $total = $total + ( $incom->credit - $incom->debit );
        }

        $income_data[$date_in] = $total;
    }

    if ( ! $income_data ) {
        $date_in               = strtotime( date( 'Y-m-d', strtotime(  $current_year .'-01'  ) ) ) * 1000;
        $income_data[$date_in] = 0;
    }

    if ( ! $expense_data ) {
        $date_ex                = strtotime( date( 'Y-m-d', strtotime(  $current_year .'-01' ) ) ) * 1000;
        $expense_data[$date_ex] = 0;
    }

    $income_plot_data  = json_encode( $income_data );
    $expense_plot_data = json_encode( $expense_data );
    $symbol            = json_encode( erp_ac_get_currency_symbol() );

    ?>
    <script type="text/javascript">
    jQuery(document).ready(function ($) {

        var current_year       = <?php echo $current_year; ?>,
            prev_year          = <?php echo $prev_year; ?>,
            income             = <?php echo $income_plot_data; ?>,
            expense            = <?php echo $expense_plot_data; ?>,
            income_chart_data  = [],
            expense_chart_data = [],
            symbol = <?php echo $symbol; ?>;

        $.each( income, function( date, value ) {
            income_chart_data.push([date, value]);
        } );

        $.each( expense, function( date, value ) {
            expense_chart_data.push([date, value]);
        } );

        var chartData = [
            {
                label: "Income",
                data: income_chart_data,
                shadowSize: 0,
                bars: {
                    show: true,
                    barWidth: 12*24*60*60*300,
                    fill: true,
                    lineWidth: 1,
                    order: 1,
                    fillColor:  "#3483BA"
                },
                color: "#3483BA"
            },
            {
                label: "Expense",
                data: expense_chart_data,
                shadowSize: 0,
                bars: {
                    show: true,
                    barWidth: 12*24*60*60*300,
                    fill: true,
                    lineWidth: 1,
                    order: 2,
                    fillColor:  "#C5D7EE"
                },
                color: "#C5D7EE"
            },
        ];
     
        $.plot($("#income-expense-chart"), chartData, {
            xaxis: {
                min: (new Date(prev_year, 11, 20)).getTime(),
                max: (new Date(current_year, 12, 01)).getTime(),
                mode: "time",
                timeformat: "%b",
                tickSize: [1, "month"],
                monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                tickLength: 0, // hide gridlines
                axisLabel: 'Month',
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                axisLabelPadding: 2
            },
            yaxis: {
                axisLabel: 'Value',
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                axisLabelPadding: 2
            },
            grid: {
                hoverable: true,
                clickable: false,
                borderWidth: 1
            },
            legend: {
                show: false,
                labelBoxBorderColor: "none",
                position: 'nw'
            },
            series: {
                shadowSize: 2
            },
            tooltip: true,
            tooltipOpts: {
                defaultTheme: true,
                content: '%s<br><strong>'+symbol+'%y</strong>'
            },
        });
    });
    </script>

    <div id="income-expense-chart" style="height: 350px; width: 100%;"></div>
    <?php
}

/**
 * Dashboard bill you need to pay
 *
 * @since  1.0
 *
 * @return void
 */
function erp_ac_dashboard_bills_payable() {
    $first = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $last  = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

    $incomes_args = [
        'start_date' => $first,
        'end_date'   => $last,
        'form_type'  => ['vendor_credit'],
        'type'       => ['expense'],
        'status'     => ['in' => ['awaiting_payment', 'partial'] ],
        'number'     => -1
    ];

    $incomes_args = apply_filters( 'erp_ac_bill_payable_arags', $incomes_args );
    $invoices = erp_ac_get_all_transaction( $incomes_args );

    $priv_day = date( 'Y-m-d', strtotime( '-1 day', strtotime( current_time( 'mysql' ) ) ) );
    $second   = date( 'Y-m-d', strtotime( '-29 day', strtotime( $priv_day ) ) );
    $third    = date( 'Y-m-d', strtotime( '-30 day', strtotime( $second ) ) );
    $forth    = date( 'Y-m-d', strtotime( '-30 day', strtotime( $third ) ) );

    $first_price  = 0;
    $second_price = 0;
    $third_price  = 0;
    $forth_price  = 0;
    $fifty_price  = 0;
    $symbol       = erp_ac_get_currency_symbol();

    foreach ( $invoices as $key => $invoice ) {

        //Comming due
        if ( $priv_day < $invoice->due_date ) {
            $first_price = $first_price +  $invoice->due;
        }

        //1-30 days overdue
        if ( $priv_day >= $invoice->due_date && $second <= $invoice->due_date ) {
            $second_price = $second_price + $invoice->due;
        }

        //31-60 days overdue
        if ( $second > $invoice->due_date && $third <= $invoice->due_date ) {
            $third_price = $third_price + $invoice->due;
        }

        //61-90 days overdue
        if ( $third > $invoice->due_date && $forth <= $invoice->due_date ) {
            $forth_price = $forth_price + $invoice->due;
        }

        //> 90 days overdue
        if ( $forth > $invoice->due_date  ) {
            $fifty_price = $fifty_price + $invoice->due;
        }
    }
    ?>
    <table>
        <tr>
            <td><?php _e( 'Coming Due', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $first_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '1-30 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $second_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '31-60 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $third_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '61-90 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $forth_price ); ?></td>
        </tr>
        <tr>
            <td><?php _e( '> 90 days overdue', 'erp' ); ?></td>
            <td class="price"><?php echo erp_ac_get_price( $fifty_price ); ?></td>
        </tr>
    </table>
    <?php
}

/**
 * Dashboard expense pie chart
 *
 * @since  1.0
 *
 * @return void
 */
function erp_ac_dashboard_expense_chart() {

    $ledger_data = [];
    $labels      = [];
    $trans = erp_ac_get_transaction_by_calss_id( [3] );

    foreach ( $trans as $tran ) {
        if ( isset( $ledger_data[$tran->ledger_id] ) ) {
            $ledger_data[$tran->ledger_id] = $ledger_data[$tran->ledger_id] + ( $tran->debit - $tran->credit );
        } else {
            $ledger_data[$tran->ledger_id] = $tran->debit - $tran->credit;
        }

        $labels[$tran->ledger_id] = $tran->ledger_name;
    }

    foreach ( $ledger_data as $key => $amount ) {
        if ( $amount < 0 ) {
            unset( $ledger_data[$key] );
        }
    }

    $no_result = erp_ac_message('no_result');
    ?>
    <script type="text/javascript">
        (function() {
            jQuery(function($) {

                function labelFormatter(label, series) {

                    if ( label === false ) {
                        return "<div style='font-size:10pt; text-align:center; padding:2px; color:white;'>"+no_result+"</div>";
                    } else {
                        return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label + "<br>" + Math.round(series.percent) + "%</div>";
                    }

                }

                var ledgers = <?php echo json_encode( $ledger_data ); ?>,
                    labels  = <?php echo json_encode( $labels ); ?>,
                    no_result = '<?php echo $no_result; ?>',
                    data    = [];

                $.each( ledgers, function( id,val ) {
                    data.push( { label: labels[id], data: val } );
                });

                if ( data.length == 0 ) {
                    var data = [
                        { label: false, data: -1},
                    ],
                    radius = 0.1,
                    content = '',
                    colors = ['#0073aa'];
                } else {
                    var radius = 3/4,
                        content = "%s %p.0%",
                        colors = [ '#1abc9c', '#2ecc71', '#4aa3df', '#9b59b6', '#f39c12', '#d35400', '#2c3e50'];
                }

                $.plot('#expense-pie-chart', data, {
                    series: {
                        pie: {
                            show: true,
                            radius: 1,
                            label: {
                                show: true,
                                radius: radius,
                                formatter: labelFormatter,
                            }
                        }
                    },
                    grid: {
                        hoverable: true
                    },
                    colors: colors,
                    tooltip: true,
                    tooltipOpts: {
                        defaultTheme: false,
                        content: content,
                    },
                    legend: {
                        show: false
                    },
                });
            });
        })();
    </script>

    <div id="expense-pie-chart" style="height: 350px; width: 100%;"></div>
    <?php
}
