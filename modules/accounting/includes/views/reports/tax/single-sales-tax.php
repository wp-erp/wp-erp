<div class="wrap erp-ac-tax-report-wrap">

    <h2><?php _e( 'Sales Tax Summary', 'erp' ); ?>
        <a class="add-new-h2" href="<?php echo erp_ac_get_sales_tax_report_url(); ?>">&#8592;<?php _e( 'Back', 'erp' ); ?></a>
    </h2>
    <div class="erp-ac-trial-report-header-wrap">
        <p class="erp-ac-report-tax-date erp-ac-tax-name">
            <?php printf( '<i class="fa fa-calculator"></i> %1$s (%2$s%3$s)', $taxinfo[$tax_id]['name'], $taxinfo[$tax_id]['rate'], '%' ); ?>
        </p>

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
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Transaction ID', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Transaction Date', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Tax Payable', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Tax Receivable', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Net Tax', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Balance', 'erp' ); ?></th>
            </tr>

        </thead>
        <tbody>


        <?php
            $balance = 0;
            $sales_taxs = isset( $taxs['sales'] ) ? $taxs['sales'] : [];

            foreach ( $taxs as $transaction_id => $trans ) {
                $receivable = isset( $trans['receivable'] ) ? $trans['receivable'] : 0;
                $payable    = isset( $trans['payable'] ) ? $trans['payable'] : 0;
                $net_tax    = $receivable + $payable;
                $balance = $balance + $net_tax;
                ?>
                <tr>
                    <td>
                        <a data-transaction_id="<?php echo $transaction_id; ?>" class="erp-ac-transaction-report" href="<?php echo erp_ac_get_expense_voucher_url( $transaction_id ); ?>">#<?php echo $transaction_id; ?></a>
                    </td>
                    <td><?php echo erp_format_date( $trans['issue_date'] ); ?></td>
                    <td><?php echo erp_ac_get_price( $payable ); ?></td>
                    <td><?php echo erp_ac_get_price( $receivable ); ?></td>
                    <td><?php echo erp_ac_get_price( $net_tax ); ?></td>
                    <td><?php echo erp_ac_get_price( $balance ); ?></td>
                </tr>

                <?php
            }

            if ( ! count( $taxs ) ) {
                ?>
                <tr>

                    <td colspan="5"><?php _e( 'No result found!', 'erp' ); ?></td>

                </tr>
                <?php
            }
        ?>

        </tbody>
    </table>
    <?php echo erp_ac_pagination( $count, $limit, $pagenum ); ?>
</div>



