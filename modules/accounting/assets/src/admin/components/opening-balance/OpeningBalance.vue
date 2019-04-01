<template>
    <div class="wperp-container accordion-container">
        <div v-if="chartAccounts[0]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open1=!open1"
                 :class="open1?'active':'beforeBorder'">
                <span class="wp-erp-ob-title">{{chartAccounts[0].label}}</span>
            </div>
            <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open1">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Balance</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="idx" v-if="ledgers[1]" v-for="(ledger,idx) in ledgers[1]">
                    <td>{{ledger.name}}</td>
                    <td>{{ledger.balance}}</td>
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-if="chartAccounts[1]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open2=!open2"
                 :class="open2?'active':'beforeBorder'">
                <span class="wp-erp-ob-title">{{chartAccounts[1].label}}</span>
            </div>
            <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open2">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Balance</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="idx" v-if="ledgers[2]" v-for="(ledger,idx) in ledgers[2]">
                    <td>{{ledger.name}}</td>
                    <td>{{ledger.balance}}</td>
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>

        </div>
        <div v-if="chartAccounts[2]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open3=!open3"
                 :class="open3?'active':'beforeBorder'">
                <span class="wp-erp-ob-title">{{chartAccounts[2].label}}</span>
            </div>
            <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open3">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Balance</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="idx" v-if="ledgers[3]" v-for="(ledger,idx) in ledgers[3]">
                    <td>{{ledger.name}}</td>
                    <td>{{ledger.balance}}</td>
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-if="chartAccounts[3]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open4=!open4"
                 :class="open4?'active':'beforeBorder'">
                <span class="wp-erp-ob-title">{{chartAccounts[3].label}}</span>
            </div>
            <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open4">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Balance</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="idx" v-if="ledgers[4]" v-for="(ledger,idx) in ledgers[4]">
                    <td>{{ledger.name}}</td>
                    <td>{{ledger.balance}}</td>
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-if="chartAccounts[4]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open5=!open5"
                 :class="open5?'active':'beforeBorder'">
                <span class="wp-erp-ob-title">{{chartAccounts[4].label}}</span>
            </div>
            <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open5">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Balance</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="idx" v-if="ledgers[5]" v-for="(ledger,idx) in ledgers[2]">
                    <td>{{ledger.name}}</td>
                    <td>{{ledger.balance}}</td>
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-if="chartAccounts[5]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open6=!open6"
                 :class="open6?'active':'beforeBorder'">
                <span class="wp-erp-ob-title">{{chartAccounts[5].label}}</span>
            </div>
            <table class="wperp-table wperp-form-table erp-accordion-expand-body" v-show="open6">
                <thead>
                <tr>
                    <th>Account</th>
                    <th>Balance</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="idx" v-if="ledgers[6]" v-for="(ledger,idx) in ledgers[6]">
                    <td>{{ledger.name}}</td>
                    <td>{{ledger.balance}}</td>
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-if="chartAccounts[6]" class="erp-accordion">
            <div class="erp-accordion-expand"
                 @click="open7=!open7"
                 :class="open7?'active':'beforeBorder'">
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
                    <td><input type="number" v-model="ledger.debit"></td>
                    <td><input type="number" v-model="ledger.credit"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'

    export default {
        name: "OpeningBalance",

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
                chartAccounts: [],
                ledgers: [],
            }
        },

        mounted() {
            this.fetchChartAccounts();
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
        }
    }
</script>

<style scoped>
    .accordion-container {
        padding-top: 10px;
    }
    .wp-erp-ob-title{
        font-weight: bolder;
        color: #3f9ed4;
        font-size: larger;
    }
    .beforeBorder {
        position: relative;
    }
    .beforeBorder:before {
        transition: opacity 0.1s linear, transform 0.5s ease-in-out;
        position: absolute;
        content: '';
        width: 100%;
        left: 0;
        bottom: -1px;
    }
    .erp-accordion {
        background: #fff;
        box-shadow: 0 1px 12px 1PX rgba(0,0,0,0.25);
        overflow: hidden;
    }
    .erp-accordion-expand {
        cursor: pointer;
        padding: .5rem .75rem;
        border-bottom: 1px solid #efefef;
    }
    .erp-accordion-expand:hover {
        color: #477dca;
    }
    .erp-accordion-expand.active {
        border-bottom-color: #477dca;
    }
    .erp-accordion-expand-icon.open* {
        transform: rotate(180deg);
    }
    .erp-accordion-expand-body {
        padding: 1rem 1.5rem;
        background: #eff0f2;
    }
</style>
