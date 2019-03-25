<template>
    <div>
        <h2 class="content-header__title">Balance Sheet</h2>

        <form action="" method="" @submit.prevent="fetchItems" class="query-options">

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">Filter</button>
            </div>

        </form>

        <div class="wperp-panel-body">
            <div class="wperp-row">
                <div class="wperp-col-sm-12">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet-asset"
                        :columns="columns1"
                        :rows="rows1"
                        :showItemNumbers="false"
                        :showCb="false">
                        <template slot="name" slot-scope="data">
                            <span v-html="data.row.name"> </span>
                        </template>
                        <template slot="balance" slot-scope="data">
                            <span v-if="isNaN(data.row.balance)">{{data.row.balance}}</span>
                            <span v-else>{{ getCurrencySign() + Math.abs(data.row.balance) }} </span>
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>Total Asset</td>
                                <td>{{ getCurrencySign() + totalAsset }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <div class="wperp-col-sm-12">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet-liability"
                        :columns="columns2"
                        :rows="rows2"
                        :showItemNumbers="false"
                        :showCb="false">
                        <template slot="name" slot-scope="data">
                            <span v-html="data.row.name"> </span>
                        </template>
                        <template slot="balance" slot-scope="data">
                            <span v-if="isNaN(data.row.balance)">{{data.row.balance}}</span>
                            <span v-else>{{ getCurrencySign() + Math.abs(data.row.balance) }} </span>
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>Total Liability</td>
                                <td>{{ getCurrencySign() + Math.abs(totalLiability) }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <div class="wperp-col-sm-12">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet-equity"
                        :columns="columns3"
                        :rows="rows3"
                        :showItemNumbers="false"
                        :showCb="false">
                        <template slot="name" slot-scope="data">
                            <span v-html="data.row.name"> </span>
                        </template>
                        <template slot="balance" slot-scope="data">
                            <span v-if="isNaN(data.row.balance)">{{data.row.balance}}</span>
                            <span v-else>{{ getCurrencySign() + Math.abs(data.row.balance) }} </span>
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>Total Equity</td>
                                <td>{{ getCurrencySign() + Math.abs(totalEquity) }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <table class="wperp-table table-striped table-dark widefat liability-equity-balance">
                    <tbody>
                        <tr>
                            <td><strong>Assets = </strong></td>
                            <td>{{ getCurrencySign() + totalAsset }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><strong>Liability + Equity = </strong></td>
                            <td>{{ getCurrencySign() + liability_equity }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Datepicker  from 'admin/components/base/Datepicker.vue'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'BalanceSheet',

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
                    'name': { label: 'Assets' },
                    'balance': { label: 'Amount' }
                },
                columns2: {
                    'name': { label: 'Liability' },
                    'balance': { label: 'Amount' }
                },
                columns3: {
                    'name': { label: 'Equity' },
                    'balance': { label: 'Amount' }
                },
                rows1: [],
                rows2: [],
                rows3: [],
                totalAsset: 0,
                totalLiability: 0,
                totalEquity: 0
            }
        },

        created() {
            this.fetchItems();
        },

        computed: {
            liability_equity() {
                return parseFloat( Math.abs(this.totalLiability) ) + parseFloat(Math.abs(this.totalEquity));
            }
        },

        methods: {
            fetchItems() {
                this.rows = [];
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.get( '/reports/balance-sheet',{
                    params: {
                        start_date: this.start_date,
                        end_date  : this.end_date
                    }
                }).then(response => {
                    this.rows1          = response.data.rows1;
                    this.rows2          = response.data.rows2;
                    this.rows3          = response.data.rows3;
                    this.totalAsset     = response.data.total_asset;
                    this.totalLiability = response.data.total_liability;
                    this.totalEquity    = response.data.total_equity;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            }
        }
    }
</script>

<style scoped>
    .content-header__title {
        padding-top: 5px !important;
    }

    .balance-sheet-asset tbody tr td:last-child {
        text-align: initial !important;
    }
    .balance-sheet-asset .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
    .balance-sheet-liability tbody tr td:last-child {
        text-align: initial !important;
    }
    .balance-sheet-liability .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
    .balance-sheet-equity tbody tr td:last-child {
        text-align: initial !important;
    }
    .balance-sheet-equity .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
    .liability-equity-balance tr td {
        background-color: #f2f2f2;
        color: #2196f3;
        font-weight: bold;
    }
</style>
