<div class="wrap erp-ac-tax-report-wrap">

    <h2><?php _e( 'Sales Tax Summary', 'erp' ); ?>
        <a class="add-new-h2" href="<?php echo erp_ac_get_sales_tax_report_url(); ?>">&#8592;<?php _e( 'Back', 'erp' ); ?></a>
    </h2>
    <p class="erp-ac-report-tax-date erp-ac-tax-name">
        <?php printf( '<i class="fa fa-calculator"></i> %1$s (%2$s%3$s)', $taxinfo[$tax_id]['name'], $taxinfo[$tax_id]['rate'], '%' ); ?>
    </p>
    <p class="erp-ac-report-tax-date">
    <?php

    $start = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) );
    $end   = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) );
    printf( '<i class="fa fa-calendar"></i> %1$s %2$s %3$s %4$s', __( 'From', 'erp' ), $start, __( 'to', 'erp' ),  $end);
    ?>
    </p>
    <table class="wp-list-table widefat fixed striped erp-ac-tax-report-table">
        <thead>
            <tr>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Transaction ID', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table"><?php _e( 'Transaction Date', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table" colspan="2"><?php _e( 'Tax Payable', 'erp' ); ?></th>
                <th class="erp-ac-single-tax-report-table" colspan="2"><?php _e( 'Tax Receivable', 'erp' ); ?></th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th><?php _e( 'Transaction subtotal', 'erp' ); ?></th>
                <th><?php _e( 'tax amount', 'erp' ); ?></th>
                <th><?php _e( 'Transaction subtotal', 'erp' ); ?></th>
                <th><?php _e( 'tax amount', 'erp' ); ?></th>

            </tr>
        </thead>
        <tbody>


        <?php
            $sales_taxs = isset( $taxs['sales'] ) ? $taxs['sales'] : [];
            foreach ( $sales_taxs as $key => $trans ) {
                ?>
                <tr>
                    <td><a data-transaction_id="<?php echo $trans['id']; ?>" class="erp-ac-transaction-report" href="<?php echo erp_ac_get_expense_voucher_url( $trans['id'] ); ?>">#<?php echo $trans['id']; ?></a></td>
                    <td><?php echo erp_format_date( $trans['issue_date'] ); ?></td>
                    <td><?php echo erp_ac_get_price( $trans['sub_total'] ); ?></td>
                    <td><?php echo erp_ac_get_price( $trans['tax_credit'] ); ?></td>
                    <td><?php echo erp_ac_get_price( 0 ); ?></td>
                    <td><?php echo erp_ac_get_price( 0 ); ?></td>
                </tr>

                <?php
            }
            $taxs_expense = isset( $taxs['expense'] ) ? $taxs['expense'] : [];
            foreach ( $taxs_expense as $key => $trans ) {
                ?>
                <tr>
                    <td><a data-transaction_id="<?php echo $trans['id']; ?>" class="erp-ac-transaction-report" href="<?php echo erp_ac_get_expense_voucher_url( $trans['id'] ); ?>">#<?php echo $trans['id']; ?></a></td>
                    <td><?php echo erp_format_date( $trans['issue_date'] ); ?></td>
                    <td><?php echo erp_ac_get_price( 0 ); ?></td>
                    <td><?php echo erp_ac_get_price( 0 ); ?></td>
                    <td><?php echo erp_ac_get_price( $trans['sub_total'] ); ?></td>
                    <td><?php echo erp_ac_get_price( $trans['tax_debit'] ); ?></td>
                </tr>

                <?php
            }
        ?>

        </tbody>
    </table>
    <?php echo erp_ac_pagination( $count, $limit, $pagenum ); ?>
</div>



