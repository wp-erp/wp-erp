<template>
    <div class="wperp-modal-dialog paybill-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h2>{{ __('Pay Bill', 'erp') }}</h2>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; {{ __('Print', 'erp') }}
                    </a>
                    <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                    <dropdown>
                        <template slot="button">
                            <a href="#" class="wperp-btn btn--default">
                                <i class="flaticon-settings-work-tool"></i>
                                &nbsp; {{ __('More Action', 'erp') }}
                            </a>
                        </template>
                        <template slot="dropdown">
                            <ul role="menu">
                                <li> <a :href="pdf_link">{{ __('Export as PDF', 'erp') }}</a> </li>
                                <li><a href="#" @click.prevent="showModal = true">{{ __('Send Mail', 'erp') }}</a></li>
                            </ul>
                        </template>
                    </dropdown>
                </div>
            </div>

            <send-mail v-if="showModal" :userid="people_id" :data="print_data" :type="type"/>

            <div class="wperp-modal-body">
                <div class="wperp-invoice-panel">
                    <div class="invoice-header" v-if="null != company">
                        <div class="invoice-logo">
                            <img :src="company.logo" alt="logo name" width="100" height="100">
                        </div>
                        <div class="invoice-address">
                            <address>
                                <strong>{{ company.name }}</strong><br>
                                {{ company.address.address_1 }}<br>
                                {{ company.address.address_2 }}<br>
                                {{ company.address.city }}<br>
                                {{ company.address.country }}
                            </address>
                        </div>
                    </div>

                    <div class="invoice-body">
                        <h4>{{ __('Pay Bill', 'erp') }}</h4>
                        <div class="wperp-row" v-if="null != payBill">
                            <div class="wperp-col-sm-6">
                                <div class="persons-info">
                                    <strong>{{ payBill.vendor_name }}</strong><br>
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>{{ __('Voucher No', 'erp') }}:</th>
                                        <td>#{{ payBill.voucher_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Reference No', 'erp') }}:</th>
                                        <td>#{{ payBill.ref }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Transaction Date', 'erp') }}:</th>
                                        <td>{{ formatDate(payBill.trn_date) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Created At', 'erp') }}:</th>
                                        <td>{{ formatDate(payBill.created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Transaction From', 'erp') }}:</th>
                                        <td>{{ payBill.trn_by }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != payBill">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                            <tr class="inline-edit-row">
                                <th>{{ __('Sl.', 'erp') }}</th>
                                <th>{{ __('Bill No', 'erp') }}</th>
                                <th>{{ __('Amount', 'erp') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :key="index" v-for="(line, index) in payBill.bill_details" class="inline-edit-row">
                                <td>{{ line.id }}</td>
                                <td>{{ line.bill_no }}</td>
                                <td>{{ moneyFormat(line.amount) }}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr class="inline-edit-row">
                                <td colspan="7">
                                    <ul>
                                        <li><span>{{ __('Subtotal', 'erp') }}:</span> {{ moneyFormat(payBill.amount) }}</li>
                                        <li><span>{{ __('Total', 'erp') }}:</span> {{ moneyFormat(payBill.amount) }}</li>
                                    </ul>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

                <trans-particulars :particulars="payBill.particulars" />

                <div class="invoice-attachments d-print-none">
                    <h4>{{ __('Attachments', 'erp') }}</h4>
                    <a class="attachment-item" :href="attachment"
                       :key="index"
                       v-for="(attachment, index) in payBill.attachments" download>
                        <img :src="acct_var.acct_assets + '/images/file-thumb.png'">
                        <div class="attachment-meta">
                            <span>{{attachment.substring(attachment.lastIndexOf('/')+1) }}</span><br>
                            <!-- <span class="text-muted">file size</span> -->
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import SendMail from 'admin/components/email/SendMail.vue';
import Dropdown from 'admin/components/base/Dropdown.vue';
import TransParticulars from 'admin/components/transactions/TransParticulars.vue';

export default {
    name: 'PayBillSingle',

    components: {
        SendMail,
        Dropdown,
        TransParticulars
    },

    data() {
        return {
            company  : null,
            payBill  : {},
            isWorking: false,
            acct_var : erp_acct_var, /* global erp_acct_var */
            print_data : null,
            type       : 'pay_bill',
            showModal  : false,
            people_id  : null,
            pdf_link   : '#'
        };
    },

    created() {
        this.getCompanyInfo();
        this.getBill();

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

        getBill() {
            this.isWorking = true;
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get(`/pay-bills/${this.$route.params.id}`).then(response => {
                this.payBill = response.data;
                this.people_id = this.payBill.vendor_id;
                this.pdf_link = this.payBill.pdf_link;
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(e => {}).then(() => {
                this.print_data = this.payBill;
                this.isWorking = false;
            });
        },

        printPopup() {
            window.print();
        }
    }

};
</script>

<style lang="less">
    .paybill-single {
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
