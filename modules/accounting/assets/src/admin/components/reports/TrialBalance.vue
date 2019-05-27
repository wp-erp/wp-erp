<template>
    <div class="trial-balance">
        <h2>Trial Balance</h2>

        <form action="" method="" @submit.prevent="getTrialBalance" class="query-options no-print">
            <div class="wperp-date-btn-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
            </div>

            <button class="wperp-btn btn--primary add-line-trigger" type="submit">View</button>

            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i> &nbsp; Print
            </a>
        </form>

        <p><strong>For the period of ( Transaction date ):</strong> <em>{{ start_date }}</em> to <em>{{ end_date }}</em></p>

        <table class="wperp-table table-striped table-dark widefat">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Debit Total</th>
                    <th>Credit Total</th>
                </tr>
            </thead>
            <tbody :key="key" v-for="(chart, key) in chrtAcct">
                <tr v-if="rows[chart.id] && debugMode"><h1>{{ chart.label }}</h1></tr>

                <tr :key="index" v-for="(row, index) in rows[chart.id]">
                    <td>
                        <details v-if="row.additional" open>
                            <summary>{{ row.name }}</summary>
                            <p :key="additional.id" v-for="additional in row.additional">
                                <strong>{{ additional.name }}</strong>
                                <em>{{ getCurrencySign() + Math.abs(additional.balance) }}</em>
                            </p>
                        </details>

                        <span v-else>{{ row.name }}</span>
                    </td>

                    <td>{{ Math.sign(row.balance) === 1 ? getCurrencySign() + row.balance : '' }}</td>
                    <td>{{ Math.sign(row.balance) === -1 ? getCurrencySign() + Math.abs(row.balance) : '' }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="t-foot">
                    <td>Total</td>
                    <td>{{ getCurrencySign() + totalDebit }}</td>
                    <td>{{ getCurrencySign() + Math.abs(totalCredit) }}</td>
                </tr>
            </tfoot>
        </table>

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
                chrtAcct   : null,
                start_date : null,
                end_date   : null
            }
        },

        computed: {
            debugMode() {
                return '1' == erp_acct_var.erp_debug_mode;
            }
        },

        created() {
            //? why is nextTick here ...? i don't know.
            this.$nextTick(function () {
                // with leading zero, and JS month are zero index based
                let month = ('0' + ((new Date).getMonth() + 1)).slice(-2);

                if ( this.$route.query.start ) {
                    this.start_date = this.$route.query.start;
                    this.end_date   = this.$route.query.end;
                } else {
                    this.start_date = `2019-${month}-01`;
                    this.end_date   = erp_acct_var.current_date;
                }
            });

            this.getChartOfAccts();
        },

        methods: {
            updateDate() {
                this.$router.push({ path: this.$route.path, query: {
                    start: this.start_date,
                    end  : this.end_date
                } });
            },

            getChartOfAccts() {
                HTTP.get( '/ledgers/accounts').then(response => {
                    this.chrtAcct = response.data;

                    this.setDateAndGetTb();
                });
            },

            setDateAndGetTb() {
                this.updateDate();
                this.getTrialBalance();
            },

            getTrialBalance() {
                this.updateDate();

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
            },

            printPopup() {
                window.print();
            }
        }
    }
</script>

<style lang="less">
    .trial-balance {
        h2 {
            padding-top: 15px;
        }

        tr {
            h1 {
                padding-left: 10px;
                font-size: 15px;
                font-weight: bold;
            }
        }

        .col--check {
            display: none;
        }

        .tablenav {
            &.top,
            &.bottom {
                display: none
            }
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
            width: 500px;
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
                padding: 3px;
            }
        }
    }


    @media print {
        .erp-nav-container {
            display: none;
        }

        .no-print, .no-print * {
            display: none !important;
        }

        .trial-balance {
            p {
                margin-bottom: 0;
            }

            .wperp-table td,
            .wperp-table th {
                padding: 1px;
            }

            .wperp-table thead tr th {
                font-weight: bold;
            }

            details {
                margin: 0;
                padding: 0;

                summary {
                    margin-bottom: 2px;
                }
            }

            .wperp-table thead tr th {
                font-weight: bold;
                &:not(:first-child) {
                    text-align: right;
                }
            }

            .wperp-table tbody tr td {
                &:not(:first-child) {
                    text-align: right !important;
                }
            }

            .wperp-table tfoot {
                td:first-child {
                    padding-left: 20px;
                }

                td:not(:first-child) {
                    text-align: right !important;
                }
            }
        }
    }
</style>
