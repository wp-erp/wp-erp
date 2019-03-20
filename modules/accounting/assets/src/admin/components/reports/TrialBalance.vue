<template>
    <div class="trial-balance">
        <h2>Trial Balance</h2>

        <form action="" method="" @submit.prevent="getTrialBalance" class="query-options">
            <div class="wperp-date-btn-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
            </div>

            <button class="wperp-btn btn--primary add-line-trigger" type="submit">View</button>
        </form>

        <p><strong>For the period of ( Transaction date ):</strong> <em>{{ start_date }}</em> to <em>{{ end_date }}</em></p>

        <list-table v-if="rows.length"
            tableClass="wperp-table table-striped table-dark widefat"
            :columns="columns"
            :rows="rows">
            <template slot="name" slot-scope="data">

                <details v-if="data.row.additional" open>
                    <summary>{{ data.row.name }}</summary>
                    <p :key="additional.id" v-for="additional in data.row.additional">
                        <strong>{{ additional.name }}</strong>
                        <em>{{ getCurrencySign() + Math.abs(additional.balance) }}</em>
                    </p>
                </details>
                <span v-else>{{ data.row.name }}</span>

            </template>
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
    import Datepicker  from 'admin/components/base/Datepicker.vue'

    export default {
        name: 'TrialBalance',

        components: {
            ListTable,
            Datepicker,
        },

        data() {
            return {
                bulkActions: [
                    {
                        key  : 'trash',
                        label: 'Move to Trash',
                        img  : erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'name'  : { label: 'Account Name' },
                    'debit' : { label: 'Debit Total' },
                    'credit': { label: 'Credit Total' }
                },
                rows       : [],
                totalDebit : 0,
                totalCredit: 0,
                start_date : null,
                end_date   : null
            }
        },

        created() {
            //? why is nextTick here ...? i don't know.
            this.$nextTick(function () {
                // with leading zero, and JS month are zero index based
                let month = ('0' + ((new Date).getMonth() + 1)).slice(-2);

                this.start_date = `2019-${month}-01`;
                this.end_date   = erp_acct_var.current_date;

                this.getTrialBalance();
            });
        },

        methods: {
            getTrialBalance() {
                this.rows = [];
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.get( '/reports/trial-balance', {
                    params: {
                        start_date: this.start_date,
                        end_date  : this.end_date
                    }
                }).then(response => {
                    this.rows        = response.data.rows;
                    this.totalDebit  = response.data.total_debit;
                    this.totalCredit = response.data.total_credit;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch(e => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });
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

        .query-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 400px;
            padding: 20px 0;
        }

        details {
            summary {
                margin-bottom: 15px;

                &:focus {
                    outline: 0;
                }
            }

            p {
                display: flex;
                justify-content: space-between;
                max-width: 300px;
                box-shadow: 0 1px 5px rgba(0, 0, 0, 0.15);
                padding: 3px;
            }
        }
    }
</style>
