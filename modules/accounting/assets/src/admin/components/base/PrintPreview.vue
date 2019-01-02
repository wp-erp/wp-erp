<template>
    <div class="wperp-modal wperp-modal-open wperp-invoice-modal wperp-custom-scroll" role="dialog">
        <div class="wperp-modal-dialog" v-click-outside="outside" @click="inside">
            <div class="wperp-modal-content">
                <div class="wperp-modal-header">
                    <h4>{{ type }}</h4>
                    <div class="d-print-none">
                        <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="window.print()">
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

                        <div class="invoice-body">
                            <h4>{{ type }}</h4>
                            <div class="wperp-row">
                                <div class="wperp-col-sm-6">
                                    <h5>Bill to:</h5>
                                    <div class="persons-info">
                                        <strong>{{ customer.customer.name }}</strong><br>
                                        {{ customer.billing_address }}
                                    </div>
                                </div>
                                <div class="wperp-col-sm-6">
                                    <table class="invoice-info">
                                        <tr>
                                            <th>{{ type }} No.</th>
                                            <td>#1</td>
                                        </tr>
                                        <tr>
                                            <th>{{ type }} Date:</th>
                                            <td>{{ new Date().toISOString().slice(0, 10) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Due Date:</th>
                                            <td>{{ new Date().toISOString().slice(0, 10) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Amount Due:</th>
                                            <td>{{ totalAmount }}</td>
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
                                    <tr :key="index" v-for="(transaction, index) in transactions">
                                        <th>{{ transaction.selectedProduct.name }}</th>
                                        <td>{{ transaction.qty }}</td>
                                        <td>{{ transaction.unitPrice }}</td>
                                        <td>{{ transaction.discount }}</td>
                                        <td>...</td>
                                        <td>{{ transaction.taxAmount }}</td>
                                        <td>{{ transaction.totalAmount }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <ul>
                                                <li><span>Subtotal:</span> {{ totalAmount }}</li>
                                                <li><span>Total:</span> {{ totalAmount }}</li>
                                                <li><span>Total Related Payments:</span> {{ totalAmount }}</li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                    <!-- <div class="invoice-attachments d-print-none" v-if="invoice.data != null">
                        <h4>Attachments</h4>
                        <a class="attachment-item" :href="attachment"
                            :key="index" 
                            v-for="(attachment, index) in invoice.data[0].attachments" download>
                            <img :src="acct_var.acct_assets + '/images/img-thumb.png'" alt="image name">
                            <div class="attachment-meta">
                                <span>File</span><br>
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
                    </div> -->

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';

    export default {
        name: 'PrintPreview',

        props: {
            customer: {
                type: Object
            },
            transactions: {
                type: Array
            },
            // id: {
            //     type: String,
            //     required: true,
            // },
            type: {
                type: String,
                required: true,
            },
            // totalDue: {
            //     type: String
            // },
            totalAmount: {
                type: [String, Number]
            }
        },

        data() {
            return {
                isWorking: false,
                company: null,
                invoice: [],
                acct_var: erp_acct_var
            }
        },

        created() {
            this.getCompanyInfo();
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then((response) => {
                    this.company = response.data;
                }).then( (e) => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            inside() {},

            outside() {
                this.$root.$emit('preview-modal-close');
            }
        },

    }
</script>
