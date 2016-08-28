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
    erp_admin_dash_metabox( __( 'Net Income', 'erp' ), 'erp_ac_dashboard_net_income', 'bank-balance' );
    erp_admin_dash_metabox( __( 'Bills you need to pay', 'erp' ), 'erp_ac_dashboard_bills_payable' );
}

function erp_ac_dashboard_banks() {
    $bank_journals = erp_ac_get_bank_journals();
    $transactions = erp_ac_get_all_transaction([
        'type'   => ['expense', 'sales', 'journal'],
        'status' => array( 'not_in' => array( 'draft' ) )
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

function erp_ac_dashboard_invoice_payable() {

    $incomes_args = [
        'form_type'  => 'invoice',
        'type'       => 'sales',
        'status'     => 'awaiting_payment',
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

function erp_ac_dashboard_net_income() {

    $incomes_args = [
        'join'       => ['journals'],
        'type'       => ['sales', 'expense', 'journal'],
        'status'     => ['not_in' => 'draft'],
        'groupby'    => 'type',
        'output_by'  => 'array'

    ];

    $income = 0;
    $expense = 0;

    $transections = erp_ac_get_all_transaction( $incomes_args );
    $sales        = isset( $transections['sales'] ) ? $transections['sales'] : [];
    $expenses     = isset( $transections['expense'] ) ? $transections['expense'] : [];
    $journals_tr  = isset( $transections['journal'] ) ? $transections['journal'] : [];

    $income_ledger_attr  = erp_ac_get_ledger_by_class_id( 4 );
    $income_ledgers      = wp_list_pluck( $income_ledger_attr, 'id' );

    $expense_ledger_attr = erp_ac_get_ledger_by_class_id( 3 );
    $expense_ledgers     = wp_list_pluck( $expense_ledger_attr, 'id' );

    $expense_tax_ledgers = erp_ac_get_tax_receivable_ledger();
    $expense_tax_ledgers = wp_list_pluck( $expense_tax_ledgers, 'id' );

    $inc_exp_info = [];

    foreach (  $journals_tr as $key => $journal_tr ) {

        foreach ( $journal_tr['journals'] as $jor_key => $jor_attr ) {
            if ( in_array( $jor_attr['ledger_id'], $income_ledgers ) ) {
                $income = $income + $jor_attr['debit'] - $jor_attr['credit'];
            }

            if ( in_array( $jor_attr['ledger_id'], $expense_ledgers ) ) {
                $ex = ( $jor_attr['debit'] - $jor_attr['credit'] );
                $expense = $expense +  $ex;
            }
        }
    }

    foreach ( $sales as $key => $arrs ) {
        $credit = [];
        foreach ( $arrs['journals'] as $key => $journal ) {
            if ( $journal['type'] == 'main' ) {
                $main_id = $journal['ledger_id'];
            }

            if ( in_array( $journal['ledger_id'], $income_ledgers ) && $journal['type'] != 'main' ) {
                $credit[] = $journal['credit'];
            }
        }

        $inc_exp_info[$main_id]['credit'] = $credit;
    }

    foreach ( $expenses as $key => $arrs ) {
        $debit = [];
        foreach ( $arrs['journals'] as $key => $journal ) {
            if ( in_array( $journal['ledger_id'], $expense_tax_ledgers ) ) {
                continue;
            }

            if ( $journal['type'] == 'main' ) {
                $main_id = $journal['ledger_id'];
            }

            if ( in_array( $journal['ledger_id'], $expense_ledgers ) && $journal['type'] != 'main' ) {
                $debit[] = $journal['debit'];
            }
        }

        $inc_exp_info[$main_id]['debit'] = $debit;
    }

    foreach ( $inc_exp_info as $inc_exp ) {
        $credit_arr = isset( $inc_exp['credit'] ) ? $inc_exp['credit'] : array();
        $debit_arr  = isset( $inc_exp['debit'] ) ? $inc_exp['debit'] : array();
        $income     = $income + array_sum( $credit_arr );
        $expense    = $expense + array_sum( $debit_arr );
    }
    $expense = ( $expense <= 0 ) ? 0 : $expense;
    $net_income = $income - $expense;
    ?>
    <ul>
        <li><span class="account-title"><?php _e( 'Income', 'erp' ); ?></span> <span class="price"><a href="#"><?php echo erp_ac_get_price( $income ); ?></a></span></li>
        <li><span class="account-title"><?php _e( 'Expense', 'erp' ); ?></span> <span class="price"><a href="#"><?php echo erp_ac_get_price( $expense ); ?></a></span></li>
        <li class="total">
            <span class="account-title"><?php _e( 'Net Income', 'erp' ); ?></span> <span class="price"><a href="#"><?php echo erp_ac_get_price( $net_income ); ?></a></span>
        </li>
    </ul>
    <?php
}

function erp_ac_dashboard_income_expense() {

    $db    = new \WeDevs\ORM\Eloquent\Database();
    $first = date( 'Y-01-01', strtotime( current_time( 'mysql' ) ) );
    $last  = date( 'Y-12-t', strtotime( current_time( 'mysql' ) ) );

    $incomes_args = [
        'join'       => ['journals'],
        'start_date' => $first,
        'end_date'   => $last,
        //'form_type'  => 'payment',
        'type'       => [ 'sales', 'journal' ],
        'status'     => ['not_in' => 'draft'],
        'select'     => [ '*', $db->raw( 'MONTHNAME( issue_date ) as month' ) ],
        'groupby'    => 'month',
        'output_by'  => 'array'
    ];

    $expense_args = [
        'join'       => ['journals'],
        'start_date' => $first,
        'end_date'   => $last,
        //'form_type'  => 'payment_voucher',
        'type'       => [ 'expense', 'journal' ],
        'status'     => ['not_in' => 'draft'],
        //'status'     => 'paid',
        'select'     => [ '*', $db->raw( 'MONTHNAME( issue_date ) as month' ) ],
        'groupby'    => 'month',
        'output_by'  => 'array'
    ];

    $current_year = date( 'Y', strtotime( current_time( 'mysql' ) ) );
    $prev_year    = date( 'Y', strtotime( '-1 year', strtotime( current_time( 'mysql' ) ) ) );

    $incomes        = erp_ac_get_all_transaction( $incomes_args );
    $income_ledgers = erp_ac_get_ledger_by_class_id( 4 );
    $income_ledgers = wp_list_pluck( $income_ledgers, 'id' );

    $expenses            = erp_ac_get_all_transaction( $expense_args );
    $expense_ledgers     = erp_ac_get_ledger_by_class_id( 3 );
    $expense_ledgers     = wp_list_pluck( $expense_ledgers, 'id' );
    $expense_tax_ledgers = erp_ac_get_tax_receivable_ledger();
    $expense_tax_ledgers = wp_list_pluck( $expense_tax_ledgers, 'id' );

    $expense_data = [];
    $income_data  = [];

    foreach ( $expenses as $key => $expense ) {
        $ex_month = date_parse( $key );
        $date_ex  = strtotime( date( 'Y-m-d', strtotime(  $current_year .'-'. $ex_month['month']  ) ) ) * 1000;
        $total    = 0;

        foreach ( $expense as $key => $journal_val ) {

            foreach ( $journal_val['journals'] as $key => $journal ) {

                if ( ! in_array( $journal['ledger_id'], $expense_ledgers ) ) {
                    continue;
                }

                if ( in_array( $journal['ledger_id'], $expense_tax_ledgers ) ) {
                    continue;
                }
                $ex = $journal['debit'] - $journal['credit'];
                $total = $total + $ex;
            }
        }

        $expense_data[$date_ex] = ( $total <= 0 ) ? 0 : $total;
    }

    //echo '<pre>'; print_r($incomes); echo '</pre>'; die();
    foreach ($incomes as $key => $income ) {
        $in_month = date_parse( $key );
        $date_in  = strtotime( date( 'Y-m-d', strtotime(  $current_year .'-'. $in_month['month']  ) ) ) * 1000;
        $total    = 0;

        foreach ( $income as $key => $journal_val ) {

            foreach ( $journal_val['journals'] as $key => $journal ) {

                if ( ! in_array( $journal['ledger_id'], $income_ledgers ) ) {
                    continue;
                }
                $total = $total + $journal['debit'] - $journal['credit'];
            }
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


function erp_ac_dashboard_bills_payable() {
    $incomes_args = [
        'form_type'  => 'vendor_credit',
        'type'       => 'expense',
        'status'     => 'awaiting_payment',
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

function erp_ac_dashboard_expense_chart() {
    $first  = date( 'Y-01-01', strtotime( current_time( 'mysql' ) ) );
    $second = date( 'Y-12-t', strtotime( current_time( 'mysql' ) ) );
    $db     = new \WeDevs\ORM\Eloquent\Database();

    $expense_args = [
        'join'       => ['journals'],
        'start_date' => $first,
        'end_date'   => $second,
        //'form_type'  => 'payment_voucher',
        'type'       => ['expense', 'journal'],
        'status'     => ['not_in' => 'draft'],
        'select'     => [ '*', $db->raw( 'MONTHNAME( issue_date ) as month' ) ],
        'groupby'    => 'month',
        'output_by'  => 'array'
    ];

    $expenses            = erp_ac_get_all_transaction( $expense_args );
    $expense_ledger_attr = erp_ac_get_ledger_by_class_id( 3 );
    $expense_ledgers     = wp_list_pluck( $expense_ledger_attr, 'id' );
    $expense_tax_ledgers = erp_ac_get_tax_receivable_ledger();
    $expense_tax_ledgers = wp_list_pluck( $expense_tax_ledgers, 'id' );
    $labels              = [];

    foreach ( $expense_ledger_attr as $expense_acc ) {
        $labels[$expense_acc->id] = $expense_acc->name;
    }

    $expense_data = [];

    foreach ( $expenses as $key => $expense ) {

        foreach ( $expense as $key => $journal_val ) {

            foreach ( $journal_val['journals'] as $key => $journal ) {

                if ( ! in_array( $journal['ledger_id'], $expense_ledgers ) ) {
                    continue;
                }

                if ( in_array( $journal['ledger_id'], $expense_tax_ledgers ) ) {
                    continue;
                }

                $expense_data[$journal['ledger_id']][] = ($journal['debit'] - $journal['credit']) <= 0 ? 0 : ($journal['debit'] - $journal['credit']);
            }
        }
    }
    $ledger_data = [];

    foreach ( $expense_data as $id => $ledg_data ) {
        if ( array_sum( $ledg_data ) <= 0 ) {
            continue;
        }

        $ledger_data[$id] = array_sum( $ledg_data );
    }

    ?>
    <script type="text/javascript">
        (function() {
            jQuery(function($) {

                function labelFormatter(label, series) {
                    return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label + "<br>" + Math.round(series.percent) + "%</div>";
                }

                var ledgers = <?php echo json_encode( $ledger_data ); ?>,
                    labels  = <?php echo json_encode( $labels ); ?>,
                    data    = [];

                $.each( ledgers, function( id,val ) {
                    data.push( { label: labels[id], data: val } );
                });

                if ( data.length == 0 ) {
                    var data = [
                        { label: "No Result Found!",  data: 11.1},
                    ];
                }

                $.plot('#expense-pie-chart', data, {
                    series: {
                        pie: {
                            show: true,
                            radius: 1,
                            label: {
                                show: true,
                                radius: 3/4,
                                formatter: labelFormatter
                            }
                        }
                    },
                    grid: {
                        hoverable: true
                    },
                    colors: [ '#1abc9c', '#2ecc71', '#4aa3df', '#9b59b6', '#f39c12', '#d35400', '#2c3e50'],
                    tooltip: true,
                    tooltipOpts: {
                        defaultTheme: false,
                        content: "%s %p.0%",
                    },
                    legend: {
                        show: false
                    }
                });
            });
        })();
    </script>

    <div id="expense-pie-chart" style="height: 350px; width: 100%;"></div>
    <?php
}
