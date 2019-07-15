<template>
    <div class="wperp-modal-dialog sales-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h2 v-if="null != type">{{ 'payment' == type ? 'Receive Payment' : getInvoiceType() }}</h2>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; Print
                    </a>
                    <!-- todo: more action has some dropdown and will implement later please consider as planning -->

                    <dropdown>
                        <template slot="button">
                            <a href="#" class="wperp-btn btn--default">
                                <i class="flaticon-settings-work-tool"></i>
                                &nbsp; More Action
                            </a>
                        </template>
                        <template slot="dropdown">
                            <ul role="menu">
                                <li><a href="#" @click.prevent="showModal = true">Send Mail</a></li>
                            </ul>
                        </template>
                    </dropdown>

                    <a href="#" class="wperp-btn btn--default print-btn"
                       v-clipboard="copyLink"
                       @success="handleSuccess"
                       @error="handleError">Copy Link</a>
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

            <send-mail v-if="showModal" :data="print_data" :type="type"/>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import InvoiceSingleContent from 'admin/components/transactions/sales/InvoiceSingleContent.vue'
    import PaymentSingleContent from 'admin/components/transactions/sales/PaymentSingleContent.vue'
    import SendMail from 'admin/components/email/SendMail.vue'
    import Dropdown from 'admin/components/base/Dropdown.vue'

    export default {
        name: 'SalesSingle',

        data() {
            return {
                isWorking : false,
                invoice   : null,
                payment   : null,
                type      : null,
                company   : null,
                acct_var  : erp_acct_var,
                showModal : false,
                print_data: null,
                copyLink  : '#'
            }
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
            let params = this.$route.params;

            if ( typeof params.type === 'undefined' ) {
                this.getSalesType(params.id);
            } else {
                this.loadData(params.type);
            }

            this.getCompanyInfo();

            this.$root.$on( 'close', () => {
                this.showModal = false;
            })
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then(response => {
                    this.company = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getSalesType(id) {
                HTTP.get(`/transactions/type/${id}`).then(response => {
                    this.loadData(response.data);
                }).then( (e) => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            loadData(type) {
                this.type = type;

                if ( 'invoice' === type ) {
                    this.getInvoice();
                } else if ( 'payment' === type ) {
                    this.getPayment();
                }
            },

            getInvoiceType() {
                if ( this.invoice !== null && '1' === this.invoice.estimate ) {
                    return "Estimate";
                } else {
                    return "Invoice";
                }
            },

            getInvoice() {
                this.isWorking = true;

                HTTP.get(`/invoices/${this.$route.params.id}`).then(response => {
                    this.invoice = response.data;
                }).then( e => {} ).then(() => {
                    this.print_data = this.invoice;
                    this.copyLink   = this.invoice.readonly_url;
                    this.isWorking  = false;
                });
            },

            getPayment() {
                this.isWorking = true;

                HTTP.get(`/payments/${this.$route.params.id}`).then(response => {
                    this.payment = response.data;
                }).then( e => {} ).then(() => {
                    this.print_data = this.payment;
                    this.isWorking = false;
                });
            },

            printPopup() {
                window.print();
            },

            handleSuccess (e) {
                console.log(e);
                alert('Link has been copied.')
            },

            handleError (e) {
                e.preventDefault();
                alert('Failed to copy link.')
            }
        },

    }
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

