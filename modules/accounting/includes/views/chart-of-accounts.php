<div class="wrap erp-accounting chart-of-accounts">
    <?php
    if ( erp_ac_create_account() ) {
        ?>
        <h2><?php _e( 'Chart of Accounts', 'erp' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-accounting-charts&action=new' ); ?>" class="add-new-h2"><?php _e( 'Add New', 'erp' ); ?></a></h2>
        <?php
    }
    ?>


    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch ( $_GET['msg'] ) {
            case 'update':
                erp_html_show_notice( __( 'Account has been updated!', 'erp' ) );
                break;

            case 'new':
                erp_html_show_notice( __( 'New account has been added!', 'erp' ) );
                break;
        }
    }

    $charts     = [];
    $all_charts = erp_ac_get_all_chart( [ 'number' => -1 ]);

    foreach ( $all_charts as $chart ) {
        $charts[ $chart->class_id ][] = $chart;
    }

    $charat_1 = isset( $charts['1'] ) ? $charts['1'] : array();
    $charat_2 = isset( $charts['2'] ) ? $charts['2'] : array();
    $charat_3 = isset( $charts['3'] ) ? $charts['3'] : array();
    $charat_4 = isset( $charts['4'] ) ? $charts['4'] : array();
    $charat_5 = isset( $charts['5'] ) ? $charts['5'] : array();

    erp_ac_chart_print_table( __( 'Assets', 'erp' ), $charat_1 );
    erp_ac_chart_print_table( __( 'Liabilities', 'erp' ), $charat_2 );
    erp_ac_chart_print_table( __( 'Expenses', 'erp' ),$charat_3 );
    erp_ac_chart_print_table( __( 'Income', 'erp' ), $charat_4 );
    erp_ac_chart_print_table( __( 'Equity', 'erp' ), $charat_5 );
    ?>
</div>