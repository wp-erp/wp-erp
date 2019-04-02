<template>
    <div class="wperp-modal-dialog expense-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h2>Tax Payment</h2>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; Print
                    </a>
                    <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                    <dropdown>
                        <template slot="button">
                            <a href="#" class="wperp-btn btn--default">
                                <i class="flaticon-settings-work-tool"></i>
                                &nbsp; More Action
                            </a>
                        </template>
                        <template slot="dropdown">
                            <ul role="menu">
                                <li><a href="#" @click.prevent="showModal = true">Send Mail</a></li>
                            </ul>
                        </template>
                    </dropdown>
                </div>
            </div>

            <send-mail v-if="showModal" :data="print_data" :type="type"/>

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
                        <h4>Tax Payment to</h4>
                        <div class="wperp-row" v-if="null != tax_pay_data">
                            <div class="wperp-col-sm-6">
                                <div class="persons-info">
                                    <strong>{{ tax_pay_data.agency_id }}</strong><br>
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">
                                    <tr>
                                        <th>Voucher No</th>
                                        <td>#{{ tax_pay_data.voucher_no }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Date:</th>
                                        <td>{{ tax_pay_data.trn_date }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table" v-if="null != tax_pay_data">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                            <tr>
                                <th>Voucher No</th>
                                <th>Account</th>
                                <th>Voucher Type</th>
                                <th>Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ tax_pay_data.voucher_no }}</td>
                                <td>{{ tax_pay_data.ledger_id}}</td>
                                <td>{{ tax_pay_data.voucher_type }}</td>
                                <td>{{ getCurrencySign() + tax_pay_data.amount }}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7">
                                    <ul>
                                        <li><span>Total:</span> {{ getCurrencySign() + tax_pay_data.amount }}</li>
                                    </ul>
                                </td>
                            </tr>
                            <br/>
                            <tr>
                                <td class="wperp-invoice-amounts" colspan="7">
                                    <h2>Particulars</h2>
                                    <p v-if="tax_pay_data.particulars">{{ tax_pay_data.particulars }}</p>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>



            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import SendMail from 'admin/components/email/SendMail.vue';
    import Dropdown from 'admin/components/base/Dropdown.vue';

    export default {
        name: 'PayTaxSingle',

        components: {
            HTTP,
            SendMail,
            Dropdown
        },

        data() {
            return {
                company : null,
                tax_pay_data : {},
                isWorking : false,
                acct_var : erp_acct_var,
                print_data : null,
                type       : 'expense',
                showModal  : false
            }
        },

        created() {
            this.$store.dispatch( 'spinner/setSpinner', true );
            this.getCompanyInfo();
            this.getExpense();

            this.$root.$on( 'close', () => {
                this.showModal = false;
            })
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

                HTTP.get(`/taxes/tax-records/${this.$route.params.id}`).then(response => {
                    this.tax_pay_data = response.data;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then( e => {} ).then(() => {
                    this.print_data = this.tax_pay_data;
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
        max-width: 960px;
        margin: 0 auto;
        .wperp-modal-footer {
            border-top: 1px solid #e2e2e2;
        }
        .wperp-modal-header {
            border-bottom: 1px solid #e2e2e2;
        }
        .wperp-form-field, input:not(.wperp-btn) {
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }
    }

    @media print {
        .erp-nav-container {
            display: none;
        }
    }
</style>

