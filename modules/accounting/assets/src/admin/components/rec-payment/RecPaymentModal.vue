<template>
    <div class="wperp-modal wperp-modal-open wperp-invoice-modal wperp-custom-scroll" role="dialog">
        <div class="wperp-modal-dialog" v-click-outside="outside" @click="inside">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <h4>
                        Payment
                    </h4>
                    <div class="d-print-none">
                        <a href="#" class="wperp-btn btn--default print-btn">
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
                        <div class="invoice-header">
                            <div class="invoice-logo">
                            </div>
                            <div class="invoice-address">
                                <address>
                                    <strong>Google LLC.</strong><br>
                                    983 Aiden Roads Suite 062<br>
                                    Address Line 2<br>
                                    1483 Theresafort Afyon<br>
                                    Turkey
                                </address>
                            </div>
                        </div>

                        <div class="invoice-body">
                            <h4>Invoice</h4>
                            <div class="wperp-row">
                                <div class="wperp-col-sm-6">
                                    <h5>Bill to:</h5>
                                    <div class="persons-info">
                                        <strong>A Customer</strong><br>
                                        983 Aiden Roads Suite 062<br>
                                        Address Line 2<br>
                                        1483 Theresafort Afyon<br>
                                        Turkey
                                    </div>
                                </div>
                                <div class="wperp-col-sm-6">
                                    <table class="invoice-info">
                                        <tr>
                                            <th>Reference No</th>
                                            <td>PAYMENT-0001</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date:</th>
                                            <td>17-10-2018</td>
                                        </tr>
                                        <tr>
                                            <th>Amount Due:</th>
                                            <td>2000</td>
                                        </tr>
                                        <tr>
                                            <th>Deposit to</th>
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
                                    <th>ID</th>
                                    <th>Invoice ID</th>
                                    <th>Total</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :key="key" v-for="(payment,key) in payments">
                                    <td>{{payment.id}}</td>
                                    <td>{{payment.invoice_no}}</td>
                                    <td>{{payment.amount}}</td>
                                    <td>{{payment.line_total}}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <ul>
                                            <li><span>Subtotal:</span>{{total}}</li>
                                            <li><span>Total:</span>{{total}}</li>
                                        </ul>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'

    export default {
        name: "RecPaymentModal",

        data() {
            return {
                entry_id: 0,
                trn_date: '',
                particulars: '',
                payments: [{}],
                total: 0,
                acct_var: erp_acct_var
            }
        },

        created() {
            this.entry_id = this.$route.params.id;

            HTTP.get(`/payments/${this.entry_id}`).then((response) => {
                var total = 0

                response.data.line_items.forEach(element => {
                    total += parseFloat(element.amount);

                    this.payments.push({
                        id: element.id,
                        invoice_no: parseFloat(element.invoice_no),
                        amount: parseFloat(element.amount),
                        line_total: parseFloat(element.amount),
                        total: total
                    });
                });
                this.total = total;
            });
        },

        methods: {
            inside() {},

            outside() {
                this.$root.$emit('payment-modal-close');
            }
        },
    }
</script>

<style scoped>

</style>
