<template>
    <div class="wperp-modal-dialog bill-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4>Bill</h4>
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
                        <h4>Bill</h4>
                        <div class="wperp-row" v-if="null != bill">
                            <div class="wperp-col-sm-6">
                                <div class="persons-info">
                                    <strong>{{ bill.vendor_name }}</strong><br>
                                    {{ bill.billing_address }}
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>Bill No</th>
                                        <td>#{{ bill.voucher_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>Bill Date:</th>
                                        <td>{{ bill.trn_date }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount Due:</th>
                                        <td>{{ getCurrencySign() + bill.due }}</td>
                                    </tr>
                                    <tr>
                                        <th>Due Date:</th>
                                        <td>{{ bill.due_date }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != bill">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ledger ID</th>
                                <th>Particulars</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :key="index" v-for="(line, index) in bill.bill_details">
                                <td>{{ line.id}}</td>
                                <td>{{ line.ledger_name }}</td>
                                <td>{{ line.particulars }}</td>
                                <td>{{ getCurrencySign() + line.amount }}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7">
                                    <ul>
                                        <li><span>Subtotal:</span> {{ getCurrencySign() + bill.amount }}</li>
                                        <li><span>Total:</span> {{ getCurrencySign() + bill.amount }}</li>
                                    </ul>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
                <div class="invoice-attachments d-print-none">
                    <h4>Attachments</h4>
                    <a class="attachment-item" href="#">
                        <img :src="acct_var.acct_assets + '/images/img-thumb.png'" alt="image name">
                        <div class="attachment-meta">
                            <span>File name with extension</span><br>
                            <span class="text-muted">file size</span>
                        </div>
                    </a>
                    <a class="attachment-item" href="#">
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
                    </a>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';

    export default {
        name: 'BillSingle',

        data() {
            return {
                company  : null,
                bill     : null,
                isWorking: false,
                acct_var : erp_acct_var,
            }
        },

        created() {
            this.getCompanyInfo();
            this.getBill();
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then(response => {
                    this.company = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getBill() {
                this.isWorking = true;

                HTTP.get(`/bills/${this.$route.params.id}`).then(response => {
                    this.bill = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            printPopup() {
                window.print();
            }
        },

    }
</script>


<style lang="less">
    .bill-single {
        width: 800px;
        margin: 40px 0;
    }
</style>

