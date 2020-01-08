<template>
    <div class="wperp-modal-dialog sales-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h2 v-if="null != type">{{ 'payment' == type ? 'Receive Payment' : getInvoiceType() }}</h2>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; {{ __('Print', 'erp') }}
                    </a>
                    <!-- todo: more action has some dropdown and will implement later please consider as planning -->

                    <dropdown v-if="acct_var.pdf_plugin_active">
                        <template slot="button">
                            <a href="#" class="wperp-btn btn--default">
                                <i class="flaticon-settings-work-tool"></i>
                                &nbsp; {{ __('More Action', 'erp') }}
                            </a>
                        </template>
                        <template slot="dropdown">
                            <ul role="menu">
                                <li>
                                    <a :href="pdf_link">{{ __('Export as PDF', 'erp') }}</a>
                                </li>
                                <li><a href="#" @click.prevent="showModal = true">{{ __('Send Mail', 'erp') }}</a></li>
                            </ul>
                        </template>
                    </dropdown>

                    <template v-if="invoice">
                        <a href="#" class="wperp-btn btn--default print-btn"
                           v-clipboard="copyLink"
                           @success="handleSuccess"
                           @error="handleError">{{ __('Copy Link', 'erp') }}</a>
                    </template>
                </div>
            </div>

            <invoice-single-content
                v-if="null != invoice && null != company"
                :invoice="invoice"
                :company="company" />

            <payment-single-content
                v-if="null != payment && null != company"
                :payment="payment"
                :company="company" />

            <send-mail v-if="showModal" :userid="user_id" :data="print_data" :type="type"/>

        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import InvoiceSingleContent from 'admin/components/transactions/sales/InvoiceSingleContent.vue';
import PaymentSingleContent from 'admin/components/transactions/sales/PaymentSingleContent.vue';
import SendMail from 'admin/components/email/SendMail.vue';
import Dropdown from 'admin/components/base/Dropdown.vue';

export default {
    name: 'SalesSingle',

    data() {
        return {
            isWorking : false,
            invoice   : null,
            payment   : null,
            type      : null,
            company   : null,
            acct_var  : erp_acct_var, /* global erp_acct_var */
            showModal : false,
            print_data: null,
            copyLink  : '#',
            user_id   : null,
            pdf_link : '#'
        };
    },

    components: {
        InvoiceSingleContent,
        PaymentSingleContent,
        SendMail,
        Dropdown
    },

    created() {
        /* If this page load directly,
            then we don't have the type or type is `undefined`
            thats why we need to load the type from database */
        const params = this.$route.params;

        if (typeof params.type === 'undefined') {
            this.getSalesType(params.id);
        } else {
            this.loadData(params.type);
        }

        this.getCompanyInfo();

        this.$root.$on('close', () => {
            this.showModal = false;
        });
    },

    methods: {
        getCompanyInfo() {
            HTTP.get(`/company`).then(response => {
                this.company = response.data;
            }).then(e => {}).then(() => {
                this.isWorking = false;
            });
        },

        getSalesType(id) {
            HTTP.get(`/transactions/type/${id}`).then(response => {
                this.loadData(response.data);
            }).then((e) => {}).then(() => {
                this.isWorking = false;
            });
        },

        loadData(type) {
            this.type = type;

            if (type === 'invoice') {
                this.getInvoice();
            } else if (type === 'payment') {
                this.getPayment();
            }
        },

        getInvoiceType() {
            if (this.invoice !== null && this.invoice.estimate === '1') {
                return 'Estimate';
            } else {
                return 'Invoice';
            }
        },

        getInvoice() {
            this.isWorking = true;

            HTTP.get(`/invoices/${this.$route.params.id}`).then(response => {
                this.invoice = response.data;
            }).then(() => {
                this.print_data = this.invoice;
                this.copyLink   = this.invoice.readonly_url;
                this.pdf_link   = this.invoice.pdf_link;
                this.isWorking  = false;
                this.user_id    = this.print_data.customer_id;
            });
        },

        getPayment() {
            this.isWorking = true;

            HTTP.get(`/payments/${this.$route.params.id}`).then(response => {
                this.payment = response.data;
            }).then(() => {
                this.print_data = this.payment;
                this.pdf_link   = this.payment.pdf_link;
                this.user_id    = this.print_data.customer_id;
                this.isWorking  = false;
            });
        },

        printPopup() {
            window.print();
        },

        handleSuccess(e) {
            alert(erp_acct_var.link_copy_success);
        },

        handleError(e) {
            alert(erp_acct_var.link_copy_error);
        }
    }

};
</script>

<style lang="less">
    .wperp-email-multiselect {
        .multiselect__content-wrapper {
            display: none !important;
            height: 0 !important;
            visibility: hidden;
        }
        .multiselect__tags {
            font-size: 12px;
            padding-left: 15px;
            border-radius: 3px;
            input {
                max-height: 30px;
                font-size: 12px;
            }
        }
        .multiselect__tag-icon {
            line-height: 18px;
        }
        .multiselect {
            input.multiselect__input {
                display: none;
            }
            &.multiselect--active input.multiselect__input {
                display: block;
                width: 100% !important;
            }
        }
    }

    .sales-single {
        max-width: 960px;
        margin: 0 auto;
        .wperp-modal-footer {
            border-top: 1px solid #e2e2e2;
        }
        .wperp-modal-header {
            border-bottom: 1px solid #e2e2e2;
        }
        .wperp-form-field, input:not(.wperp-btn) {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }
    }

    @media print {
        .erp-nav-container {
            display: none;
        }
    }
</style>
