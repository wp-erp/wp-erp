<template>
    <div class="wperp-modal-dialog purchase-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4>{{ __('Purchase', 'erp') }}</h4>
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
                        <h4>{{ __('Purchase', 'erp') }}</h4>
                        <div class="wperp-row" v-if="null != purchase">
                            <div class="wperp-col-sm-6">
                                <div class="persons-info">
                                    <strong>{{ purchase.vendor_name }}</strong><br>
                                    {{ purchase.billing_address }}
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>{{ __('Voucher No', 'erp') }}:</th>
                                        <td>#{{ purchase.voucher_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Transaction Date', 'erp') }}:</th>
                                        <td>{{ purchase.date }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Due Date', 'erp') }}:</th>
                                        <td>{{ purchase.due_date }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Created At', 'erp') }}:</th>
                                        <td>{{ purchase.created_at }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != purchase">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Sl.', 'erp') }}</th>
                                    <th>{{ __('Item name', 'erp') }}</th>
                                    <th>{{ __('Qty', 'erp') }}</th>
                                    <th>{{ __('Unit Price', 'erp') }}</th>
                                    <th>{{ __('Amount', 'erp') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr :key="index" v-for="(line, index) in purchase.line_items">
                                    <td>{{ index+1 }}</td>
                                    <td>{{ line.name }}</td>
                                    <td>{{ line.qty }}</td>
                                    <td>{{ moneyFormat(line.price) }}</td>
                                    <td>{{ moneyFormat(line.amount) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7">
                                    <ul>
                                        <li><span>{{ __('Subtotal', 'erp') }}:</span> {{ moneyFormat(purchase.amount) }}</li>
                                        <li><span>{{ __('Total', 'erp') }}:</span> {{ moneyFormat(purchase.amount) }}</li>
                                    </ul>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

                <trans-particulars :particulars="purchase.particulars" />

                <div class="invoice-attachments d-print-none">
                    <h4>{{ __('Attachments', 'erp') }}</h4>
                    <a class="attachment-item" :href="attachment"
                       :key="index"
                       v-for="(attachment, index) in purchase.attachments" download>
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
    name: 'PurchaseSingle',

    components: {
        SendMail,
        Dropdown,
        TransParticulars
    },

    data() {
        return {
            company   : null,
            purchase  : {},
            isWorking : false,
            acct_var  : erp_acct_var,   /* global erp_acct_var */
            print_data: null,
            type      : 'purchase',
            showModal : false,
            people_id : null
        };
    },

    created() {
        this.getCompanyInfo();
        this.getPurchase();

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

        getPurchase() {
            this.isWorking = true;
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get(`/purchases/${this.$route.params.id}`).then(response => {
                this.purchase  = response.data;
                this.people_id = this.purchase.vendor_id;
                this.$store.dispatch('spinner/setSpinner', false);
            }).then(e => {}).then(() => {
                this.print_data = this.purchase;
                this.isWorking = false;
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        printPopup() {
            window.print();
        }
    }

};
</script>

<style lang="less">
    .purchase-single {
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
