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
                <h4>Payment</h4>
                <div class="wperp-row">
                    <div class="wperp-col-sm-6">
                        <h5>Payment From:</h5>
                        <div class="persons-info">
                            <strong>{{ payment.customer_name }}</strong><br>
                            <!-- {{ payment.billing_address }} -->
                        </div>
                    </div>
                    <div class="wperp-col-sm-6">
                        <table class="invoice-info">
                            <tr>
                                <th>Payment No.</th>
                                <td>#{{ payment.voucher_no }}</td>
                            </tr>
                            <tr>
                                <th>Payment Date:</th>
                                <td>{{ payment.trn_date }}</td>
                            </tr>
                            <tr>
                                <th>Deposit To:</th>
                                <td>Bank</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="wperp-invoice-table">
                <table class="wperp-table wperp-form-table invoice-table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr :key="index" v-for="(detail, index) in payment.line_items">
                            <th>#{{ detail.invoice_no }}</th>
                            <td>{{ getCurrencySign() + detail.amount }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <ul>
                                    <li><span>Subtotal:</span> {{ getCurrencySign() + payment.amount }}</li>
                                    <li><span>Total:</span> {{ getCurrencySign() + payment.amount }}</li>
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
               v-for="(attachment, index) in payment.attachments" download>
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
        name: 'PaymentSingleContent',

        props: {
            payment: {
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
        }

    }
</script>
