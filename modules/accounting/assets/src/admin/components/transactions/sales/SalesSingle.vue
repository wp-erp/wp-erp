<template>
    <div class="wperp-modal-dialog sales-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4 v-if="null != type">{{ 'payment' == type ? 'Payment' : 'Invoice' }}</h4>
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

            <modal
                v-if="showModal" 
                @close="showModal = false" 
                :title="'Send Mail'"
                :footer="true"
                :hasForm="true"
                :header="true"
                >
                <!-- <h2 slot="header">Send an Email</h2> -->
                <template slot="body">
                    <div class="wperp-form-group wperp-row">
                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                            <label for="to">To <span class="wperp-required-sign">*</span></label>
                        </div>
                        <div class="wperp-col-sm-9 wperp-col-xs-12 wperp-email-multiselect">
                            <multiselect 
                                v-model="value" 
                                tag-placeholder="Add this Email" 
                                placeholder="Click to Add Email Address" 
                                label="name" 
                                track-by="code" 
                                :options="options" 
                                :multiple="true" 
                                :taggable="true" 
                                @tag="addEmail">
                            </multiselect>
                        </div>
                    </div>
                    <div class="wperp-form-group wperp-row">
                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                            <label for="subject">Subject <span class="wperp-required-sign">*</span></label>
                        </div>
                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                            <input type="text" name="subject" id="subject" class="wperp-form-field" placeholder="Enter Subject Here" />
                        </div>
                    </div>
                    <div class="wperp-form-group wperp-row">
                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                            <label for="message">Message <span class="wperp-required-sign">*</span></label>
                        </div>
                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                            <textarea name="message" id="message" class="wperp-form-field" placeholder="Enter Your Message Here" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="wperp-row">
                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                            <label for="attachment">Attachment <span class="wperp-required-sign">*</span></label>
                        </div>
                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                            <div class="form-check">
                                <label class="form-check-label mb-0" for="attachment">
                                <input class="form-check-input" name="attachment" id="attachment" type="checkbox">
                                <span class="form-check-sign"></span> <span class="field-label">Attach the invoice as PDF</span>
                            </label>
                            </div>
                        </div>
                    </div>
                </template>
                <template slot="footer">
                    <div class="buttons-wrapper text-right">
                        <button class="wperp-btn btn--default" @click="showModal = false">Cancel</button>
                        <button class="wperp-btn btn--primary" type="submit">Send</button>
                    </div>
                </template>
            </modal>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import InvoiceSingleContent from 'admin/components/transactions/sales/InvoiceSingleContent.vue';
    import PaymentSingleContent from 'admin/components/transactions/sales/PaymentSingleContent.vue';
    import Dropdown from 'admin/components/base/Dropdown.vue';
    import Modal from 'admin/components/modal/Modal.vue';
    import Multiselect from 'vue-multiselect';

    export default {
        name: 'SalesSingle',

        data() {
            return {
                isWorking: false,
                invoice  : null,
                payment  : null,
                type     : null,
                company  : null,
                acct_var : erp_acct_var,
                showModal: false,
                value: [],
                options: [],

            }
        },

        components: {
            InvoiceSingleContent,
            PaymentSingleContent,
            Dropdown,
            Modal,
            Multiselect
        },

        created() {
            /* If this page load directly, 
            then we don't have the type or type is `undefined`
            thats why wee need to load the type from database */
            let params = this.$route.params;

            if ( typeof params.type === 'undefined' ) {
                this.getSalesType(params.id);
            } else {
                this.loadData(params.type);
            }

            this.getCompanyInfo();
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

                if ( 'sales_invoice' === type ) {
                    this.getInvoice();
                } else if ( 'payment' === type ) {
                    this.getPayment();
                }
            },

            getInvoice() {
                this.isWorking = true;

                HTTP.get(`/invoices/${this.$route.params.id}`).then(response => {                    
                    this.invoice = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getPayment() {
                this.isWorking = true;

                HTTP.get(`/payments/${this.$route.params.id}`).then(response => {
                    this.payment = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            printPopup() {
                window.print();
            },

            addEmail (newEmail) {
              const email = {
                name: newEmail,
                code: newEmail.substring(0, 2) + Math.floor((Math.random() * 10000000))
              } 
              this.value.push(email)
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
        max-width: 800px;
        margin: 40px 0;
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

