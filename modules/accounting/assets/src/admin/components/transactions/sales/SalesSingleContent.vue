<template>
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
                <h4>{{ contentType }}</h4>
                <div class="wperp-row">
                    <div class="wperp-col-sm-6">
                        <h5>Bill to:</h5>
                        <div class="persons-info">
                            <strong>{{ content.customer_name }}</strong><br>
                            {{ content.billing_address }}
                        </div>
                    </div>
                    <div class="wperp-col-sm-6">
                        <table class="invoice-info">
                            <tr>
                                <th>{{ contentType }} No.</th>
                                <td>#{{ content.voucher_no }}</td>
                            </tr>
                            <tr>
                                <th>{{ contentType }} Date:</th>
                                <td>{{ content.trn_date }}</td>
                            </tr>
                            <tr v-if="'Invoice' == contentType">
                                <th>Due Date:</th>
                                <td>{{ content.due_date }}</td>
                            </tr>
                            <tr v-if="'Invoice' == contentType">
                                <th>Amount Due:</th>
                                <td>{{ getCurrencySign() + content.total_due }}</td>
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
                        <tr :key="index" v-for="(detail, index) in content.line_items">
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
                                    <li><span>Subtotal:</span> {{ getCurrencySign() + content.debit }}</li>
                                    <li><span>Total:</span> {{ getCurrencySign() + content.debit }}</li>
                                    <li><span>Total Related Payments:</span> {{ getCurrencySign() + content.debit }}</li>
                                </ul>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
        <!-- atttt -->

    </div>
</template>

<script>
    // Uses `getCurrencySign()` from common.js - mixin

    import HTTP from 'admin/http';

    export default {
        name: 'SalesSingleContent',

        props: {
            contentType: {
                type: String
            },
            content: {
                type: Object
            }
        },

        data() {
            return {
                company: null
            };
        },

        created() {
            this.getCompanyInfo();
        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then(response => {
                    this.company = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },
        }
    }
</script>

<style scoped>

</style>