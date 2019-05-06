<template>
    <div class="wperp-modal-body">
        <div class="wperp-invoice-panel">
            <div class="invoice-header">
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
                        <h5>Bill to:</h5>
                        <div class="persons-info">
                            <strong>{{ invoice.customer_name }}</strong><br>
                            {{ invoice.billing_address }}
                        </div>
                    </div>
                    <div class="wperp-col-sm-6">
                        <table class="invoice-info">
                            <tr>
                                <th>Invoice No.</th>
                                <td>#{{ invoice.voucher_no }}</td>
                            </tr>
                            <tr>
                                <th>Invoice Date:</th>
                                <td>{{ invoice.trn_date }}</td>
                            </tr>
                            <tr>
                                <th>Due Date:</th>
                                <td>{{ invoice.due_date }}</td>
                            </tr>
                            <tr>
                                <th>Amount Due:</th>
                                <td>{{ getCurrencySign() + invoice.total_due }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="wperp-invoice-table">
                <table class="wperp-table wperp-form-table invoice-table">
                    <thead>
                        <tr>
                            <th>Sl.</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr :key="index" v-for="(detail, index) in invoice.line_items">
                            <th>{{ index+1 }}</th>
                            <th>{{ detail.name }}</th>
                            <td>{{ detail.qty }}</td>
                            <td>{{ getCurrencySign() + detail.unit_price }}</td>
                            <td>{{ getCurrencySign() + detail.item_total }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="wperp-invoice-amounts" colspan="7">
                                <ul>
                                    <li><span>Subtotal:</span> {{ getCurrencySign() + invoice.amount }}</li>
                                    <li><span>Discount:</span> (-) {{ getCurrencySign() + invoice.discount }}</li>
                                    <li><span>Tax:</span> (+) {{ getCurrencySign() + invoice.tax }}</li>
                                    <li><span>Total:</span> {{ getCurrencySign() + invoice.amount }}</li>
                                </ul>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
        
        <div class="invoice-attachments d-print-none">
            <h4>Attachments</h4>
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
    // Uses `getCurrencySign()` from common.js - mixin

    import HTTP from 'admin/http';

    export default {
        name: 'InvoiceSingleContent',

        props: {
            invoice: {
                type: Object
            },
            company: {
                type: Object
            }
        },

        data() {
            return {
                acct_var: erp_acct_var
            }
        },

        methods: {
            getInvoiceType() {
                if ( this.invoice !== null && '1' === this.invoice.estimate ) {
                    return "Estimate";
                } else {
                    return "Invoice";
                }
            },
        }

    }
</script>
