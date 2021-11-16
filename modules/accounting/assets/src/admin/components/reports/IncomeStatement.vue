<template>
    <div class="income-statement">
        <h2 class="content-header__title">{{ __('Income Statement', 'erp') }}</h2>

        <form @submit.prevent="fetchItems" class="query-options no-print">

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
                <button class="wperp-btn btn--primary add-line-trigger" type="submit">{{ __('Filter', 'erp') }}</button>
            </div>

            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; {{ __('Print', 'erp') }}
            </a>

        </form>

        <p><strong>{{ __('For the period of ( Transaction date )', 'erp') }}:</strong> <em>{{ start_date }}</em> to <em>{{ end_date }}</em></p>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat income-statement income-balance-report"
            :columns="columns1"
            :rows="rows1"
            :showItemNumbers="false"
            :showCb="false">
            <template slot="amount" slot-scope="data">
                {{ transformBalance(Math.abs(data.row.balance)) }}
            </template>
            <template slot="tfoot">
                <tr class="t-foot">
                    <td>{{ __('Total Income', 'erp') }}</td>
                    <td>{{ transformBalance(Math.abs(income)) }}</td>
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
                {{ transformBalance(Math.abs(data.row.balance)) }}
            </template>
            <template slot="tfoot">
                <tr class="t-foot">
                    <td>{{ __('Total Expense', 'erp') }}</td>
                    <td>{{ transformBalance(Math.abs(expense)) }}</td>
                </tr>
            </template>
        </list-table>

        <table class="wperp-table table-striped table-dark widefat income-statement-balance income-balance-report">
            <template v-if="profit>=0">
                <tbody class="wperp-col-sm-12">
                <tr>
                    <td><strong>{{ __('Profit', 'erp') }}</strong></td>
                    <td>{{ moneyFormat( Math.abs(profit) ) }}</td>
                    <td class="no-print"></td>
                </tr>
                </tbody>
            </template>
            <template v-else>
                <tbody class="wperp-col-sm-12">
                    <tr>
                        <td><strong>{{ __('Loss', 'erp') }}</strong></td>
                        <td>{{ moneyFormat(Math.abs(loss)) }}</td>
                        <td class="no-print"></td>
                    </tr>
                </tbody>
            </template>
        </table>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import Datepicker  from 'admin/components/base/Datepicker.vue';
import ListTable from 'admin/components/list-table/ListTable.vue';

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
                    label: __('Move to Trash', 'erp'),
                    img: erp_acct_var.erp_assets + '/images/trash.png' /* global erp_acct_var */
                }
            ],
            columns1: {
                name  : { label: __('Account Name', 'erp') },
                amount : { label: __('Amount', 'erp') }
            },

            columns2: {
                name  : { label: __('Account Name', 'erp') },
                amount : { label: __('Amount', 'erp') }
            },
            rows1: [],
            rows2: [],
            income: 0,
            expense: 0,
            profit: 0,
            loss: 0
        };
    },

    created() {
        this.$nextTick(function() {
            const dateObj = new Date();

            // with leading zero, and JS month are zero index based
            const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);

            if (this.$route.query.start) {
                this.start_date = this.$route.query.start;
                this.end_date   = this.$route.query.end;
            } else {
                this.start_date = `${dateObj.getFullYear()}-${month}-01`;
                this.end_date   = erp_acct_var.current_date;
            }

           // this.updateDate();

            this.fetchItems();
        });
    },

    methods: {
        updateDate() {
            this.$router.push({ path: this.$route.path,
                query: {
                    start: this.start_date,
                    end  : this.end_date
                } });
        },

        fetchItems() {
            this.updateDate();

            this.rows1 = [];
            this.rows2 = [];
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/reports/income-statement', {
                params: {
                    start_date: this.start_date,
                    end_date  : this.end_date
                }
            }).then(response => {
                this.rows1   = response.data.rows1;
                this.rows2   = response.data.rows2;
                this.income  = response.data.income;
                this.expense = response.data.expense;
                this.profit  = response.data.profit;
                this.loss    = response.data.loss;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        transformBalance(val) {
            if (val === null && typeof val === 'object') {
                val = 0;
            }

            if (val < 0) {
                return `Cr. ${this.moneyFormat(Math.abs(val))}`;
            }

            return `Dr. ${this.moneyFormat(val)}`;
        },

        printPopup() {
            window.print();
        }
    }
};
</script>

<style lang="less">
    .content-header__title {
        padding-top: 5px !important;
    }
    .income-statement tbody tr td:last-child {
        text-align: left !important;
    }

    .income-balance-report {
        tbody tr td:first-child {
            width: 70% !important;
        }

        thead tr th:first-child {
            width: 70% !important;
        }
    }

    .income-statement {
        .tablenav.top,
        .tablenav.bottom {
            display: none;
        }

        .print-btn {
            float: right;
        }

        .query-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 20px 0;
        }

        @media screen {
            @media ( max-width: 782px ) {
                thead th.column.amount {
                    display: none !important;
                }

                tbody {
                    tr + tr {
                        border-top: 1px solid black;
                    }
                }
            }
        }
    }
    .income-statement .t-foot td {
        color: #2196f3;
        font-weight: bold;
    }
    .income-statement-balance tr td {
        background-color: #f2f2f2;
        color: #2196f3;
        font-weight: bold;
    }

    @media print {
        .income-statement {
            p {
                margin-bottom: 20px;

                em {
                    font-weight: bold;
                }
            }
        }

        .erp-nav-container {
            display: none;
        }

        .no-print, .no-print * {
            display: none !important;
        }

        .wperp-table.income-balance-report {
            td,
            th {
                padding: 3px 20px;
            }

            thead tr th {
                font-weight: bold;

                &:not(:first-child) {
                    text-align: right;
                }
            }

            tbody tr td {
                &:not(:first-child) {
                    text-align: right !important;
                }
            }

            tfoot td:not(:first-child) {
                text-align: right !important;
            }
        }
    }
</style>
