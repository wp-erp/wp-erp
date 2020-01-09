<template>
    <div>
        <h2 class="view-doc">
            <span>{{ __('Accounting Help', 'erp') }}</span>
            <a href="https://wperp.com/docs/accounting/getting-started/" class="wperp-btn">{{ __('View all documentation', 'erp') }}</a>
        </h2>

        <div class="acoounting-docs-wrap">
            <div id="dashboard-widgets" class="metabox-holder">

                <div class="erp-help-section postbox-container" :key="index" v-for="(doc, index) in docs">
                    <div class="metabox-holder">
                        <div class="meta-box-sortables">
                            <div class="postbox">
                                <h2 class="hndle">{{ doc.title }}</h2>

                                <div class="erp-help-questions">
                                    <ul :key="queInx" v-for="(que, queInx) in doc.ques">
                                        <li><a :href="que.url" target="_blank">{{ que.label }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- .erp-help-section -->

            </div>
        </div>

    </div>
</template>

<script>

export default {
    name: 'HelpContent',

    data() {
        return {
            docs: [

                {
                    title: window.__('General', 'erp'),
                    ques: [
                        {
                            label: window.__('How to set Financial Years?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/getting-started/setting-financial-year/'
                        },
                        {
                            label: window.__('What is Opening Balance?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/getting-started/opening-balance/'
                        },
                        {
                            label: window.__('How to Set Currency?', 'erp'),
                            url: 'https://wperp.com/docs/erp-core/global-settings/'
                        },
                        {
                            label: window.__('What are the basic transaction types?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/sales-transactions/basic-of-transaction-types/'
                        },
                        {
                            label: window.__('How to create a Journal Entry?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/journal-entry/'
                        }
                    ]
                },

                {
                    title: window.__('Users', 'erp'),
                    ques: [
                        {
                            label: window.__('How to create customer?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/users/creating-a-customer/'
                        },
                        {
                            label: window.__('How to create vendors?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/users/creating-a-vendor/'
                        }
                    ]
                },

                {
                    title: window.__('Sales Transactions', 'erp'),
                    ques: [
                        {
                            label: window.__('How to create an invoice?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/sales-transactions/creating-your-first-invoice/'
                        },
                        {
                            label: window.__('How to receive payment from invoices?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/sales-transactions/adding-invoice-payments/'
                        },
                        {
                            label: window.__('How can I delete an invoice?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/sales-transactions/creating-your-first-invoice/'
                        }
                    ]
                },

                {
                    title: window.__('Expense Transactions', 'erp'),
                    ques: [
                        {
                            label: window.__('How to create a bill?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/expense-transactions/creating-bill-new/'
                        },
                        {
                            label: window.__('How to create payments for bills?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/expense-transactions/creating-bill-new/'
                        },
                        {
                            label: window.__('How to create an expense?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/expense-transactions/creating-expense-new/'
                        },
                        {
                            label: window.__('How to create a check payment?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/expense-transactions/creating-inventory-check-new/'
                        }
                    ]
                },

                {
                    title: window.__('Purchase Transactions', 'erp'),
                    ques: [
                        {
                            label: window.__('How to create purchase quotation?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/purchase-transactions-2/create-purchase/'
                        },
                        {
                            label: window.__('How to create payments for purchase quotations?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/purchase-transactions-2/pay-purchase/'
                        }
                    ]
                },

                {
                    title: window.__('Bank Accounts', 'erp'),
                    ques: [
                        {
                            label: window.__('Can I create a bank account in Accounting?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/chart-of-accounts/creating-a-new-chart/'
                        },
                        {
                            label: window.__('How can I receive, spend transfer between bank accounts?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/bank-accounts/transferring-between-accounts/'
                        }
                    ]
                },

                {
                    title: window.__('Products', 'erp'),
                    ques: [
                        {
                            label: window.__('Can I create products / services / product categoty?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/adding-products/'
                        }
                    ]
                },

                {
                    title: window.__('Tax', 'erp'),
                    ques: [
                        {
                            label: window.__('What is Tax Zone?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/tax/'
                        },
                        {
                            label: window.__('What is Tax Category?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/tax/'
                        },
                        {
                            label: window.__('What is Tax Agency?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/tax/'
                        }
                    ]
                },

                {
                    title: window.__('Reports', 'erp'),
                    ques: [
                        {
                            label: window.__('How to produce different types of reports in Accounting?', 'erp'),
                            url: 'https://wperp.com/docs/accounting/reporting/'
                        }
                    ]
                }

            ]
        };
    }
};
</script>

<style lang="less">
    .view-doc {
        margin-top:15px;
        align-items: center;
        display: flex;

        span {
            font-size: 18px;
            font-weight: bold;
        }

        a {
            background: #1a9ed4;
            border-radius: 3px;
            color: #fff;
            font-size: 12px;
            height: 29px;
            line-height: 29px;
            margin-left: 13px;
            text-align: center;
            text-decoration: none;
            width: 160px;

            &:hover {
                color: #fff;
            }
        }
    }

    .erp-help-questions ul {
        margin-bottom: 0;
    }

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
