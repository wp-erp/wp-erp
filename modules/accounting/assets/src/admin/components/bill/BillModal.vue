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
                                <img :src="assets_url + '/images/dummy-logo.png'" alt="logo name">
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
                                    <th>Invoice ID</th>
                                    <th>Due Date</th>
                                    <th>Total</th>
                                    <th>Due</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr :key="key" v-for="(invoice,key) in invoices">
                                    <td>{{invoice.id}}</td>
                                    <td>{{invoice.due_date}}</td>
                                    <td>{{invoice.total}}</td>
                                    <td>$240.00</td>
                                    <td>{{invoice.total}}</td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <ul>
                                            <li><span>Subtotal:</span> $15,000.00</li>
                                            <li><span>Total:</span> $15,000.00</li>
                                            <li><span>Total Related Payments:</span> $15,000.00</li>
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
                            <img :src="assets_url + '/images/img-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File name with extension</span><br>
                                <span class="text-muted">file size</span>
                            </div>
                        </a>
                        <a class="attachment-item" href="#">
                            <img :src="assets_url + '/images/doc-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File name with extension</span><br>
                                <span class="text-muted">file size</span>
                            </div>
                        </a>
                        <a class="attachment-item" href="#">
                            <img :src="assets_url + '/images/pdf-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File name with extension</span><br>
                                <span class="text-muted">file size</span>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "BillModal",

        props: {
            basic_fields: Object,
            invoices: Array,
            attachments: Array,
            finalTotalAmount: [ String, Number ],
            assets_url: String
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
