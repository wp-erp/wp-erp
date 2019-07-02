<template>
    <div class="wperp-modal-dialog sales-single">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h2>Transfer Money</h2>
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

           <div class="wperp-modal-body">
                <div class="wperp-invoice-panel">
                    <div class="invoice-header">
                        <div class="invoice-logo">
                            <img :src="company.logo" alt="logo name" width="100" height="100">
                        </div>
                        <div class="invoice-address">
                            <address v-if="company.address">
                                <strong >{{ company.name }}</strong><br>
                                {{ company.address.address_1 }}<br>
                                {{ company.address.address_2 }}<br>
                                {{ company.address.city }}<br>
                                {{ company.address.country }}
                            </address>
                        </div>
                    </div>

                    <div class="invoice-body">
                        <h4>Transfer Money</h4>
                        <div class="wperp-row" v-if="voucher.created_by">
                            <div class="wperp-col-sm-6">
                                <h5>Created By:</h5>
                                <div class="persons-info">
                                    <strong>{{ voucher.created_by.display_name }}</strong><br>
                                    {{ voucher.created_by.user_email }}
                                </div>
                            </div>
                            <div class="wperp-col-sm-6">
                                <table class="invoice-info">

                                    <tr>
                                        <th>Transaction Date:</th>
                                        <td>{{ voucher.trn_date }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="wperp-invoice-table">
                        <table class="wperp-table wperp-form-table invoice-table">
                            <thead>
                                <tr>
                                    <th>Voucher No</th>
                                    <th>Account From</th>
                                    <th>Amount</th>
                                    <th>Account To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>#{{ voucher.voucher }}</th>
                                    <th>{{ voucher.ac_from }}</th>
                                    <th>{{ voucher.amount }}</th>
                                    <th>{{ voucher.ac_to }}</th>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="wperp-invoice-amounts" colspan="7">
                                        <h2>Particulars</h2>
                                        <p v-if="voucher.particulars">{{ voucher.particulars }}</p>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

            </div>
            <send-mail v-if="showModal" :data="print_data"/>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import SendMail from 'admin/components/email/SendMail.vue'
    import Dropdown from 'admin/components/base/Dropdown.vue'
    export default {
        name: 'SingleTransfer',

        data() {
            return {
                voucher: {},
                company: '',
                showModal: false,
                print_data: null,
            }
        },

        components: {
            SendMail,
            Dropdown
        },

        created() {
            this.$store.dispatch( 'spinner/setSpinner', true );
            this.getCompanyInfo();
            this.getVoucher();

        },

        methods: {
            getCompanyInfo() {
                HTTP.get(`/company`).then(response => {
                    this.company = response.data;
                })
            },

            getVoucher() {
                HTTP.get(`/accounts/transfer/${this.$route.params.id}`).then(response => {
                    this.voucher = response.data;
                    this.print_data = this.voucher;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            },

            printPopup() {
                window.print();
            },
        },

    }
</script>

<style lang="less">
    .wperp-email-multiselect {
        .multiselect__content-wrapper {
            display: none !important;
            height: 0 !important;
            visibility: hidden;
        }
        .multiselect__tags {
            font-size: 12px;
            padding-left: 15px;
            border-radius: 3px;
            input {
                max-height: 30px;
                font-size: 12px;
            }
        }
        .multiselect__tag-icon {
            line-height: 18px;
        }
        .multiselect {
            input.multiselect__input {
                display: none;
            }
            &.multiselect--active input.multiselect__input {
                display: block;
                width: 100% !important;
            }
        }
    }


    .sales-single {
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

    .wperp-invoice-table td:last-child,
    .wperp-invoice-table th:last-child {
        width: 100px !important;
    }

    @media print {
        .erp-nav-container {
            display: none;
        }
    }
</style>

