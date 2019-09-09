<template>
    <div class="sales-tax-report">
        <h2>{{ __('Sales Tax Report', 'erp') }}</h2>

        <form action="" method="" @submit.prevent="getSalesTaxReport" class="query-options no-print">
            <div class="with-multiselect">
                <multi-select v-model="selectedAgency" :options="taxAgencies" />
            </div>

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
            </div>

            <button class="wperp-btn btn--primary add-line-trigger" type="submit">{{ __('View', 'erp') }}</button>

            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; {{ __('Print', 'erp') }}
            </a>
        </form>

        <ul class="report-header" v-if="null !== selectedAgency">
            <li><strong>{{ __('Account Name', 'erp') }}:</strong> <em>{{ selectedAgency.name }}</em></li>
            <li><strong>{{ __('Currency', 'erp') }}:</strong> <em>{{ symbol }}</em></li>
            <li><strong>{{ __('For the period of ( Transaction date )', 'erp') }}:</strong> <em>{{ start_date }}</em> to <em>{{ end_date }}</em></li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="rows"
            :showCb="false">
            <template slot="trn_no" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'DynamicTrnLoader', params: { id: data.row.trn_no }}">
                        <span v-if="data.row.trn_no">#{{ data.row.trn_no }}</span>
                    </router-link>
                </strong>
            </template>
            <template slot="debit" slot-scope="data">
                {{ moneyFormat(data.row.debit) }}
            </template>
            <template slot="credit" slot-scope="data">
                {{ moneyFormat(data.row.credit) }}
            </template>
            <template slot="balance" slot-scope="data">
                {{ moneyFormat(data.row.balance) }}
            </template>
            <template slot="tfoot">
                <tr class="tfoot">
                    <td colspan="3"></td>
                    <td>{{ __('Total', 'erp') }} =</td>
                    <td>{{ moneyFormat(totalDebit) }}</td>
                    <td>{{ moneyFormat(totalCredit) }}</td>
                    <td></td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

<script>
import HTTP        from 'admin/http';
import ListTable   from 'admin/components/list-table/ListTable.vue';
import Datepicker  from 'admin/components/base/Datepicker.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'SalesTax',

    components: {
        ListTable,
        Datepicker,
        MultiSelect
    },

    data() {
        return {
            start_date    : null,
            end_date      : null,
            selectedAgency: null,
            taxAgencies   : [],
            openingBalance: 0,
            rows          : [],
            totalDebit    : 0,
            totalCredit   : 0,
            columns       : {
                trn_date   : { label: 'Trns Date' },
                created_at : { label: 'Created At' },
                trn_no     : { label: 'Trns No' },
                particulars: { label: 'Particulars' },
                debit      : { label: 'Debit' },
                credit     : { label: 'Credit' },
                balance    : { label: 'Balance' }
            },
            symbol: erp_acct_var.symbol
        };
    },

    watch: {
        selectedAgency() {
            this.rows = [];
        }
    },

    created() {
        // ? why is nextTick here ...? i don't know.
        this.$nextTick(function() {
            const dateObj = new Date();

            // with leading zero, and JS month are zero index based
            const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);

            this.start_date = `${dateObj.getFullYear()}-${month}-01`;
            this.end_date   = erp_acct_var.current_date; /* global erp_acct_var */
        });

        this.getAgencies();
    },

    methods: {
        getAgencies() {
            HTTP.get('/tax-agencies').then(res => {
                this.taxAgencies = res.data;
            });
        },

        getSalesTaxReport() {
            if (this.selectedAgency === null) return;

            this.$store.dispatch('spinner/setSpinner', true);

            this.rows = [];

            HTTP.get('/reports/sales-tax-report', {
                params: {
                    agency_id : this.selectedAgency.id,
                    start_date: this.start_date,
                    end_date  : this.end_date
                }
            }).then(response => {
                this.rows        = response.data.details;
                this.totalDebit  = response.data.extra.total_debit;
                this.totalCredit = response.data.extra.total_credit;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(e => {
                this.$store.dispatch('spinner/setSpinner', false);
            });
        },

        printPopup() {
            window.print();
        }
    }
};
</script>

<style lang="less">
    .sales-tax-report {
        h2 {
            padding-top: 15px;
        }

        .query-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            width: 900px;
        }

        .with-multiselect {
            width: 300px;
            float: left;
            margin-right: 50px;
        }

        .wperp-btn {
            margin-top: 2px;
        }

        .report-header {
            width: 420px;
            padding: 10px 0 0 0;

            li {
                display: flex;
                justify-content: space-between;
            }
        }

        .sales-tax-table tbody tr td:last-child {
            text-align: left !important;
        }
    }

    @media print {
        .erp-nav-container {
            display: none;
        }

        .no-print, .no-print * {
            display: none !important;
        }

        .sales-tax-report {
            .wperp-table.sales-tax-table {
                th.trn_date,
                th.created_at {
                    min-width: 120px;
                }

                th.trn_no {
                    min-width: 100px;
                }

                td,
                th {
                    padding: 3px !important;
                }

                tr th:first-child,
                tr td:last-child {
                    padding-left: 5px;
                }

                tr th:last-child,
                tr td:last-child {
                    padding-right: 5px;
                }

                thead tr th {
                    font-weight: bold;

                    &:nth-child(5),
                    &:nth-child(6),
                    &:nth-child(7) {
                        text-align: right;
                    }
                }

                tbody tr td {
                    &:nth-child(5),
                    &:nth-child(6),
                    &:nth-child(7) {
                        text-align: right !important;
                    }
                }

                tfoot td {
                    &:nth-child(3),
                    &:nth-child(4) {
                        text-align: right !important;
                    }
                }
            }
        }
    }
</style>
