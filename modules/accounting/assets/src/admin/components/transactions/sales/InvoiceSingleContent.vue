<template>
    <div class="wperp-modal-body">
        <div class="wperp-invoice-panel">
            <div class="invoice-header" v-if="company.name">
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
                <h4>{{getInvoiceType()}}</h4>
                <div class="wperp-row">
                    <div class="wperp-col-sm-6">
                        <h5>{{ __('Bill to', 'erp') }}:</h5>
                        <div class="persons-info">
                            <strong>{{ invoice.customer_name }}</strong><br>
                            {{ invoice.billing_address }}
                        </div>
                    </div>
                    <div class="wperp-col-sm-6">
                        <table class="invoice-info">
                            <tr v-if="invoice.sales_voucher_id">
                                <th>{{ __('Sales Voucher No', 'erp') }}:</th>
                                <td>#{{ invoice.sales_voucher_id }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Voucher No', 'erp') }}:</th>
                                <td>#{{ invoice.voucher_no }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Transaction Date', 'erp') }}:</th>
                                <td>{{ formatDate( invoice.trn_date ) }}</td>
                            </tr>
                            <tr v-if="invoice.due_date">
                                <th>{{ __('Due Date', 'erp') }}:</th>
                                <td>{{ formatDate( invoice.due_date ) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Created At', 'erp') }}:</th>
                                <td>{{ formatDate( invoice.created_at ) }}</td>
                            </tr>
                            <tr v-if="invoice.total_due">
                                <th>{{ __('Amount Due', 'erp') }}:</th>
                                <td>{{ moneyFormat( invoice.total_due ) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="wperp-invoice-table">
                <table class="wperp-table wperp-form-table invoice-table">
                    <thead>
                        <tr>
                            <th>{{ __('Sl', 'erp') }}.</th>
                            <th>{{ __('Product', 'erp') }}</th>
                            <th>{{ __('Qty', 'erp') }}</th>
                            <th>{{ __('Unit Price', 'erp') }}</th>
                            <th>{{ __('Amount', 'erp') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr :key="index" v-for="(detail, index) in invoice.line_items">
                            <th>{{ index+1 }}</th>
                            <th>{{ detail.name }}</th>
                            <td>{{ detail.qty }}</td>
                            <td>{{ moneyFormat( detail.unit_price ) }}</td>
                            <td>{{ moneyFormat( parseFloat(detail.unit_price) * parseFloat(detail.qty) ) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="wperp-invoice-amounts" colspan="7">
                                <ul>
                                    <li><span>{{ __('Subtotal', 'erp') }}:</span> {{ moneyFormat( invoice.amount ) }}</li>
                                    <li><span>{{ __('Discount', 'erp') }}:</span> (-) {{ moneyFormat( invoice.discount ) }}</li>
                                    <li><span>{{ __('Tax', 'erp') }}:</span> (+) {{ moneyFormat( invoice.tax ) }}</li>
                                    <li><span>{{ __('Total', 'erp') }}:</span> {{ moneyFormat( total ) }}</li>
                                </ul>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        <trans-particulars :particulars="invoice.particulars" />

        <div class="invoice-attachments d-print-none">
            <h4>{{ __('Attachments', 'erp') }}</h4>
            <a class="attachment-item" :href="attachment"
                :key="index"
                v-for="(attachment, index) in invoice.attachments" download>
                <img :src="acct_var.acct_assets + '/images/file-thumb.png'">
                <div class="attachment-meta">
                    <span>{{attachment.substring(attachment.lastIndexOf('/')+1) }}</span><br>
                    <!-- <span class="text-muted">file size</span> -->
                </div>
            </a>
        </div>

    </div>
</template>

<script>
import TransParticulars from 'admin/components/transactions/TransParticulars.vue';

export default {
    name: 'InvoiceSingleContent',

    props: {
        invoice: {
            type: [Object],
            default: {}
        },
        company: {
            type: [Object],
            default: {}
        }
    },

    data() {
        return {
            acct_var: erp_acct_var, /* global erp_acct_var */
            //total   : null
        };
    },

    components: {
        TransParticulars
    },

    computed: {
        total () {
            if ( !this.invoice.amount ) {
                return '00.00';
            }
            
            return parseFloat(this.invoice.amount) + parseFloat(this.invoice.tax) - parseFloat(this.invoice.discount);
        }
    },

    created() {
    },

    methods: {
        getInvoiceType() {
            if (this.invoice !== null && this.invoice.estimate === '1') {
                return 'Estimate';
            } else if ( this.invoice.sales_voucher_id ) {
                return 'Sales Return Invoice';
            } else {
                return 'Invoice';
            }
        },
    }
};
</script>
