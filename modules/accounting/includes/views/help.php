<div class="wrap">
    <h1>
        <?php _e( 'Accounting Help', 'erp' ); ?>
        <a href="https://wperp.com/accounting/" target="_blank" class="page-title-action">
            <?php _e( 'View all Documentations', 'erp' ); ?>
        </a>
    </h1>
    <?php
    $erp_doc_sections = array(
        __( 'General', 'erp' )            => array(
            __( 'What are the functionalities of “Chart of Accounts”?', 'erp' ) => 'https://wperp.com/docs/accounting/getting-started/chart-of-accounts/',
            __( 'Can I add a new account in Chart of Accounts?', 'erp' )        => 'https://wperp.com/docs/accounting/getting-started/creating-a-new-chart/',
            __( 'Can I create a bank account in Accounting?', 'erp' )           => 'https://wperp.com/docs/accounting/getting-started/creating-bank-accounts/',
            __( 'How to approve/void a transaction?', 'erp' )                   => 'https://wperp.com/docs/accounting/getting-started/approving-voiding-transactions/',
            __( 'How to create vendors and customers?', 'erp' )                 => 'https://wperp.com/docs/accounting/vendor-customers/'
        ),
        __( 'Sales Management', 'erp' )   => array(
            __( 'What are the basic transaction types?', 'erp' )               => 'https://wperp.com/docs/accounting/sales-transactions/basic-of-transaction-types/',
            __( 'What is the difference between invoice and payment?', 'erp' ) => 'https://wperp.com/docs/accounting/sales-transactions/invoice-vs-payment/',
            __( 'How to create an invoice?', 'erp' )                           => 'https://wperp.com/docs/accounting/sales-transactions/creating-your-first-invoice/',
            __( 'How to create a payment invoice?', 'erp' )                    => 'https://wperp.com/docs/accounting/sales-transactions/adding-invoice-payments/',
            __( 'How can I delete an invoice?', 'erp' )                        => 'https://wperp.com/docs/accounting/sales-transactions/delete-an-invoice/',
            __( 'How to take partial payment?', 'erp' )                        => 'https://wperp.com/docs/accounting/sales-transactions/taking-payment-in-installments/',
        ),
        __( 'Expense Management', 'erp' ) => array(
            __( 'What are the differences between vendor credit and voucher?', 'erp' ) => 'https://wperp.com/docs/accounting/expense-transactions/vendor-credit-vs-voucher/',
            __( 'How to create vendor credit?', 'erp' )                                => 'https://wperp.com/docs/accounting/expense-transactions/creating-vendor-credits/',
            __( 'How to pay vendor credit?', 'erp' )                                   => 'https://wperp.com/docs/accounting/expense-transactions/paying-vendor-credits/',
            __( 'How to create purchase voucher?', 'erp' )                             => 'https://wperp.com/docs/accounting/expense-transactions/creating-purchase-vouchers/',
            __( 'How to pay in installment?', 'erp' )                                  => 'https://wperp.com/docs/accounting/expense-transactions/paying-in-installments/'
        ),
        __( 'Miscellaneous', 'erp' )      => array(
            __( 'How can I receive, spend transfer between bank accounts?', 'erp' ) => 'https://wperp.com/docs/accounting/bank-accounts/',
            __( 'How to create a Journal Entry?', 'erp' )                           => 'https://wperp.com/docs/accounting/journal-entry/creating-a-journal-entry/',
            __( 'How to produce different types of reports in Accounting?', 'erp' ) => 'https://wperp.com/docs/accounting/reporting/',
        )
    );

    $sections = apply_filters( 'erp_ac_help_docs', $erp_doc_sections );

    if ( ! empty( $sections ) ):?>
        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">
                <?php foreach ( $sections as $section_title => $docs ): ?>
                    <div class="erp-help-section postbox-container">
                        <div class="metabox-holder">

                            <div class="meta-box-sortables">

                                <div class="postbox">
                                    <h2 class="hndle"><?php echo esc_html( $section_title ); ?></h2>

                                    <?php if ( !empty($docs) ) { ?>
                                        <div class="erp-help-questions">
                                            <ul>
                                                <?php foreach ($docs as $title => $link) { ?>
                                                    <?php
                                                    $tracking_url = add_query_arg(
                                                        array(
                                                            'utm_source'   => 'doc',
                                                            'utm_medium'   => 'erp',
                                                            'utm_campaign' => 'manik',
                                                            'utm_content'  => 'aion',
                                                        ),
                                                        untrailingslashit($link)
                                                    );
                                                    ?>
                                                    <li><a href="<?php echo esc_url_raw( $tracking_url ); ?>" target="_blank"><?php echo esc_html( $title ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>

    <?php endif; ?>

</div>



<style type="text/css" media="screen">
    .erp-help-questions li {
        margin: 0;
        border-bottom: 1px solid #eee;
    }

    .erp-help-questions li a {
        padding: 10px 15px;
        display: block;
    }

    .erp-help-questions li a:hover {
        background-color: #F5F5F5;
    }

    .erp-help-questions li:last-child {
        border-bottom: none;
    }

    .erp-help-questions li .dashicons {
        float: right;
        color: #ccc;
        margin-top: -3px;
    }

    @media screen and (min-width: 960px) {
        .erp-help-section .postbox-container{
            width: 100% !important;
        }

        .erp-help-section:nth-child(odd){
            clear:both !important;
        }

    }
</style>
