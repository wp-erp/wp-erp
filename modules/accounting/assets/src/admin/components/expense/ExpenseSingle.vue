<template>
    <div class="wperp-modal-dialog expense-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4>Expense</h4>
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
                        <h4>Expense</h4>
                        <div class="wperp-row" v-if="null != expense_data">
                            <div class="wperp-col-sm-6">
                                <div class="persons-info">
                                    <strong>{{ expense_data.vendor_name }}</strong><br>
                                    {{ expense_data.billing_address }}
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>Expense No</th>
                                        <td>#{{ expense_data.voucher_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expense Date:</th>
                                        <td>{{ expense_data.trn_date }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount Due:</th>
                                        <td>{{ getCurrencySign() + expense_data.due }}</td>
                                    </tr>
                                    <tr>
                                        <th>Due Date:</th>
                                        <td>{{ expense_data.due_date }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != expense_data">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                            <tr>
                                <th>Ledger ID</th>
                                <th>Voucher No</th>
                                <th>Particulars</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr :key="index" v-for="(line, index) in expense_data.bill_details">
                                <td>{{ line.ledger_id}}</td>
                                <td>{{ line.trn_no }}</td>
                                <td>{{ line.particulars }}</td>
                                <td>{{ getCurrencySign() + line.amount }}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7">
                                    <ul>
                                        <li><span>Subtotal:</span> {{ getCurrencySign() + expense_data.total }}</li>
                                        <li><span>Total:</span> {{ getCurrencySign() + expense_data.total }}</li>
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
                       v-for="(attachment, index) in expense_data.attachments" download>
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

    export default {
        name: 'ExpenseSingle',

        data() {
            return {
                company : null,
                expense_data : {},
                isWorking : false,
                acct_var : erp_acct_var,
            }
        },

        created() {
            this.getCompanyInfo();
            this.getExpense();
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then(response => {
                    this.company = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getExpense() {
                this.isWorking = true;

                HTTP.get(`/expenses/${this.$route.params.id}`).then(response => {
                    this.expense_data = response.data;
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
    .expense-single {
        width: 800px;
        margin: 40px 0;
    }
</style>

