<div class="wrap erp-ac-journal-entry erp-ac-form-wrap">

    <?php if ( ! $journal_id ): ?>
        <h2><?php _e( 'New Journal Entry', 'erp' ); ?></h2>
    <?php else: ?>
        <h2><?php _e( 'Edit Journal Entry', 'erp' ); ?></h2>
    <?php endif ?>

    <form action="" method="post" class="erp-form erp-ac-transaction-form" id="erp-journal-form">
        <ul class="erp-form-fields">

            <li class="erp-form-field row-ref">
                <?php
                erp_html_form_input( array(
                    'label' => __( 'Reference', 'erp' ),
                    'name'  => 'ref',
                    'type'  => 'text',
                    'class' => 'erp-ac-reference-field',
                    'addon' => '#',
                    'value' => isset( $journal['ref'] ) ? $journal['ref'] : ''
                ) );
                ?>
            </li>

            <li class="erp-form-field row-issue-date">
                <?php erp_html_form_input( array(
                    'label'    => __( 'Date', 'erp' ),
                    'name'     => 'issue_date',
                    'id'       => 'issue_date',
                    'required' => true,
                    'type'     => 'text',
                    'class'    => 'erp-date-field',
                    'value'    => isset( $journal['issue_date'] ) ? date( 'Y-m-d', strtotime( $journal['issue_date'] ) ) : date( 'Y-m-d', current_time( 'timestamp' ) ),
                ) ); ?>
            </li>

            <li class="erp-form-field row-summary">
                <?php erp_html_form_input( array(
                    'label'       => __( 'Summary', 'erp' ),
                    'name'        => 'summary',
                    'id'          => 'summary',
                    'required'    => true,
                    'type'        => 'textarea',
                    'placeholder' => __( 'Summary', 'erp' ),
                    'custom_attr' => array( 'rows' => 5, 'cols' => 30 ),
                    'value'       => isset( $journal['summary'] ) ? $journal['summary'] : ''
                ) ); ?>
            </li>
        </ul>

        <table class="erp-table erp-ac-transaction-table journal-table">
            <thead>
                <tr>
                    <th class="col-chart"><?php _e( 'Account', 'erp' ); ?></th>
                    <th class="col-desc"><?php _e( 'Description', 'erp' ); ?></th>
                    <th class="col-amount"><?php _e( 'Debit', 'erp' ); ?></th>
                    <th class="col-amount"><?php _e( 'Credit', 'erp' ); ?></th>
                    <th class="col-action">&nbsp;</th>
                </tr>
            </thead>

            <tbody>
            <?php

                $items = [];
                $journal['journals'] = isset( $journal['journals'] ) ? $journal['journals'] : [];

                foreach ( $journal['journals'] as $key => $value ) {
                    array_map( function( $item ) use ( $value, &$items ) {

                        if ( $item['journal_id'] == $value['id'] ) {
                            $items[] = array_merge( $value, $item );
                        }

                    }, $journal['items'] );
                }

                $total_debit  = 0;
                $total_credit = 0;

                erp_html_form_input( array(
                    'name'  => 'id',
                    'type'  => 'hidden',
                    'value' => isset( $journal['id'] ) ? $journal['id'] : 0,
                ) );

                $items = count( $items ) ? $items : [1,2];

                foreach ( $items as $key => $item ) {
                    ?>
                    <tr>
                        <td class="col-chart">
                            <?php
                            $account = erp_ac_get_chart_dropdown();
                            echo erp_ac_render_account_dropdown_html( $account, [
                                    'name' => 'journal_account[]',
                                    'selected' => isset( $item['ledger_id'] ) ? $item['ledger_id'] : ''
                                ] );

                            ?>
                        </td>

                        <td class="col-desc">
                            <?php
                            erp_html_form_input( array(
                                'name'  => 'line_desc[]',
                                'type'  => 'text',
                                'value' => isset( $item['description'] ) ? $item['description'] : ''
                            ) );
                            ?>
                        </td>

                        <td class="col-amount">
                            <?php
                            erp_html_form_input( array(
                                'name'        => 'line_debit[]',
                                'type'        => 'text',
                                'class'       => 'line_debit',
                                'placeholder' => erp_ac_get_price_for_field( '0.00', ['symbol'=>false] ),
                                'value'       => isset( $item['debit'] ) ? erp_ac_get_price_for_field( $item['debit'], ['symbol'=>false] ) : ''
                            ) );
                            ?>
                        </td>

                        <td class="col-amount">
                            <?php
                            erp_html_form_input( array(
                                'name'        => 'line_credit[]',
                                'type'        => 'text',
                                'class'       => 'line_credit',
                                'placeholder' => erp_ac_get_price_for_field( '0.00', ['symbol'=>false] ),
                                'value'       => isset( $item['credit'] ) ? erp_ac_get_price_for_field( $item['credit'], ['symbol'=>false] ) : ''
                            ) );
                            ?>
                        </td>

                        <td class="col-action">
                            <a href="#" class="remove-line"><span class="dashicons dashicons-trash"></span></a>
                            <!-- <a href="#" class="move-line"><span class="dashicons dashicons-menu"></span></a> -->
                        </td>

                        <?php
                        erp_html_form_input( array(
                            'name'  => 'journal_id[]',
                            'type'  => 'hidden',
                            'value' => isset( $item['journal_id'] ) ? $item['journal_id'] : 0
                        ) );
                        ?>

                        <?php
                        erp_html_form_input( array(
                            'name'  => 'item_id[]',
                            'type'  => 'hidden',
                            'value' => isset( $item['id'] ) ? $item['id'] : 0
                        ) );
                        ?>
                    </tr>

                    <?php
                    $debit        = isset( $item['debit'] ) ? $item['debit'] : 0;
                    $credit       = isset( $item['credit'] ) ? $item['credit'] : 0;
                    $total_debit  = $total_debit + $debit;
                    $total_credit = $total_credit + $credit;
                }

                $total_debit = $total_debit > 0 ? erp_ac_get_price_for_field( $total_debit, ['symbol'=>false] ) : erp_ac_get_price_for_field( '0.00', ['symbol'=>false] );
                $total_credit = $total_credit > 0 ? erp_ac_get_price_for_field( $total_credit, ['symbol'=>false] ) : erp_ac_get_price_for_field( '0.00', ['symbol'=>false] );
            ?>

            </tbody>
            <tfoot>
                <tr>
                    <th><a href="#" class="button add-line"><?php _e( '+ Add Line', 'erp' ); ?></a></th>
                    <th class="align-right"><?php _e( 'Total', 'erp' ); ?></th>
                    <th class="col-amount">
                        <input type="text" name="debit_total" class="debit-price-total" readonly value="<?php echo $total_debit; ?>">
                    </th>
                    <th class="col-amount">
                        <input type="text" name="credit_total" class="credit-price-total" readonly value="<?php echo $total_credit; ?>">
                    </th>

                </tr>
                <tr>

                    <th colspan="2" class="align-right"></th>
                    <th colspan="2" class="col-amount">
                        <div class="valid erp-ac-journal-diff">
                            <?php _e( 'The amount of debit and credit are not same', 'erp' ); ?>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>

        <input type="hidden" name="erp-action" value="ac-new-journal-entry">

        <?php wp_nonce_field( 'erp-ac-journal-entry' ); ?>

        <?php if ( !$journal_id ): ?>
            <?php submit_button( __( 'Add Journal Entry', 'erp' ), 'primary', 'submit_erp_ac_journal' ); ?>
        <?php else: ?>
            <?php submit_button( __( 'Update Journal Entry', 'erp' ), 'primary', 'submit_erp_ac_journal' ); ?>
        <?php endif ?>

    </form>
</div>
