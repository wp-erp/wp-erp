<template>
    <div class="wperp-modal-dialog sales-report">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4>Invoice</h4>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; Print
                    </a>
                    <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                    <a href="#" class="wperp-btn btn--default">
                        <i class="flaticon-settings-work-tool"></i>
                        &nbsp; More Action
                    </a>
                </div>
            </div>
            <div class="wperp-modal-body">
                <div class="wperp-invoice-panel">
                    <div class="invoice-header" v-if="company != null">
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

                    <div class="invoice-body" v-if="invoice.data != null">
                        <h4>Invoice</h4>
                        <div class="wperp-row">
                            <div class="wperp-col-sm-6">
                                <h5>Bill to:</h5>
                                <div class="persons-info">
                                    <strong>{{ getInvoiceInfo('customer_name') }}</strong><br>
                                    {{ getInvoiceInfo('billing_address') }}
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>Invoice No.</th>
                                        <td>#{{ getInvoiceInfo('id') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Invoice Date:</th>
                                        <td>{{ getInvoiceInfo('created_at') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Due Date:</th>
                                        <td>{{ getInvoiceInfo('due_date') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount Due:</th>
                                        <td>{{ getCurrencySign() + getInvoiceInfo('total_due') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Discount</th>
                                    <th>Tax</th>
                                    <th>Tax Amount</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr :key="index" v-for="(detail, index) in invoice.data">
                                    <th>{{ detail.name }}</th>
                                    <td>{{ detail.qty }}</td>
                                    <td>{{ detail.unit_price }}</td>
                                    <td>{{ detail.discount }}</td>
                                    <td>...</td>
                                    <td>{{ detail.tax }}</td>
                                    <td>{{ detail.line_total }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <ul>
                                            <li><span>Subtotal:</span> {{ getCurrencySign() + getInvoiceInfo('debit') }}</li>
                                            <li><span>Total:</span> {{ getCurrencySign() + getInvoiceInfo('debit') }}</li>
                                            <li><span>Total Related Payments:</span> {{ getCurrencySign() + getInvoiceInfo('debit') }}</li>
                                        </ul>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
                <div class="invoice-attachments d-print-none" v-if="invoice.data != null">
                    <h4>Attachments</h4>
                    <a class="attachment-item" :href="attachment"
                        :key="index" 
                        v-for="(attachment, index) in invoice.data[0].attachments" download>
                        <img :src="acct_var.acct_assets + '/images/img-thumb.png'" alt="image name">
                        <div class="attachment-meta">
                            <span>File</span><br>
                            <!-- <span class="text-muted">file size</span> -->
                        </div>
                    </a>
                    <!-- <a class="attachment-item" href="#">
                        <img :src="acct_var.acct_assets + '/images/doc-thumb.png'" alt="image name">
                        <div class="attachment-meta">
                            <span>File name with extension</span><br>
                            <span class="text-muted">file size</span>
                        </div>
                    </a>
                    <a class="attachment-item" href="#">
                        <img :src="acct_var.acct_assets + '/images/pdf-thumb.png'" alt="image name">
                        <div class="attachment-meta">
                            <span>File name with extension</span><br>
                            <span class="text-muted">file size</span>
                        </div>
                    </a> -->
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    // Uses `getCurrencySign()` from common.js - mixin

    import HTTP from 'admin/http';

    export default {
        name: 'SalesReport',

        data() {
            return {
                isWorking: false,
                company: null,
                invoice: [],
                acct_var: erp_acct_var,
            }
        },

        created() {
            this.getCompanyInfo();

            // if ( 'sales_invoice' == true ) {
                this.getInvoice();
            // }
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then((response) => {
                    this.company = response.data;
                }).then( (e) => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getInvoice() {
                this.isWorking = true;

                HTTP.get(`/invoices/${this.$route.params.id}`).then(response => {
                    this.invoice = response.data;
                }).then( (e) => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getInvoiceInfo(prop) {
                return this.invoice.data[0][prop];
            },

            printPopup() {
                window.print();
            }
        },

    }
</script>

<style lang="less">
    .sales-report {
        width: 800px;
        margin: 40px 0;
    }

    @media print {
        .erp-nav-container {
            display: none;
        }
    }
</style>

