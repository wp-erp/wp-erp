<template>
    <div>
        <h4>Balance Sheet</h4>

        <form action="" method="" @submit.prevent="fetchItems" class="query-options">

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">Filter</button>
            </div>

        </form>

        <div class="wperp-panel-body">
            <div class="wperp-row">
                <div class="wperp-col-sm-6">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet"
                        :columns="columns"
                        :rows="rows1"
                        :showCb="false">
                        <template slot="bs_left" slot-scope="data">
                            {{ getCurrencySign() + Math.abs(data.row.balance) }}
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>Total</td>
                                <td>{{ getCurrencySign() + totalLeft }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <div class="wperp-col-sm-6">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet"
                        :columns="columns"
                        :rows="rows2"
                        :showCb="false">
                        <template slot="bs_right" slot-scope="data">
                            {{ getCurrencySign() + Math.abs(data.row.balance) }}
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>Total</td>
                                <td>{{ getCurrencySign() + Math.abs(totalRight) }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>
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
                columns: {
                    'name': { label: 'Account Name' },
                    'balance': { label: 'Balance' }
                },
                rows1: [],
                rows2: [],
                totalDebit: 0,
                totalCredit: 0
            }
        },

        created() {
            this.fetchItems();
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
                    this.rows1 = response.data.rows1;
                    this.rows2 = response.data.rows2;
                    this.totalLeft = response.data.total_left;
                    this.totalRight = response.data.total_right;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });
            }
        }
    }
</script>

<style>
    .wperp-date-group {
        padding-left: 50px !important;
    }
    .balance-sheet tbody tr td:last-child {
        text-align: initial !important;
    }
    .balance-sheet .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
</style>
