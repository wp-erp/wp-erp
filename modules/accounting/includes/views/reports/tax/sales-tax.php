<div class="wrap erp-ac-tax-report-wrap">

    <h1><?php _e( 'Sales Tax Summary', 'erp' ); ?></h1>
    <div class="erp-ac-trial-report-header-wrap">
        <p class="erp-ac-report-tax-date">
        <?php
            //$start = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) );
            //$end   = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) );
            printf( '<i class="fa fa-calendar"></i> %1$s', erp_format_date( $end, 'F j, Y' ) );
        ?>
        </p>

        <?php erp_ac_report_filter_form(false); ?>
    </div>
    <table class="wp-list-table widefat fixed striped erp-ac-tax-report-table">
        <thead>
            <tr>
                <th><?php _e( 'Tax', 'erp' ); ?></th>
                <th><?php _e( 'Tax Payable', 'erp' ); ?></th>
                <th><?php _e( 'Tax Receivable', 'erp' ); ?></th>
                <th><?php _e( 'Net Tax', 'erp' ); ?></th>
                <th><?php _e( 'Balance', 'erp' ); ?></th>
            </tr>

        </thead>
        <tbody>

            <?php
            $balance = 0;
            foreach ( $taxs as $tax_id => $tax ) {
                $net_sales_tax   =  isset( $tax['sales']['amount'] ) ? $tax['sales']['amount'] : 0;
                $net_expense_tax =  isset( $tax['expense']['amount'] ) ? $tax['expense']['amount'] : 0;
                $tax_name        =  isset( $tax['sales']['tax_name'] ) ? $tax['sales']['tax_name'] : $tax['expense']['tax_name'];
                $tax_rate        =  isset( $tax['sales']['rate'] ) ? $tax['sales']['rate'] : $tax['expense']['rate'];
                $net_tax         =  $net_sales_tax + $net_expense_tax;
                $balance         =  $balance + $net_tax;
                ?>
                    <tr>
                        <td>
                            <a href="<?php echo erp_ac_get_singe_tax_report_url( $tax_id, $end ); ?>">
                                <?php echo $tax_name . ' (' . $tax_rate . '%)'; ?>
                            </a>
                        </td>
                        <td><?php echo erp_ac_get_price( $net_sales_tax ); ?></td>
                        <td><?php echo erp_ac_get_price( $net_expense_tax ); ?></td>
                        <td><?php echo erp_ac_get_price( $net_tax ); ?></td>
                        <td><?php echo erp_ac_get_price( $balance ); ?></td>

                    </tr>
                <?php
            }

            if ( ! $taxs ) {
                ?>
                <tr><td colspan="6"><?php _e( 'No Result Found!', 'erp' ); ?></td></tr>
                <?php
            }
            ?>


        </tbody>
    </table>

</div>
