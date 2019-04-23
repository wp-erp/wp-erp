<template>
    <div class="wperp-container accordion-container">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Opening Balances</h2>
                </div>
            </div>
        </div> <!-- End .header-section -->

        <show-errors :error_msgs="form_errors" ></show-errors>

        <form action="" method="post" @submit.prevent="submitOBForm">
            <div class="wperp-row">
                <div class="wperp-col-sm-6">
                    <label>Financial Year</label>
                    <simple-select
                        v-model="fin_year"
                        @input="getSelectedOB"
                        :width="200"
                        :options="years"
                    >
                    </simple-select>
                </div>

                <div class="wperp-col-sm-6">
                    <a href="#" class="wperp-col-sm-4 wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; Print
                    </a>
                </div>
            </div>


            <!-- Accounts Receivable Section -->
            <div class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open5=!open5"
                     :class="open5?'active':'before-border'">
                    <span class="wp-erp-ob-title">Accounts Receivable</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open5">
                    <thead>
                    <tr>
                        <th>People</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in acct_rec">
                        <td><div class="wperp-form-group ob-people with-multiselect">
                            <multi-select v-model="acct.people" :options="options" /></div></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="acct.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" disabled v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeAcctRecRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="acct_rec.push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add People</button>
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
                    <span class="wp-erp-ob-title">Accounts Payable</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open6">
                    <thead>
                    <tr>
                        <th>People</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in acct_pay">
                        <td><div class="wperp-form-group ob-people with-multiselect">
                            <multi-select v-model="acct.people" :options="options" /></div></td>
                        <td><input type="number" @keyup="calculateAmount" disabled v-model="acct.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeAcctPayRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="acct_pay.push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add People</button>
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
                    <span class="wp-erp-ob-title">Tax Payable</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open7">
                    <thead>
                    <tr>
                        <th>Agency</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in tax_pay">
                        <td><div class="with-multiselect"><multi-select v-model="acct.agency" :options="agencies"/></div></td>
                        <td><input type="number" @keyup="calculateAmount" disabled v-model="acct.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeAcctPayRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="tax_pay.push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add People</button>
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
                        <th>Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[1]">
                        <td>{{ledger.name}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
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
                        <th>Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[2]">
                        <td>{{ledger.name}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
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
                        <th>Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[3]">
                        <td>{{ledger.name}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
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
                        <th>Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(acct,idx) in ledgers[7]">
                        <td><div class="with-multiselect"><multi-select v-model="acct.bank" :options="banks"/></div></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="acct.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="acct.credit"></td>
                        <td class="delete-row" data-colname="Remove">
                            <a @click.prevent="removeBankRow(idx)" href="#"><i class="flaticon-trash"></i></a>
                        </td>
                    </tr>
                    <tr class="add-new-line">
                        <td colspan="9" style="text-align: left;">
                            <button @click.prevent="ledgers[7].push({})" class="wperp-btn btn--primary add-line-trigger"><i class="flaticon-add-plus-button"></i>Add Bank</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <table class="wperp-table wperp-form-table">
                <tbody>
                <tr class="total-amount-row">
                    <td class="pl-10 text-right col--total-amount" style="width: 60%;">
                        <span>Total Amount</span>
                    </td>
                    <td data-colname="Total Debit"><input type="text" class="text-right" :value="totalDebit" readonly ></td>
                    <td data-colname="Total Credit"><input type="text" class="text-right" :value="totalCredit" readonly ></td>
                </tr>
                <tr class="wperp-form-group">
                    <td colspan="9" style="text-align: left;">
                        <label>Description</label>
                        <textarea v-model="description" rows="4" class="wperp-form-field display-flex" placeholder="Internal Information"></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
            <submit-button text="Add Opening Balance"></submit-button>

        </form>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import SimpleSelect from 'admin/components/select/SimpleSelect.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: "OpeningBalance",

        components: {
            HTTP,
            SimpleSelect,
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
                open1: true,
                open2: true,
                open3: true,
                open4: true,
                open5: true,
                open6: true,
                open7: true,
                form_errors: [],
                chartAccounts: [],
                ledgers: [],
                agencies: null,
                banks: [],
                people: [],
                options: [],
                fin_year: {},
                years: [],
                description: '',
                all_ledgers: [],
                credit_total: 0,
                debit_total: 0,
                isWorking: false,
                acct_rec: [],
                acct_pay: [],
                tax_pay: [],
                totalDebit: 0,
                totalCredit: 0
            }
        },

        created() {
            this.fetchData();

            this.$root.$on( "SimpleSelectChange", (data) => {
                this.fin_year = this.years.find(o => o.id === data.selected);
                this.getSelectedOB();
            });
        },

        methods: {
            groupBy(arr, fn) { /* https://30secondsofcode.org/ */
                return arr.map(typeof fn === 'function' ? fn : val => val[fn]).reduce((acc, val, i) => {
                    acc[val] = (acc[val] || []).concat(arr[i]);
                    return acc;
                }, {})
            },

            fetchData() {
                this.chartAccounts = [];
                this.$store.dispatch( 'spinner/setSpinner', true );
                this.getYears();
                this.fetchLedgers();
                this.fetchAgencies();
                this.fetchBanks();
                this.getPeople();
                HTTP.get('/ledgers/accounts').then( response => {
                    this.chartAccounts = response.data;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });
            },

            fetchLedgers() {
                HTTP.get('/ledgers').then( response => {
                    response.data.forEach( (ledger) => {
                        ledger.balance = this.transformBalance( ledger.balance );
                    });
                    this.ledgers = this.groupBy(response.data, 'chart_id');
                    this.all_ledgers = response.data;
                });
            },

            fetchAgencies() {
                HTTP.get('/tax-agencies').then((response) => {
                    this.agencies = response.data;
                }).catch(error => {
                    console.log(error);
                });
            },

            fetchBanks() {
                HTTP.get('/ledgers/7/accounts').then((response) => {
                    this.banks = response.data;
                }).catch(error => {
                    console.log(error);
                });
            },

            transformBalance( val ) {
                if ( null === val && typeof val === 'object' ) {
                    val = 0;
                }
                let currency = '$';
                if ( val < 0 ){
                    return `Cr. ${currency}${Math.abs(val)}`;
                }

                return `Dr. ${currency}${val}`;
            },

            calculateAmount() {
                this.debit_total = 0; this.credit_total = 0;

                for (let key in this.ledgers) {
                    for ( let idx = 0; idx < this.ledgers[key].length; idx++ ) {
                        if ( this.ledgers[key][idx].hasOwnProperty('debit') ) {
                            this.debit_total += parseFloat(this.ledgers[key][idx].debit);
                        }
                        if ( this.ledgers[key][idx].hasOwnProperty('credit') ) {
                            this.credit_total += parseFloat(this.ledgers[key][idx].credit);
                        }
                    }
                }

                for (let key in this.acct_rec) {
                    this.debit_total += parseFloat(this.acct_rec[key].debit);
                }

                for (let key in this.acct_pay) {
                    this.credit_total += parseFloat(this.acct_pay[key].credit);
                }

                for (let key in this.tax_pay) {
                    this.credit_total += parseFloat(this.tax_pay[key].credit);
                }

                let diff = Math.abs( this.debit_total - this.credit_total );

                this.totalDebit = this.debit_total;
                this.totalCredit = this.credit_total;
                this.isWorking = true;
                if( 0 === diff ) {
                    this.isWorking = false;
                }
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.fin_year.hasOwnProperty('id') ) {
                    this.form_errors.push('Financial year is required.');
                }

                if ( this.isWorking ) {
                    this.form_errors.push('Debit and Credit must be Equal.');
                }
            },

            submitOBForm() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.post('/opening-balances', {
                    year: this.fin_year.id,
                    ledgers: this.ledgers,
                    acct_pay: this.acct_pay,
                    acct_rec: this.acct_rec,
                    tax_pay: this.tax_pay,
                    description: this.description,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Opening Balance Created!' );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).then(() => {
                    this.isWorking = false;
                });
            },

            getYears() {
                HTTP.get('/opening-balances/names').then( response => {
                    this.years = response.data;
                });
            },

            getPeople() {
                HTTP.get('/people', {
                    params: {
                        type: 'all'
                    }
                }).then(response => {
                    this.options = response.data;
                });
            },

            getSelectedOB() {
                this.acct_pay = []; this.acct_rec = []; this.tax_pay = [];

                HTTP.get(`/opening-balances/${this.fin_year.id}`).then( response => {
                    this.totalDebit = 0;
                    this.totalCredit = 0;
                    response.data.forEach( (ledger) => {
                        ledger.balance = this.transformBalance( ledger.balance );
                        this.totalDebit += parseFloat( ledger.debit );
                        this.totalCredit += parseFloat( ledger.credit);
                    });
                    this.ledgers = this.groupBy(response.data, 'chart_id');
                }).then(()=>{
                    if ( Object.keys(this.ledgers).length === 0 ) {
                        this.fetchLedgers();
                    }
                });

                HTTP.get(`/opening-balances/virtual-accts/${this.fin_year.id}`).then( response => {
                    this.acct_pay = response.data.acct_payable;
                    this.acct_rec = response.data.acct_receivable;
                    this.tax_pay  = response.data.tax_payable;
                })
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
        },

        watch: {
            isWorking(newval) {
                this.isWorking = newval;
            },
        },
    }
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
</style>
