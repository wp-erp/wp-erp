<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Record Sales Tax Payment', 'erp') }}</h2>
                </div>
            </div>
        </div>

        <div class="wperp-panel wperp-panel-default pb-0">
            <div class="wperp-panel-body">
                <form action="#" class="wperp-form" method="post" @submit.prevent="submitForTaxPay">

                    <show-errors :error_msgs="form_errors" />

                    <div class="wperp-row wperp-gutter-20">

                        <div class="wperp-col-sm-4 with-multiselect">
                            <label>{{ __('Payment Method', 'erp') }}<span class="wperp-required-sign">*</span></label>
                            <multi-select v-model="trn_by" :options="pay_methods"></multi-select>
                        </div>
                        <div class="wperp-col-sm-4 mb-20">
                            <label>
                                <span v-if="'debit' == voucher_type.id">{{ __('Payment From', 'erp') }}</span>
                                <span v-else>{{ __('Deposit To', 'erp') }}</span>
                                <span class="wperp-required-sign">*</span>
                            </label>

                            <select-accounts v-model="deposit_to" :override_accts="accts_by_chart"></select-accounts>
                        </div>
                        <div class="wperp-col-sm-4 wperp-col-xs-12">
                            <div class="wperp-form-group">
                                <label>{{ __('Payment Date', 'erp') }}</label>
                                <div class="wperp-has-datepicker pay-tax-date">
                                    <datepicker v-model="trn_date"></datepicker>
                                </div>
                            </div>
                        </div>

                        <div class="wperp-col-sm-4 wperp-col-xs-12">
                            <div class="wperp-form-group with-multiselect">
                                <label>{{ __('Payment To', 'erp') }}</label>
                                <multi-select v-model="agency" :options="agencies" />
                            </div>
                        </div>
                        <div class="wperp-col-sm-4 wperp-col-xs-12">
                            <div class="wperp-form-group">
                                <label>{{ __('Tax Amount', 'erp') }}</label>
                                <input type="number" v-model="tax_amount" class="wperp-form-field" :placeholder="__('Enter Tax Amount', 'erp')">

                                <span>{{ __('Due Amount', 'erp') }}: <span class="text-theme">{{ moneyFormat(dueAmount) }}</span></span>
                            </div>
                        </div>
                        <div class="wperp-col-sm-4 wperp-col-xs-12">
                            <div class="wperp-form-group with-multiselect">
                                <label>{{ __('Voucher Type', 'erp') }}</label>
                                <multi-select v-model="voucher_type" :options="voucher_types" :placeholder="__('Enter Voucher Type', 'erp')" />
                            </div>
                        </div>

                        <div class="wperp-col-xs-12">
                            <label>{{ __('Particulars', 'erp') }}</label>
                            <textarea rows="3" v-model="particulars" maxlength="250" class="wperp-form-field" :placeholder="__('Enter Particulars', 'erp')"></textarea>
                        </div>
                        <div class="wperp-col-xs-12">
                            <div class="wperp-form-group text-right mt-10 mb-0">
                                <submit-button :text="__( 'Save', 'erp' )" :working="isWorking"></submit-button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</template>

<script>
import HTTP from 'admin/http';
import Datepicker from 'admin/components/base/Datepicker.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import SubmitButton from 'admin/components/base/SubmitButton.vue';
import SelectAccounts from 'admin/components/select/SelectAccounts.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';

