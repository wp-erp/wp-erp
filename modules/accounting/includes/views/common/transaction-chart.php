<?php
$screen = get_current_screen();
$symbol = erp_ac_get_currency_symbol();
$financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
$financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

$hook = str_replace( sanitize_title( __( 'Accounting', 'erp' ) ) , 'accounting', $screen->base );

if ( $hook == 'accounting_page_erp-accounting-sales'  ) {
    $transactions = erp_ac_get_all_transaction([
        'type'       => ['sales'],
        'status'     => ['in' => ['draft','closed', 'partial', 'awaiting_payment']],
        'output_by'  => 'array',
        'join'       => ['payments'],
        'start_date' =>  date( 'Y-m-d', strtotime( erp_financial_start_date() ) ),
        'end_date'   => date( 'Y-m-d', strtotime( erp_financial_end_date() ) ),
        'number'     => -1,
        //'form_type'  => [ 'in' => ['payment'] ]
    ]);

    $current_time = current_time( 'mysql' );
    $outstanding  = 0;
    $overdue      = 0;
    $draft        = 0;
    $paid         = 0;
    $partial      = 0;
    $received     = 0;

    foreach( $transactions as $key => $transaction ) {

        if ( 'draft' == $transaction['status'] ) {
            $draft = $draft + 1;
            continue;
        }

        if ( ( date( 'Y-m-d', strtotime( $current_time ) ) > date( 'Y-m-d', strtotime( $transaction['due_date'] ) ) )  && $transaction['due'] > 0 ) {
            $overdue     = $overdue + 1;
        }

        if ( '0' == $transaction['due'] ) {
            $paid = $paid + 1;
        }

        if ( 'partial' == $transaction['status'] ) {
            $partial  = $partial + 1;
        }

        if ( $transaction['status'] == 'partial' ) {
            $received = $received + $transaction['due'];
        } else if ( $transaction['status'] != 'draft' ) {
            $received = $received + $transaction['trans_total'];
        }

        $outstanding = $transaction['due'] + $outstanding;

    }

    $payment_received[] = [
        'label' => __( 'Received', 'erp' ),
        'data'  =>  $received,
        'color' => '#A3C716'
    ];

    $payment_received[] = [
        'label' => __( 'Outstanding', 'erp' ),
        'data'  => $outstanding,
        'color' => '#B7C9D1'
    ];

    $payment_status[] = [
        'label' => __( 'Paid', 'erp' ),
        'data'  => $paid,
        'color' => '#A3C716'
    ];

    $payment_status[] = [
        'label' => __( 'Overdue', 'erp' ),
        'data'  => $overdue,
        'color' => '#DB4F4F'
    ];

    $payment_status[] = [
        'label' => __( 'Partial', 'erp' ),
        'data'  => $partial,
        'color' => '#E1C518'
    ];

    $payment_status[] = [
        'label' => __( 'Draft', 'erp' ),
        'data'  => $draft,
        'color' => '#6C90A2'
    ];

} else if ( $hook == 'accounting_page_erp-accounting-expense' ) {

    $transactions = erp_ac_get_all_transaction([
        'type'       => ['expense'],
        'status'     => ['in' => ['draft','closed', 'partial', 'awaiting_payment', 'paid']],
        'output_by'  => 'array',
        'number'     => -1,
        'join'       => ['payments'],
        'start_date' =>  date( 'Y-m-d', strtotime( erp_financial_start_date() ) ),
        'end_date'   => date( 'Y-m-d', strtotime( erp_financial_end_date() ) )
       // 'form_type' => [ 'in' => ['payment_voucher'] ]
    ]);

    $current_time = current_time( 'mysql' );
    $outstanding  = 0;
    $overdue      = 0;
    $draft        = 0;
    $expense      = 0;
    $partial      = 0;
    $received     = 0;

    foreach( $transactions as $key => $transaction ) {

        if ( 'draft' == $transaction['status'] ) {
            $draft = $draft + 1;
            continue;
        }

        if ( ( date( 'Y-m-d', strtotime( $current_time ) ) > date( 'Y-m-d', strtotime( $transaction['due_date'] ) ) ) && $transaction['due'] > 0 ) {
            $overdue  = $overdue + 1;
        }

        if ( '0' == $transaction['due'] ) {
            $expense = $expense + 1;
        }

        if ( 'partial' == $transaction['status'] ) {
            $partial  = $partial + 1;
        }

        if ( $transaction['status'] == 'paid' ) {
            $received = $received + $transaction['trans_total'];
        }

        $outstanding = $transaction['due'] + $outstanding;
    }

    $payment_received[] = [
        'label' => __( 'Paid', 'erp' ),
        'data'  =>  $received,
        'color' => '#A3C716'
    ];

    $payment_received[] = [
        'label' => __( 'Outstanding', 'erp' ),
        'data'  =>  $outstanding,
        'color' => '#B7C9D1'
    ];

    $payment_status[] = [
        'label' => __( 'Paid', 'erp' ),
        'data'  => $expense,
        'color' => '#A3C716'
    ];

    $payment_status[] = [
        'label' => __( 'Overdue', 'erp' ),
        'data'  => $overdue,
        'color' => '#DB4F4F'
    ];

    $payment_status[] = [
        'label' => __( 'Partial', 'erp' ),
        'data'  => $partial,
        'color' => '#E1C518'
    ];

    $payment_status[] = [
        'label' => __( 'Draft', 'erp' ),
        'data'  => $draft,
        'color' => '#6C90A2'
    ];

} else if ( $hook == 'accounting_page_erp-accounting-customers' ) {

    $transactions = erp_ac_get_all_transaction([
        'type'       => ['sales'],
        'status'     => ['in' => ['draft', 'closed', 'partial', 'awaiting_payment']],
        'output_by'  => 'array',
        'start_date' =>  date( 'Y-m-d', strtotime( erp_financial_start_date() ) ),
        'end_date'   => date( 'Y-m-d', strtotime( erp_financial_end_date() ) ),
        'number'     => -1,
        'join'       => ['payments'],
        'user_id'    => isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0,
    ]);

    $current_time = current_time( 'mysql' );
    $outstanding  = 0;
    $overdue      = 0;
    $draft        = 0;
    $paid         = 0;
    $partial      = 0;
    $received     = 0;

    foreach( $transactions as $key => $transaction ) {
        if ( 'draft' == $transaction['status'] ) {
            $draft = $draft + 1;
            continue;
        }

        $outstanding = $transaction['due'] + $outstanding;

        if ( ( date( 'Y-m-d', strtotime( $current_time ) ) > date( 'Y-m-d', strtotime( $transaction['due_date'] ) ) ) && $transaction['due'] > 0 ) {
            $overdue     = $overdue + 1;
        }

        if ( '0' == $transaction['due'] && 'sales' == $transaction['type'] ) {
            $paid = $paid + 1;
        }

        if ( 'partial' == $transaction['status'] ) {
            $partial  = $partial + 1;
        }

        if ( $transaction['status'] == 'partial' ) {
            $received = $received + $transaction['due'];
        } else if ( $transaction['status'] != 'draft' ) {
            $received = $received + $transaction['trans_total'];
        }
    }

    $payment_received[] = [
        'label' => __( 'Received', 'erp' ),
        'data'  =>  $received,
        'color' => '#A3C716'
    ];

    $payment_received[] = [
        'label' => __( 'Outstanding', 'erp' ),
        'data'  =>  $outstanding,
        'color' => '#B7C9D1'
    ];

    $payment_status[] = [
        'label' => __( 'Paid', 'erp' ),
        'data'  => $paid,
        'color' => '#A3C716'
    ];

    $payment_status[] = [
        'label' => __( 'Overdue', 'erp' ),
        'data'  => $overdue,
        'color' => '#DB4F4F'
    ];

    $payment_status[] = [
        'label' => __( 'Partial', 'erp' ),
        'data'  => $partial,
        'color' => '#E1C518'
    ];

    $payment_status[] = [
        'label' => __( 'Draft', 'erp' ),
        'data'  => $draft,
        'color' => '#6C90A2'
    ];

} else if ( $hook == 'accounting_page_erp-accounting-vendors' ) {
    $transactions = erp_ac_get_all_transaction([
        'type'       => ['expense'],
        'status'     => ['in' => ['draft', 'closed', 'partial', 'awaiting_payment']],
        'output_by'  => 'array',
        'join'       => ['payments'],
        'number'     => -1,
        'start_date' =>  date( 'Y-m-d', strtotime( erp_financial_start_date() ) ),
        'end_date'   => date( 'Y-m-d', strtotime( erp_financial_end_date() ) ),
       // 'form_type'  => 'vendor_credit',
        'user_id'    => isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0
    ]);

    $current_time = current_time( 'mysql' );
    $outstanding  = 0;
    $overdue      = 0;
    $draft        = 0;
    $expense      = 0;
    $partial      = 0;
    $received     = 0;

    foreach( $transactions as $key => $transaction ) {

        if ( 'draft' == $transaction['status'] ) {
            $draft = $draft + 1;
            continue;
        }

        if ( ( date( 'Y-m-d', strtotime( $current_time ) ) > date( 'Y-m-d', strtotime( $transaction['due_date'] ) ) ) && $transaction['due'] > 0 ) {
            $overdue  = $overdue + 1;
        }

        if ( '0' == $transaction['due'] ) {
            $expense = $expense + 1;
        }

        if ( 'partial' == $transaction['status'] ) {
            $partial  = $partial + 1;
        }

        if ( $transaction['status'] == 'partial' ) {
            $received = $received + $transaction['due'];
        } else if ( $transaction['status'] != 'draft' ) {
            $received = $received + $transaction['trans_total'];
        }

        $outstanding = $transaction['due'] + $outstanding;

    }

    $payment_received[] = [
        'label' => __( 'Expense', 'erp' ),
        'data'  => $received,
        'color' => '#A3C716'
    ];

    $payment_received[] = [
        'label' => __( 'Outstanding', 'erp' ),
        'data'  => $outstanding,
        'color' => '#B7C9D1'
    ];

    $payment_status[] = [
        'label' => __( 'Paid', 'erp' ),
        'data'  => $expense,
        'color' => '#A3C716'
    ];

    $payment_status[] = [
        'label' => __( 'Overdue', 'erp' ),
        'data'  => $overdue,
        'color' => '#DB4F4F'
    ];

    $payment_status[] = [
        'label' => __( 'Partial', 'erp' ),
        'data'  => $partial,
        'color' => '#E1C518'
    ];

    $payment_status[] = [
        'label' => __( 'Draft', 'erp' ),
        'data'  => $draft,
        'color' => '#6C90A2'
    ];
}

