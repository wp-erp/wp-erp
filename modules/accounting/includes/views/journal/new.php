<div class="wrap erp-ac-journal-entry erp-ac-form-wrap">
    <h2><?php _e( 'New Journal Entry', 'erp' ); ?></h2>

    <form action="" method="post" class="erp-form" id="erp-journal-form">

        <ul class="erp-form-fields">
            <li class="erp-form-field row-ref">
                <?php
                erp_html_form_input( array(
                    'label' => __( 'Reference', 'erp' ),
                    'name'  => 'ref',
                    'type'  => 'text',
                    'class' => 'erp-ac-reference-field',
                    'addon' => '#',
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
                    'value'    => date( 'Y-m-d', current_time( 'timestamp' ) ),
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
                <tr>
                    <td class="col-chart">
                        <?php
                        $account = erp_ac_get_chart_dropdown();
                        echo erp_ac_render_account_dropdown_html( $account, ['name' => 'journal_account[]'] );

                        ?>
                    </td>
                    <td class="col-desc">
                        <?php
                        erp_html_form_input( array(
                            'name'  => 'line_desc[]',
                            'type'  => 'text'
                        ) );
                        ?>
                    </td>
                    <td class="col-amount">
                        <?php
                        erp_html_form_input( array(
                            'name'        => 'line_debit[]',
                            'type'        => 'text',
                            'class'       => 'line_debit',
                            'placeholder' => erp_ac_get_price_for_field( '0.00', ['symbol'=>false] )
                        ) );
                        ?>
                    </td>
                    <td class="col-amount">
                        <?php
                        erp_html_form_input( array(
                            'name'        => 'line_credit[]',
                            'type'        => 'text',
                            'class'       => 'line_credit',
                            'placeholder' => erp_ac_get_price_for_field( '0.00', ['symbol'=>false] )
                        ) );
                        ?>
                    </td>
                    <td class="col-action">
                        <a href="#" class="remove-line"><span class="dashicons dashicons-trash"></span></a>
                        <a href="#" class="move-line"><span class="dashicons dashicons-menu"></span></a>
                    </td>
                </tr>

                <tr>
                    <td class="col-chart">
                        <?php
                        $account = erp_ac_get_chart_dropdown();
                        echo erp_ac_render_account_dropdown_html( $account, ['name' => 'journal_account[]'] );

                        ?>
                    </td>
                    <td class="col-desc">
                        <?php
                        erp_html_form_input( array(
                            'name'  => 'line_desc[]',
                            'type'  => 'text'
                        ) );
                        ?>
                    </td>
                    <td class="col-amount">
                        <?php
                        erp_html_form_input( array(
                            'name'        => 'line_debit[]',
                            'type'        => 'text',
                            'class'       => 'line_debit',
                            'placeholder' => erp_ac_get_price_for_field( '0.00', ['symbol'=>false] )
                        ) );
                        ?>
                    </td>
                    <td class="col-amount">
                        <?php
                        erp_html_form_input( array(
                            'name'        => 'line_credit[]',
                            'type'        => 'text',
                            'class'       => 'line_credit',
                            'placeholder' => erp_ac_get_price_for_field( '0.00', ['symbol'=>false] )
                        ) );
                        ?>
                    </td>
                    <td class="col-action">
                        <a href="#" class="remove-line"><span class="dashicons dashicons-trash"></span></a>
                        <a href="#" class="move-line"><span class="dashicons dashicons-menu"></span></a>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th><a href="#" class="button add-line"><?php _e( '+ Add Line', 'erp' ); ?></a></th>
                    <th class="align-right"><?php _e( 'Total', 'erp' ); ?></th>
                    <th class="col-amount">
                        <input type="text" name="debit_total" class="debit-price-total" readonly value="<?php echo erp_ac_get_price_for_field( '0.00', ['symbol'=>false] ); ?>">
                    </th>
                    <th class="col-amount">
                        <input type="text" name="credit_total" class="credit-price-total" readonly value="<?php echo erp_ac_get_price_for_field( '0.00', ['symbol'=>false] ); ?>">
                    </th>
                    <th class="col-diff"><?php echo erp_ac_get_price_for_field( '0.00', ['symbol'=>false] ); ?></th>
                </tr>
            </tfoot>
        </table>


        <input type="hidden" name="erp-action" value="ac-new-journal-entry">

        <?php wp_nonce_field( 'erp-ac-journal-entry' ); ?>
        <?php submit_button( __( 'Add Journal Entry', 'erp' ), 'primary', 'submit_erp_ac_journal' ); ?>

    </form>
</div>