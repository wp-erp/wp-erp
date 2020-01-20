<template>
    <div class="wperp-container accordion-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Opening Balances', 'erp') }}</h2>
                </div>
            </div>
        </div> <!-- End .header-section -->

        <show-errors :error_msgs="form_errors" ></show-errors>

        <form action="" method="post" @submit.prevent="submitOBForm">
            <div class="wperp-row">
                <div class="wperp-col-sm-6 with-multiselect opening-fyear-select">
                    <label>{{ __('Financial Year', 'erp') }}</label>
                    <multi-select v-model="fin_year" :options="years" />
                </div>

                <div class="wperp-col-sm-6">
                    <a href="#" class="wperp-col-sm-4 wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; {{ __('Print', 'erp') }}
                    </a>
                </div>
            </div>

            <div class="wperp-row">
                <ul class="report-header" v-if="null !== fin_year">
                    <li><strong>{{ __('For the period of ( Opening Balance date )', 'erp') }}:</strong> <em>{{ fin_year.start_date }}</em> to <em>{{ fin_year.end_date }}</em></li>
                </ul>
            </div>

            <!-- Accounts Receivable Section -->
            <div class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open5=!open5"
                     :class="open5?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{ __('Accounts Receivable', 'erp') }}</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open5">
                    <thead>
                    <tr>
                        <th>{{ __('People', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}
                            <span v-if="accPayRec && '0' != accPayRec.invoice_acc">({{ accPayRec.invoice_acc }})</span>
                        </th>
                        <th>{{ __('Credit', 'erp') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in acct_rec">
                        <td><div class="wperp-form-group ob-people with-multiselect">
                            <multi-select v-model="acct.people" :options="options" /></div></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="acct.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" disabled v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeAcctRecRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td v-if="undefined === acct_rec" style="float: left;">
                            {{ __( 'No People Found!', 'erp' ) }}
                        </td>
                        <td v-else colspan="9" style="text-align: left;">
                            <button @click.prevent="acct_rec.push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>{{ __('Add People', 'erp') }}</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Accounts Payable Section -->
            <div v-if="acct_pay" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open6=!open6"
                     :class="open6?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{ __('Accounts Payable', 'erp') }}</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open6">
                    <thead>
                    <tr>
                        <th>{{ __('People', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}</th>
                        <th>{{ __('Credit', 'erp') }}
                            <span v-if="accPayRec && '0' != accPayRec.bill_purchase_acc">({{accPayRec.bill_purchase_acc }})</span>
                        </th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in acct_pay">
                        <td><div class="wperp-form-group ob-people with-multiselect">
                            <multi-select v-model="acct.people" :options="options" /></div></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" disabled v-model="acct.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeAcctPayRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td v-if="undefined === acct_pay" style="float: left;">
                            {{ __( 'No People Found!', 'erp' ) }}
                        </td>
                        <td v-else colspan="9" style="text-align: left;">
                            <button @click.prevent="acct_pay.push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>{{ __('Add People', 'erp') }}</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tax Payable Section -->
            <div v-if="tax_pay" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open7=!open7"
                     :class="open7?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{ __('Tax Payable', 'erp') }}</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open7">
                    <thead>
                    <tr>
                        <th>{{ __('Agency', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}</th>
                        <th>{{ __('Credit', 'erp') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in tax_pay">
                        <td><div class="with-multiselect"><multi-select v-model="acct.agency" :options="agencies"/></div></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" disabled v-model="acct.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeTaxPayRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="tax_pay.push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>{{ __('Add Agency', 'erp') }}</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Assets Section -->
            <div v-if="chartAccounts[0]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open1=!open1"
                     :class="open1?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[0].label}}</span>
                </div>
                <table v-if="ledgers[1]" class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open1">
                    <thead>
                    <tr>
                        <th>{{ __('Account', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}</th>
                        <th>{{ __('Credit', 'erp') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[1]">
                        <td>{{ledger.name}}</td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Liability Section -->
            <div v-if="chartAccounts[1]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open2=!open2"
                     :class="open2?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[1].label}}</span>
                </div>
                <table v-if="ledgers[2]" class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open2">
                    <thead>
                    <tr>
                        <th>{{ __('Account', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}</th>
                        <th>{{ __('Credit', 'erp') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[2]">
                        <td>{{ledger.name}}</td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Equity Section -->
            <div v-if="chartAccounts[2]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open3=!open3"
                     :class="open3?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[2].label}}</span>
                </div>
                <table v-if="ledgers[3]" class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open3">
                    <thead>
                    <tr>
                        <th>{{ __('Account', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}</th>
                        <th>{{ __('Credit', 'erp') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[3]">
                        <td>{{ledger.name}}</td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bank Section -->
            <div v-if="chartAccounts[6]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open4=!open4"
                     :class="open4?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[6].label}}</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open4">
                    <thead>
                    <tr>
                        <th>{{ __('Account', 'erp') }}</th>
                        <th>{{ __('Debit', 'erp') }}</th>
                        <th>{{ __('Credit', 'erp') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in ledgers[7]">
                        <td><div class="wperp-form-group ob-people with-multiselect">
                            <multi-select v-model="acct.bank" :options="banks" /></div></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="acct.debit"></td>
                        <td><input type="number" class="wperp-form-field" @keyup="calculateAmount" v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeBankRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td v-if="undefined === ledgers[7]" style="float: left;">
                           {{ __( 'No Bank Account Found!', 'erp' ) }}
                        </td>
                        <td v-else colspan="9" style="text-align: left;">
                            <button @click.prevent="ledgers[7].push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>{{ __('Add Bank', 'erp') }}</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <table class="wperp-table wperp-form-table">
                <tbody>
                <tr class="total-amount-row">
                    <td class="pl-10 text-right col--total-amount" style="width: 60%;">
                        <span>{{ __('Total Amount', 'erp') }}</span>
                    </td>
                    <td data-colname="Total Debit">
                        <input type="text" class="text-right wperp-form-field" :value="moneyFormat(finalTotalDebit)" readonly>
                    </td>
                    <td data-colname="Total Credit">
                        <input type="text" class="text-right wperp-form-field" :value="moneyFormat(finalTotalCredit)" readonly>
                    </td>
                </tr>
                <tr class="wperp-form-group">
                    <td colspan="9" style="text-align: left;">
                        <label>{{ __('Description', 'erp') }}</label>
                        <textarea v-model="description" rows="4" class="wperp-form-field display-flex"
                        :placeholder="__('Internal Information', 'erp')"></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
            <submit-button :text="__( 'Save', 'erp' )"></submit-button>

        </form>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import SubmitButton from 'admin/components/base/SubmitButton.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';

export default {
    name: 'OpeningBalance',

    components: {
        MultiSelect,
        SubmitButton,
        ShowErrors
    },

    props: {
        title: {
            type: String,
            default: 'title'
        },
        animation: {
            type: String,
            default: 'rightToLeft'
        }
    },

    data() {
        return {
            open1        : true,
            open2        : true,
            open3        : true,
            open4        : true,
            open5        : true,
            open6        : true,
            open7        : true,
            form_errors  : [],
            chartAccounts: [],
            ledgers      : [],
            agencies     : null,
            banks        : [],
            people       : [],
            options      : [],
            fin_year     : null,
            years        : [],
            description  : '',
            all_ledgers  : [],
            credit_total : 0,
            debit_total  : 0,
            isWorking    : false,
            acct_rec     : [],
            acct_pay     : [],
            tax_pay      : [],
            totalDebit   : 0,
            totalCredit  : 0,
            accPayRec    : null
        };
    },

    watch: {
        isWorking(newval) {
            this.isWorking = newval;
        },

        fin_year(newVal) {
            this.getSelectedOB(newVal);
            this.getOpbAccountDetailsPayableReceivable(newVal.start_date);
        }
    },

    computed: {
        finalTotalDebit() {
            let invoice_acc_details = 0;

            if (this.accPayRec !== null && this.accPayRec.invoice_acc !== '0') {
                invoice_acc_details = this.accPayRec.invoice_acc;
            }

            return this.totalDebit + invoice_acc_details;
        },

        finalTotalCredit() {
            let bill_purchase_acc_details = 0;

            if (this.accPayRec !== null && this.accPayRec.bill_purchase_acc !== '0') {
                bill_purchase_acc_details = this.accPayRec.bill_purchase_acc;
            }

            return this.totalCredit + bill_purchase_acc_details;
        }
    },

    created() {
        this.fetchData();
    },

    methods: {
        groupBy(arr, fn) { /* https://30secondsofcode.org/ */
            return arr.map(typeof fn === 'function' ? fn : val => val[fn]).reduce((acc, val, i) => {
                acc[val] = (acc[val] || []).concat(arr[i]);
                return acc;
            }, {});
        },

        getOpbAccountDetailsPayableReceivable(startDate) {
            HTTP.get('/opening-balances/acc-payable-receivable', {
                params: {
                    start_date: startDate
                }
            }).then(response => {
                this.accPayRec = response.data;
            });
        },

        fetchData() {
            this.chartAccounts = [];
            this.$store.dispatch('spinner/setSpinner', true);
            this.getYears();
            this.fetchLedgers();
            this.fetchAgencies();
            this.fetchBanks();
            this.getPeople();
            HTTP.get('/ledgers/accounts').then(response => {
                this.chartAccounts = response.data;

                this.getSelectedOB(this.fin_year);

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        fetchLedgers() {
            HTTP.get('/ledgers').then(response => {
                response.data.forEach((ledger) => {
                    ledger.ledger_id = ledger.id;
                    ledger.balance = this.transformBalance(ledger.balance);
                });
                this.ledgers = this.groupBy(response.data, 'chart_id');
                this.all_ledgers = response.data;
            });
        },

        fetchAgencies() {
            HTTP.get('/tax-agencies').then((response) => {
                this.agencies = response.data;
            }).catch(error => {
                throw error;
            });
        },

        fetchBanks() {
            HTTP.get('/ledgers/7/accounts').then((response) => {
                this.banks = response.data;
            }).catch(error => {
                throw error;
            });
        },

        transformBalance(val) {
            if (val === null && typeof val === 'object') {
                val = 0;
            }
            const currency = '$';
            if (val < 0) {
                return `Cr. ${currency}${Math.abs(val)}`;
            }

            return `Dr. ${currency}${val}`;
        },

        calculateAmount() {
            this.debit_total = 0; this.credit_total = 0;

            for (const key in this.ledgers) {
                for (let idx = 0; idx < this.ledgers[key].length; idx++) {
                    if (Object.prototype.hasOwnProperty.call(this.ledgers[key][idx], 'debit')) {
                        if (this.ledgers[key][idx].debit === '') {
                            this.ledgers[key][idx].debit = 0;
                        }
                        this.debit_total += parseFloat(this.ledgers[key][idx].debit);
                    }
                    if (Object.prototype.hasOwnProperty.call(this.ledgers[key][idx], 'credit')) {
                        if (this.ledgers[key][idx].credit === '') {
                            this.ledgers[key][idx].credit = 0;
                        }
                        this.credit_total += parseFloat(this.ledgers[key][idx].credit);
                    }
                }
            }

            for (const key in this.acct_rec) {
                if (this.acct_rec[key].debit === '') {
                    this.acct_rec[key].debit = 0;
                }
                this.debit_total += parseFloat(this.acct_rec[key].debit);
            }

            for (const key in this.acct_pay) {
                if (this.acct_pay[key].credit === '') {
                    this.acct_pay[key].credit = 0;
                }
                this.credit_total += parseFloat(this.acct_pay[key].credit);
            }

            for (const key in this.tax_pay) {
                if (this.tax_pay[key].credit === '') {
                    this.tax_pay[key].credit = 0;
                }
                this.credit_total += parseFloat(this.tax_pay[key].credit);
            }

            const diff = Math.abs(this.debit_total - this.credit_total);

            this.totalDebit = this.debit_total;
            this.totalCredit = this.credit_total;
            this.isWorking = true;
            if (diff === 0) {
                this.isWorking = false;
            }
        },

        validateForm() {
            this.form_errors = [];

            this.acct_rec.forEach((element) => {
                if (typeof element !== 'undefined' && !Object.prototype.hasOwnProperty.call(element, 'people')) {
                    this.form_errors.push('People is not selected in Accounts Receivable.');
                }
            });

            this.acct_pay.forEach((element) => {
                if (typeof element !== 'undefined' && !Object.prototype.hasOwnProperty.call(element, 'people')) {
                    this.form_errors.push('People is not selected in Accounts Payable.');
                }
            });

            this.tax_pay.forEach((element) => {
                if (typeof element !== 'undefined' && !Object.prototype.hasOwnProperty.call(element, 'agency')) {
                    this.form_errors.push('Agency is not selected in Tax Payable.');
                }
            });

            if (!Object.prototype.hasOwnProperty.call(this.fin_year, 'id')) {
                this.form_errors.push('Financial year is required.');
            }

            if (this.isWorking) {
                this.form_errors.push('Debit and Credit must be Equal.');
            }
        },

        submitOBForm() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });
                return;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.post('/opening-balances', {
                year: this.fin_year.id,
                ledgers: this.ledgers,
                acct_pay: this.acct_pay,
                acct_rec: this.acct_rec,
                tax_pay: this.tax_pay,
                total_dr: this.totalDebit,
                total_cr: this.totalCredit,
                description: this.description

            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', 'Opening Balance Created!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.isWorking = false;
            });
        },

        getYears() {
            HTTP.get('/opening-balances/names').then(response => {
                this.years = response.data;
                this.fin_year = this.years.length ? this.years[0] : null;
            });
        },

        getPeople() {
            HTTP.get('/people', {
                params: {
                    type: []
                }
            }).then(response => {
                this.options = response.data;
            });
        },

        getSelectedOB(year) {
            this.acct_pay = []; this.acct_rec = []; this.tax_pay = [];

            let count = 0;

            HTTP.get(`/opening-balances/${year.id}/count`).then(response => {
                count = parseInt(response.data);
            }).then(() => {
                if (count === 0) {
                    this.fetchLedgers();
                } else {
                    HTTP.get(`/opening-balances/${year.id}`).then(response => {
                        this.totalDebit = 0;
                        this.totalCredit = 0;
                        response.data.forEach((ledger) => {
                            ledger.id = ledger.ledger_id;
                            ledger.balance = this.transformBalance(ledger.balance);
                            this.totalDebit += parseFloat(ledger.debit);
                            this.totalCredit += parseFloat(ledger.credit);
                        });
                        this.ledgers = this.groupBy(response.data, 'chart_id');
                        this.fetchVirtualAccts(year);
                    }).then(() => {
                        if (!Object.prototype.hasOwnProperty.call(this.ledgers, '7')) {
                            this.ledgers[7] = this.banks;
                        }
                    });

                    if (Object.keys(this.ledgers).length === 0) {
                        this.fetchData();
                    }
                }
            });
        },

        fetchVirtualAccts(year) {
            HTTP.get(`/opening-balances/virtual-accts/${year.id}`).then(response => {
                this.acct_pay = response.data.acct_payable;
                this.acct_rec = response.data.acct_receivable;
                this.tax_pay = response.data.tax_payable;
            }).then(() => {
                this.acct_pay.forEach((ledger) => {
                    this.totalCredit += parseFloat(ledger.credit);
                });
                this.acct_rec.forEach((ledger) => {
                    this.totalDebit += parseFloat(ledger.debit);
                });
                this.tax_pay.forEach((ledger) => {
                    this.totalCredit += parseFloat(ledger.credit);
                });
                this.$store.dispatch('spinner/setSpinner', false);
            });
        },

        printPopup() {
            window.print();
        },

        removeAcctRecRow(index) {
            this.$delete(this.acct_rec, index);
            this.calculateAmount();
        },

        removeAcctPayRow(index) {
            this.$delete(this.acct_pay, index);
            this.calculateAmount();
        },

        removeTaxPayRow(index) {
            this.$delete(this.tax_pay, index);
            this.calculateAmount();
        },

        removeBankRow(index) {
            this.$delete(this.banks, index);
            this.calculateAmount();
        }
    }

};
</script>

<style scoped>
    .accordion-container .erp-accordion table {
        width: calc(100% - 40px);
    }
    .wperp-form-group {
        margin: 0px !important;
    }
</style>

<style scoped lang="less">
    .report-header {
        width: 500px;
        padding: 10px;
        margin: 10px 0 0 0;

        li {
            display: flex;
            justify-content: space-between;
        }
    }
    .accordion-container {
        padding-top: 10px;
        .print-btn {
            float: right;
        }
        .wperp-custom-select {
            label {
                display: inline;
                font-weight: bold;
                padding-right : 5px;
            }
            select {
                background: #fff;
                border: none;
                box-shadow: none;
                margin-bottom: 10px;
                padding: 5px 15px;
                height: auto;
                width: 15%;
            }
        }
        .erp-accordion {
            background: #fff;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 18px -11px rgba(0,0,0,.25);
            table {
                padding: 0;
                margin: 20px;
                thead {
                    th {
                        &:first-child {
                            width: 40%;
                        }
                        &:not(:first-child) {
                            width: 20%;
                        }

                    }
                }
                tbody {
                    td input {
                        width: 100%;
                    }
                }
            }
        }

    }
    .wp-erp-ob-title{
        font-weight: bolder;
        color: #3f9ed4;
        font-size: larger;
    }
    .before-border {
        position: relative;
    }
    .before-border:before {
        transition: opacity 0.1s linear, transform 0.5s ease-in-out;
        position: absolute;
        content: '';
        width: 100%;
        left: 0;
        bottom: -1px;
    }
    .erp-accordion-expand {
        cursor: pointer;
        padding: .5rem .75rem;
        border-bottom: 1px solid #efefef;
    }
    .erp-accordion-expand-icon.open* {
        transform: rotate(180deg);
    }
    .erp-accordion-expand-body {
        padding: 1rem 1.5rem;
        background: #eff0f2;
    }

    .opening-fyear-select {
        .multiselect {
            width: 200px;
        }
    }
</style>
