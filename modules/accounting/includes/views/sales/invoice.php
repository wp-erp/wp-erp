<style>
    @font-face {
      font-family: "DejaVu Sans";
      src: url(<?php echo WPERP_URL;?>/vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf) format('truetype');
    }
</style>
<link rel="stylesheet" href="<?php echo WPERP_ASSETS . '/css/accounting-statement.css'; ?>">

<div class="erp-acc-statement <?php echo implode( ' ' , $invoice->settings['statement_extra_class'] ); ?>">
    <?php if ( empty( $invoice->settings['hide_statement_title'] ) ): ?>
        <div class="statement-title">
            <h3><?php echo __( 'Invoice', 'erp' ); ?></h3>
        </div>
    <?php endif; ?>

    <div class="statement-header">
        <table>
            <tr>
                <td class="company">
                    <table>
                        <tr>
                            <td class="company-logo">
                                <img src="<?php echo $invoice->settings['company_logo'] ?>" alt="<?php echo $invoice->company->name; ?>">
                            </td>

                            <td class="company-info">
                                <strong><?php echo $invoice->company->name ?></strong>
                                <div><?php echo $invoice->company->get_formatted_address(); ?></div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td class="statement-summery">
                    <div class="statement-barcode">
                        <!-- some barcode -->
                    </div>

                    <div class="statement-amount-summery">

                        <table>
                            <tr>
                                <th><?php echo __( 'Invoice Number', 'erp' ); ?></th>
                                <td><?php echo $invoice->invoice_number; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo __( 'Invoice Date', 'erp' ); ?></th>
                                <td><?php echo strtotime( $invoice->transaction->issue_date ) < 0 ? '&mdash;' : erp_format_date( $invoice->transaction->issue_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo __( 'Due Date', 'erp' ); ?></th>
                                <td><?php echo strtotime( $invoice->transaction->due_date ) < 0 ? '&mdash;' : erp_format_date( $invoice->transaction->due_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo __( 'Amount Due', 'erp' ); ?></th>
                                <td><?php echo erp_ac_get_price( $invoice->transaction->due ); ?></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="statement-address">
        <table>
            <tr>
                <td class="address-billing">
                    <h5><?php echo __( 'Invoice To', 'erp' ); ?></h5>
                    <h3><?php echo $invoice->customer->get_full_name(); ?></h3>
                    <?php echo nl2br( $invoice->transaction->billing_address ); ?>

                    <?php
                        $arb = "الأبحاث والرؤى جي إف كي في السلع الاستهلاكية تسليط الضوء على الاتجاهات وراء واقع السوق اليوم وطلبات المستهلكين الغد.";
                        $thai = "ข้อมูลเชิงลึกของสมาร์ท: สินค้าอุปโภคบริโภค";

                        echo '<pre>';
                        print_r(mb_detect_encoding($arb));
                        echo '</pre>';

                        echo '<pre>';
                        print_r(mb_detect_encoding($thai));
                        echo '</pre>';
                    ?>
                </td>
            </tr>
        </table>
    </div>


    <div class="statement-item-table">
        <table>
            <thead>
                <tr>
                    <th class="item-name"><?php echo __( 'Description', 'erp' ); ?></th>
                    <th><?php echo __( 'Quantity', 'erp' ); ?></th>
                    <th><?php echo __( 'Unit Price', 'erp' ); ?></th>
                    <th><?php echo __( 'Discount(%)', 'erp' ); ?></th>
                    <th><?php echo __( 'Tax(%)', 'erp' ); ?></th>
                    <th><?php echo __( 'Tax Amount', 'erp' ); ?></th>
                    <th><?php echo __( 'Amount', 'erp' ); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php $i = 0; foreach ( $invoice->transaction->items as $line ): ?>
                <tr class="<?php echo ( $i % 2 === 0 ) ? 'item-row-even' : ''; ?>">
                    <td>
                        <span class="item-name"><?php echo $line->journal->ledger->name; ?></span>
                        <p class="item-description"><?php echo $line->description; ?></p>
                    </td>
                    <td class="not-item-name"><?php echo $line->qty; ?></td>
                    <td class="not-item-name"><?php echo erp_ac_get_price( $line->unit_price ); ?></td>
                    <td class="not-item-name"><?php echo $line->discount; ?></td>
                    <td class="not-item-name"><?php echo isset( $invoice->taxinfo[$line->tax]['name'] ) ? $invoice->taxinfo[$line->tax]['name'] .' ('. $invoice->taxinfo[$line->tax]['rate'] .'%)' : ''; ?></td>
                    <td class="not-item-name"><?php echo erp_ac_get_price( ( $line->tax_rate * $line->line_total ) / 100 ); ?></td>
                    <td class="not-item-name"><?php echo erp_ac_get_price( $line->line_total ); ?></td>
                </tr>
                <?php ++$i; endforeach; ?>
            </tbody>

            <tfoot class="statement-footer">
                <tr>
                    <td class="statement-memo" colspan="3">
                        <p><?php echo $invoice->transaction->summary; ?></p>
                    </td>
                    <td class="item-table-total" colspan="4">
                        <table>
                            <tr>
                                <th colspan="3"><?php echo __( 'Sub Total', 'erp' ); ?></th>
                                <td><?php echo erp_ac_get_price( $invoice->transaction->sub_total ); ?></td>
                            </tr>
                            <tr>
                                <th colspan="3"><?php echo __( 'Total', 'erp' ); ?></th>
                                <td><?php echo erp_ac_get_price( $invoice->transaction->total ); ?></td>
                            </tr>
                            <tr>
                                <th colspan="3"><?php echo __( 'Total Related Payments', 'erp' ); ?></th>
                                <td>
                                    <?php
                                        $total_paid = floatval( $invoice->transaction->total ) - floatval( $invoice->transaction->due );
                                        echo erp_ac_get_price( $total_paid );
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
