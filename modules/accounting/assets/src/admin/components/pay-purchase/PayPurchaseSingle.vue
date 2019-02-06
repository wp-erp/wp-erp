<template>
    <div class="wperp-modal-dialog paypurchase-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4>Pay Purchase</h4>
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
                        <h4>Pay Purchase</h4>
                        <div class="wperp-row" v-if="null != payPurchase">
                            <div class="wperp-col-sm-6">
                                <div class="persons-info">
                                    <strong>{{ payPurchase.vendor_name }}</strong><br>
                                    {{ payPurchase.billing_address }}
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>Pay Purchase No</th>
                                        <td>#{{ payPurchase.voucher_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pay Purchase Date:</th>
                                        <td>{{ payPurchase.trn_date }}</td>
                                    </tr>
                                    <tr>
                                        <th>Deposit to</th>
                                        <td>Bank</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != payPurchase">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Purchase No</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr :key="index" v-for="(line, index) in payPurchase.purchase_details">
                                    <td>{{ line.id }}</td>
                                    <td>{{ line.purchase_no }}</td>
                                    <td>{{ getCurrencySign() + line.amount }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7">
                                    <ul>
                                        <li><span>Subtotal:</span> {{ getCurrencySign() + payPurchase.amount }}</li>
                                        <li><span>Total:</span> {{ getCurrencySign() + payPurchase.amount }}</li>
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
                       v-for="(attachment, index) in payPurchase.attachments" download>
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
        name: 'PayPurchaseSingle',

        data() {
            return {
                company  : null,
                payPurchase  : {},
                isWorking: false,
                acct_var : erp_acct_var,
            }
        },

        created() {
            this.getCompanyInfo();
            this.getPurchase();
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then(response => {
                    this.company = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getPurchase() {
                this.isWorking = true;

                HTTP.get(`/pay-purchases/${this.$route.params.id}`).then(response => {
                    this.payPurchase = response.data;
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
    .paypurchase-single {
        width: 800px;
        margin: 40px 0;
    }
</style>

