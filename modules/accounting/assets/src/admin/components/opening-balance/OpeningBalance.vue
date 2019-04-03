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
            <div class="wperp-custom-select">
                <label>Financial Year</label>
                <select v-model="fin_year">
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </select>
            </div>
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
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[1]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
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
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[2]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>

            </div>
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
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[3]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="chartAccounts[3]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open4=!open4"
                     :class="open4?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[3].label}}</span>
                </div>
                <table v-if="ledgers[4]" class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open4">
                    <thead>
                    <tr>
                        <th>Account</th>
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[4]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="chartAccounts[4]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open5=!open5"
                     :class="open5?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[4].label}}</span>
                </div>
                <table v-if="ledgers[5]" class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open5">
                    <thead>
                    <tr>
                        <th>Account</th>
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[2]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="chartAccounts[5]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open6=!open6"
                     :class="open6?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[5].label}}</span>
                </div>
                <table v-if="ledgers[6]" class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open6">
                    <thead>
                    <tr>
                        <th>Account</th>
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-for="(ledger,idx) in ledgers[6]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="chartAccounts[6]" class="erp-accordion">
                <div class="erp-accordion-expand"
                     @click="open7=!open7"
                     :class="open7?'active':'before-border'">
                    <span class="wp-erp-ob-title">{{chartAccounts[6].label}}</span>
                </div>
                <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open7">
                    <thead>
                    <tr>
                        <th>Account</th>
                        <th>Balance</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr :key="idx" v-if="ledgers[7]" v-for="(ledger,idx) in ledgers[7]">
                        <td>{{ledger.name}}</td>
                        <td>{{ledger.balance}}</td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.debit"></td>
                        <td><input type="number" @keyup="calculateAmount" v-model="ledger.credit"></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- do your comment -->
            <table class="wperp-table wperp-form-table">
                <tbody>
                <tr class="total-amount-row">
                    <td class="pl-10 text-right col--total-amount" style="width: 60%;">
                        <span>Total Amount</span>
                    </td>
                    <td data-colname="Total Debit"><input type="text" class="text-right" :value="debit_total" readonly ></td>
                    <td data-colname="Total Credit"><input type="text" class="text-right" :value="credit_total" readonly ></td>
                </tr>
                </tbody>
            </table>
            <submit-button text="Add Opening Balance"></submit-button>

        </form>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: "OpeningBalance",

        components: {
            HTTP,
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
                open1: false,
                open2: false,
                open3: false,
                open4: false,
                open5: false,
                open6: false,
                open7: false,
                form_errors: [],
                chartAccounts: [],
                ledgers: [],
                fin_year: '',
                years: [],
                all_ledgers: [],
                credit_total: 0,
                debit_total: 0,
                isWorking: false,
            }
        },

        mounted() {
            this.fetchChartAccounts();
            this.getYears();
        },

        methods: {
            groupBy(arr, fn) { /* https://30secondsofcode.org/ */
                return arr.map(typeof fn === 'function' ? fn : val => val[fn]).reduce((acc, val, i) => {
                    acc[val] = (acc[val] || []).concat(arr[i]);
                    return acc;
                }, {})
            },

            fetchChartAccounts() {
                this.chartAccounts = [];
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get('/ledgers/accounts').then( response => {
                    this.chartAccounts = response.data;

                    this.fetchLedgers();
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
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

            transformBalance( val ){
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

                let diff = Math.abs( this.debit_total - this.credit_total );
                this.isWorking = true;
                if( 0 == diff ) {
                    this.isWorking = false;
                }
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.fin_year ) {
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
                    year: this.fin_year,
                    ledgers: this.ledgers,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Opening Balance Created!' );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
                    this.isWorking = false;
                });
            },


            getYears(startYear) {
                let currentYear = new Date().getFullYear();
                startYear = startYear || 1980;
                while ( startYear <= currentYear ) {
                    this.years.push(startYear++);
                }

                return this.years;
            }
        },

        watch: {
            isWorking(newval) {
                this.isWorking = newval;
            },
        },
    }
</script>

<style>
    .accordion-container .erp-accordion table {
        width: calc(100% - 40px);
    }
</style>

<style scoped lang="less">
    .accordion-container {
        padding-top: 10px;
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
