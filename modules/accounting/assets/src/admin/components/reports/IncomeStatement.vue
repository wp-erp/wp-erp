<template>
    <div>
        <h2 class="content-header__title">Income Statement</h2>

        <form action="" @submit.prevent="fetchItems" class="query-options no-print">

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">Filter</button>
            </div>

            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; Print
            </a>

        </form>

         <list-table
            tableClass="wperp-table table-striped table-dark widefat income-statement income-balance-report"
            :columns="columns1"
            :rows="rows1"
            :showItemNumbers="false"
            :showCb="false">
            <template slot="amount" slot-scope="data">
                {{ getCurrencySign() + Math.abs(data.row.credit) }}
            </template>
            <template slot="tfoot">
                <tr class="t-foot">
                    <td>Total Income</td>
                    <td>{{ getCurrencySign() + income }}</td>
                </tr>
            </template>
        </list-table>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat income-statement income-balance-report"
            :columns="columns2"
            :rows="rows2"
            :showItemNumbers="false"
            :showCb="false">
            <template slot="amount" slot-scope="data">
                {{ getCurrencySign() + Math.abs(data.row.debit) }}
            </template>
            <template slot="tfoot">
                <tr class="t-foot">
                    <td>Total Expense</td>
                    <td>{{ getCurrencySign() + expense }}</td>
                </tr>
            </template>
        </list-table>

        <table class="wperp-table table-striped table-dark widefat income-statement-balance income-balance-report">
            <template v-if="balance >= 0">
                <tbody class="wperp-col-sm-12">
                <tr>
                    <td><strong>Profit</strong></td>
                    <td>{{ getCurrencySign() + Math.abs(balance) }}</td>
                    <td></td>
                </tr>
                </tbody>
            </template>
            <template v-else>
                <tbody class="wperp-col-sm-12">
                    <tr>
                        <td><strong>Loss</strong></td>
                        <td>{{ getCurrencySign() + Math.abs(balance) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </template>
        </table>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker  from 'admin/components/base/Datepicker.vue'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'IncomeStatement',

        components: {
            ListTable,
            Datepicker
        },

        data() {
            return {
                start_date    : null,
                end_date      : null,
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns1: {
                    'name'  : { label: 'Account Name' },
                    'amount' : { label: 'Amount' }
                },

                columns2: {
                    'name'  : { label: 'Account Name' },
                    'amount' : { label: 'Amount' }
                },
                rows1: [],
                rows2: [],
                income: 0,
                expense: 0,
                balance: 0
            }
        },

        created() {
            this.fetchItems();
        },

        methods: {
            fetchItems() {
                this.rows1 = [];
                this.rows2 = [];
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get( '/reports/income-statement',{
                    params: {
                        start_date: this.start_date,
                        end_date  : this.end_date
                    }
                }).then(response => {
                    this.rows1   = response.data.rows1;
                    this.rows2   = response.data.rows2;
                    this.income  = response.data.total_credit;
                    this.expense = response.data.total_debit;
                    this.balance = this.income - this.expense;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            },

            printPopup() {
                window.print();
            }
        }
    }
</script>

<style>
    .content-header__title {
        padding-top: 5px !important;
    }
    .income-statement tbody tr td:last-child {
        text-align: left !important;
    }
    .income-balance-report tbody tr td:first-child {
        width: 70% !important;
    }
    .income-balance-report thead tr th:first-child {
        width: 70% !important;
    }
    .income-statement .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
    .query-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 500px;
        padding: 20px 0;
    }
    .income-statement-balance tr td {
        background-color: #f2f2f2;
        color: #2196f3;
        font-weight: bold;
    }
    @media print {
        .erp-nav-container {
            display: none;
        }
        .no-print, .no-print * {
            display: none !important;
        }
    }
</style>