$payment_received = json_encode( $payment_received );
$payment_status   = json_encode( $payment_status );

?>
<div class="payment-stat-chart">
    <div class="payment-metrics">
        <span class="title"><?php _e( 'Payments', 'erp' ); ?></span>
        <div id="payment-received-stat"></div>
    </div>

    <div class="payment-metrics">
        <span class="title"><?php _e( 'Status', 'erp' ); ?></span>
        <div id="payment-status-stat"></div>
    </div>

    <div class="payment-metrics single-metric">
        <span class="title"><?php _e( 'Total Outstanding Payments', 'erp' ); ?></span>
        <span class="value"><?php echo erp_ac_get_price( $outstanding ); ?></span>
    </div>
</div>
<?php $symbol         = json_encode( $symbol ); ?>
<script type="text/javascript">
    (function($) {

        $(document).ready( function() {
            ERP_AC_paymentReceived = <?php echo $payment_received; ?>;
            ERP_AC_paymentStatus = <?php echo $payment_status; ?>;
            ERP_AC_symbol = <?php echo $symbol; ?>;

            $.plot('#payment-received-stat', ERP_AC_paymentReceived, {
                series: {
                    pie: {
                        innerRadius: 0.5,
                        show: true
                    }
                },
                grid: {
                    hoverable: true,
                    clickable: false,
                    borderWidth: 1
                },
                tooltip: true,
                tooltipOpts: {
                    defaultTheme: true,
                    content: "%s - "+ERP_AC_symbol+"%n",
                },
                legend: {
                    show: true,
                    labelFormatter: function(label, series) {

                        return ERP_AC_symbol + series.data[0][1] + '<br>' + label;
                    }
                },
            });

            $.plot('#payment-status-stat', ERP_AC_paymentStatus, {
                series: {
                    pie: {
                        innerRadius: 0.5,
                        show: true
                    }
                },
                grid: {
                    hoverable: true,
                    clickable: false,
                    borderWidth: 1
                },
                tooltip: true,
                tooltipOpts: {
                    defaultTheme: true,
                    content: "<strong>%n</strong> %s",
                },
                legend: {
                    show: true,
                    labelFormatter: function(label, series) {
                        return '<strong>' + series.data[0][1] + '</strong> ' + label;
                    }
                }
            });
        });
    })(jQuery);
</script>
