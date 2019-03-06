<template>
    <div>
        <h4>Trial Balance</h4>

         <list-table
            tableClass="wperp-table table-striped table-dark widefat trial-balance"
            :columns="columns"
            :rows="rows">
            <template slot="debit" slot-scope="data">
                {{ Math.sign(data.row.balance) === 1 ? getCurrencySign() + data.row.balance : '' }}
            </template>
            <template slot="credit" slot-scope="data">
                {{ Math.sign(data.row.balance) === -1 ? getCurrencySign() + Math.abs(data.row.balance) : '' }}
            </template>
            <template slot="tfoot">
                <tr class="t-foot">
                    <td>Total</td>
                    <td>{{ getCurrencySign() + totalDebit }}</td>
                    <td>{{ getCurrencySign() + Math.abs(totalCredit) }}</td>
                </tr>
            </template>
        </list-table>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'TrialBalance',

        components: {
            ListTable
        },

        data() {
            return {
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'name': { label: 'Account Name' },
                    'debit': { label: 'Debit Total' },
                    'credit': { label: 'Credit Total' }
                },
                rows: [],
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

                HTTP.get( '/reports/trial-balance').then(response => {
                    this.rows = response.data.rows;
                    this.totalDebit = response.data.total_debit;
                    this.totalCredit = response.data.total_credit;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch( error => {
                   this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            }
        }
    }
</script>

<style lang="less">
    .trial-balance {
        .col--check {
            display: none;
        }

        tbody tr td:last-child {
            text-align: initial !important;
        }

        .t-foot {
            td {
                color: #2196f3;
                font-weight: bold;
            }
        }
    }
</style>