export default {
    name: 'RecordPayTax',

    components: {
        Datepicker,
        MultiSelect,
        SubmitButton,
        SelectAccounts,
        ShowErrors
    },

    data() {
        return {
            agencies      : [],
            pay_methods   : [],
            accts_by_chart: [],
            trn_by        : { id: null, name: null },
            deposit_to    : { id: null, name: null },
            agency        : { id: null, name: null },
            tax_amount    : 0,
            dueAmount     : 0,
            particulars   : '',
            isWorking     : false,
            form_errors   : [],
            trn_date      : erp_acct_var.current_date, /* global erp_acct_var */
            voucher_type  : { id: 'debit', name: 'Debit' },
            voucher_types : [
                { id: 'debit', name: 'Debit' },
                { id: 'credit', name: 'Credit' }
            ]
        };
    },

    watch: {
        agency() {
            this.getDuePayAmount();
        },

        trn_by() {
            this.changeAccounts();
        }
    },

    created() {
        this.getPayMethods();
        this.getAgencies();
    },

    methods: {
        getPayMethods() {
            HTTP.get('/transactions/payment-methods').then(response => {
                this.pay_methods = response.data;
            });
        },

        changeAccounts() {
            this.accts_by_chart = [];
            if (this.trn_by.id === '2' || this.trn_by.id === '3') {
                HTTP.get('/ledgers/bank-accounts').then((response) => {
                    this.accts_by_chart = response.data;
                    this.accts_by_chart.forEach(element => {
                        if (!Object.prototype.hasOwnProperty.call(element, 'balance')) {
                            element.balance = 0;
                        }
                    });
                });
            } else if (this.trn_by.id === '1') {
                HTTP.get('/ledgers/cash-accounts').then((response) => {
                    this.accts_by_chart = response.data;
                    this.accts_by_chart.forEach(element => {
                        if (!Object.prototype.hasOwnProperty.call(element, 'balance')) {
                            element.balance = 0;
                        }
                    });
                });
                /* global erp_reimbursement_var */
            } else if (erp_reimbursement_var.erp_reimbursement_module !== 'undefined' &&  erp_reimbursement_var.erp_reimbursement_module === '1') {
                HTTP.get('/people-transactions/balances').then((response) => {
                    this.accts_by_chart = response.data;
                    this.accts_by_chart.forEach(element => {
                        if (!Object.prototype.hasOwnProperty.call(element, 'balance')) {
                            element.balance = 0;
                        }
                    });
                });
            }
            this.$root.$emit('account-changed');
        },

        getAgencies() {
            HTTP.get('/tax-agencies').then(response => {
                this.agencies = response.data;
            });
        },

        getDuePayAmount() {
            if (!this.agency.id) return;

            // ? or... we could bring due along with agencies
            HTTP.get(`/tax-agencies/due/${this.agency.id}`).then(response => {
                this.dueAmount = parseFloat(response.data);
            });
        },

        submitForTaxPay() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.post('/taxes/pay-tax', {
                agency_id   : this.agency.id,
                trn_date    : this.trn_date,
                trn_by      : this.trn_by.id,
                ledger_id   : this.deposit_to.id,
                particulars : this.particulars,
                voucher_type: this.voucher_type.id,
                amount      : parseFloat(this.tax_amount)
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Tax Paid!');
            }).then(() => {
                this.$router.push({ name: 'TaxRecords' });
                this.resetData();
                this.isWorking = false;
            });
        },

        resetData() {
            this.trn_by       = { id: null, name: null };
            this.deposit_to   = { id: null, name: null };
            this.agency       = { id: null, name: null };
            this.tax_amount   = 0;
            this.dueAmount    = 0;
            this.particulars  = '';
            this.isWorking    = false;
            this.form_errors  = [];
            this.trn_date     = erp_acct_var.current_date;
            this.voucher_type = { id: 'debit', name: 'Debit' };
        },

        validateForm() {
            this.form_errors = [];

            if (!this.trn_by.id) {
                this.form_errors.push('Payment method Name is required.');
            }

            if (!this.deposit_to.id) {
                this.form_errors.push('Deposit to is required.');
            }

            if (!this.agency.id) {
                this.form_errors.push('Agency to is required.');
            }

            if (!this.trn_date) {
                this.form_errors.push('Date is required.');
            }

            if (!this.tax_amount) {
                this.form_errors.push('Tax amount is required.');
            }

            // if ( this.tax_amount > this.dueAmount ) {
            //     this.form_errors.push('Please pay according to your due balance.');
            // }

            if (parseFloat(this.deposit_to.balance) < parseFloat(this.finalTotalAmount)) {
                this.form_errors.push('Not enough balance in selected account.');
            }
        }
    }

};
</script>

<style lang="less" scoped>
    .pay-tax-date {
        .wperp-has-dropdown {
            width: 100%;
        }
    }

    .text-theme {
        color: #1A9ED4;
        font-weight: 400;
        margin-left: 10px;
    }
</style>
