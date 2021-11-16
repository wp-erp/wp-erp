<template>
    <div class="balance-sheet">
        <h2 class="content-header__title">
            <span>{{ __('Balance Sheet', 'erp') }}</span>
        </h2>

        <div class="blnce-sheet-top">
            <form @submit.prevent="fetchItems" class="query-options no-print">

                <div v-if="!closingBtnVisibility" class="wperp-date-group">
                    <datepicker v-model="start_date"></datepicker>
                    <datepicker v-model="end_date"></datepicker>
                    <button class="wperp-btn btn--primary add-line-trigger" type="submit">{{ __('Filter', 'erp') }}</button>
                </div>

                <div v-else class="fn-year-info">
                    <div class="with-multiselect fyear-select">
                        <multi-select v-model="selectedYear" :options="fyears" />
                    </div>

                    <div v-if="selectedYear">
                        {{ __('Balance showing from', 'erp') }} <em>{{ selectedYear.start_date }}</em> {{ __('to', 'erp') }}
                        <em>{{ selectedYear.end_date }}</em>
                    </div>
                </div>

            </form>

            <div class="closing-blnc no-print">
                <div class="close-check">
                    <input type="checkbox" id="prepare-close" v-model="closingBtnVisibility">
                    <label for="prepare-close">{{ __('Prepare for closing', 'erp') }}</label>
                </div>

                <a @click.prevent="checkClosingPossibility"
                    :class="[{ 'visible': closingBtnVisibility }, 'wperp-btn btn--primary close-now-btn']"
                    href="#">{{ __('Close Now', 'erp') }}</a>

                <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                    <i class="flaticon-printer-1"></i>
                    &nbsp; {{ __('Print', 'erp') }}
                </a>
            </div>
        </div>

        <p><strong>{{ __('For the period of ( Transaction date )', 'erp') }}:</strong> <em>{{ start_date }}</em> {{ __('to', 'erp') }} <em>{{ end_date }}</em></p>

        <div class="wperp-panel-body">
            <div>
                <div class="wperp-col-sm-12">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet-asset report-table"
                        :columns="columns1"
                        :rows="rows1"
                        :showItemNumbers="false"
                        :showCb="false">
                        <template slot="name" slot-scope="data">
                            <span v-html="data.row.name"></span>
                            <p class="additional" v-for="additional in data.row.additional">
                                {{additional.name}}   <em>{{ moneyFormat( Math.abs(additional.balance) ) }}</em>
                            </p>
                        </template>
                        <template slot="balance" slot-scope="data">
                            <span v-if="isNaN(data.row.balance)">{{data.row.balance}}</span>
                            <span v-else>{{ transformBalance(data.row.balance) }} </span>
                        </template>


                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>{{ __('Total Asset', 'erp') }}</td>
                                <td>{{ transformBalance(totalAsset) }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <div class="wperp-col-sm-12">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet-liability report-table"
                        :columns="columns2"
                        :rows="rows2"
                        :showItemNumbers="false"
                        :showCb="false">
                        <template slot="name" slot-scope="data">
                            <span v-html="data.row.name"> </span>
                            <p class="additional" v-for="additional in data.row.additional">
                             {{additional.name}}   <em>{{ moneyFormat( Math.abs(additional.balance) ) }}</em>
                            </p>
                        </template>
                        <template slot="balance" slot-scope="data">
                            <span v-if="isNaN(data.row.balance)">{{data.row.balance}}</span>
                            <span v-else>{{ transformBalance(data.row.balance) }} </span>
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>{{ __('Total Liability', 'erp') }}</td>
                                <td>{{ transformBalance(totalLiability) }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <div class="wperp-col-sm-12">
                    <list-table
                        tableClass="wperp-table table-striped table-dark widefat balance-sheet-equity report-table"
                        :columns="columns3"
                        :rows="rows3"
                        :showItemNumbers="false"
                        :showCb="false">
                        <template slot="name" slot-scope="data">
                            <span v-html="data.row.name"> </span>
                        </template>
                        <template slot="balance" slot-scope="data">
                            <span v-if="isNaN(data.row.balance)">{{data.row.balance}}</span>
                            <span v-else>{{ transformBalance(data.row.balance) }} </span>
                        </template>
                        <template slot="tfoot">
                            <tr class="t-foot">
                                <td>{{ __('Total Equity', 'erp') }}</td>
                                <td>{{ transformBalance(totalEquity) }}</td>
                            </tr>
                        </template>
                    </list-table>
                </div>

                <table class="wperp-table table-striped table-dark widefat liability-equity-balance report-table">
                    <tbody>
                        <tr>
                            <td style="font-size: 16px; color: #00b33c;">{{ __('Assets', 'erp') }} = </td>
                            <td style="font-size: 16px; color: #00b33c;">{{ transformBalance(totalAsset) }}</td>
                            <td class="no-print"></td>
                            <td class="no-print"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 16px; color: #ff6666;">{{ __('Liability', 'erp') }} + {{ __('Equity', 'erp') }} = </td>
                            <td style="font-size: 16px; color: #ff6666;">{{ transformBalance(liability_equity) }}</td>
                            <td class="no-print"></td>
                            <td class="no-print"></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import Datepicker  from 'admin/components/base/Datepicker.vue';
import ListTable from 'admin/components/list-table/ListTable.vue';

export default {
    name: 'BalanceSheet',

    components: {
        MultiSelect,
        ListTable,
        Datepicker
    },

    data() {
        return {
            closingBtnVisibility: false,
            start_date          : null,
            end_date            : null,
            bulkActions: [
                {
                    key: 'trash',
                    label: __('Move to Trash', 'erp'),
                    img: erp_acct_var.erp_assets + '/images/trash.png' /* global erp_acct_var */
                }
            ],
            columns1: {
                name: { label: __('Assets', 'erp') },
                balance: { label: __('Amount', 'erp') }
            },
            columns2: {
                name: { label: __('Liability', 'erp') },
                balance: { label: __('Amount', 'erp') }
            },
            columns3: {
                name: { label: __('Equity', 'erp') },
                balance: { label: __('Amount', 'erp') }
            },
            rows1         : [],
            rows2         : [],
            rows3         : [],
            fyears        : [],
            totalAsset    : 0,
            totalLiability: 0,
            totalEquity   : 0,
            selectedYear  : null
        };
    },

    created() {
        // ? why is nextTick here ...? i don't know.
      /*  this.$nextTick(function() {
            const dateObj = new Date();

            // with leading zero, and JS month are zero index based
            const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);

            this.start_date = `${dateObj.getFullYear()}-${month}-01`;
            this.end_date   = erp_acct_var.current_date;

            this.fetchItems();
        });*/

        this.fetchFnYears();
    },

    computed: {
        liability_equity() {
            return parseFloat(this.totalLiability) + parseFloat(this.totalEquity);
        }
    },

    watch: {
        closingBtnVisibility(visible) {
            if (visible) {
                this.start_date = this.selectedYear.start_date;
                this.end_date   = this.selectedYear.end_date;

                this.fetchItems();
            }
        },

        selectedYear(newVal) {
            // only whe `prepare close` is checked
            if (this.closingBtnVisibility) {
                this.start_date = newVal.start_date;
                this.end_date   = newVal.end_date;

                this.fetchItems();
            }
        }
    },

    methods: {
        fetchItems() {
            this.rows = [];
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get('/reports/balance-sheet', {
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

        fetchFnYears() {
            HTTP.get('/opening-balances/names').then(response => {
                // get only last 5
                this.fyears = response.data.reverse().slice(0).slice(-5);
                this.getCurrentFnYear();
            });
        },

        getCurrentFnYear() {
            HTTP.get('/closing-balance/closest-fn-year').then(response => {
                this.selectedYear = response.data;
                this.start_date = response.data.start_date;
                this.end_date   = response.data.end_date;
                this.fetchItems();
            });
        },

        printPopup() {
            window.print();
        },

        checkClosingPossibility() {
            if(!this.end_date){
                this.showAlert('error',  __('Please select financial year', 'erp'));
                return false;
            }

            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/closing-balance/next-fn-year', {
                params: {
                    date : this.end_date
                }
            }).then(response => {

                if (!response.data) {
                    this.showAlert('error', __('Please create a financial year which start after ', 'erp') + this.end_date );
                }else{
                    this.closeBalancesheet(response.data.id);
                }

            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.$store.dispatch('spinner/setSpinner', false);
            });
        },

        closeBalancesheet(f_year_id) {
            HTTP.post('/closing-balance', {
                f_year_id : f_year_id,
                start_date: this.start_date,
                end_date  : this.end_date
            }).then(response => {
                this.showAlert('success', __('Balance Sheet Closed!', 'erp') ) ;
                this.closingBtnVisibility = false;
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(() => {
                this.$store.dispatch('spinner/setSpinner', false);
            });
        }
    }
};
</script>

<style lang="less">
    .content-header__title {
        padding-top: 5px !important;

        a {
            margin-left: 15px;
        }
    }

    .balance-sheet {
        .tablenav.top,
        .tablenav.bottom {
            display: none;
        }
    }

    .blnce-sheet-top,
    .query-options,
    .close-check {
        display: flex;
        justify-content: space-between;
        align-items: center;

        em {
            font-weight: bold;
        }
    }

    .fyear-select {
        min-width: 200px;
        margin-bottom: 15px;
    }

    .closing-blnc {
        display: flex;
    }

    .close-now-btn {
        margin: 0 15px;
        visibility: hidden;

        &.visible {
            visibility: visible;
        }
    }

    .close-check {
        label {
            margin: 0;
        }

        input {
            box-shadow: none;
            width: 20px;
            margin-top: 1px;
            height: 20px;
            border-radius: 3px;

            &[type=checkbox]:checked:before {
                margin: -1px 0 0 -2px;
            }
        }
    }

    .balance-sheet-asset {
        tbody {
            tr {
                td {
                    &:last-child {
                        text-align: left !important;
                    }
                }
            }
        }
        .t-foot {
            td {
                color: #2196f3;
                font-weight: bold;

                &:last-child {
                    font-size: 16px;
                }
            }
        }
    }
    .report-table {
        &.balance-sheet-asset,
        &.balance-sheet-liability,
        &.balance-sheet-equity {
            @media screen and ( max-width: 782px ) {
                thead {
                    th {
                        &.column.balance {
                            display: none;
                        }
                    }
                }
            }
        }

        tbody {
            tr {
                td {
                    &:first-child {
                        width: 70% !important;
                    }
                }
            }
        }
    }
    .balance-sheet-liability {
        tbody {
            tr {
                td {
                    &:last-child {
                        text-align: initial !important;
                    }
                }
            }
        }
        .t-foot {
            td {
                color: #2196f3;
                font-weight: bold;
            }
        }
    }
    .balance-sheet-equity {
        tbody {
            tr {
                td {
                    &:last-child {
                        text-align: initial !important;
                    }
                }
            }
        }
        .t-foot {
            td {
                color: #2196f3;
                font-weight: bold;
            }
        }
    }
    .liability-equity-balance {
        tr {
            td {
                background-color: #f2f2f2;
                color: #2196f3;
                font-weight: bold;
            }

            &:last-child td:nth-child(2) {
                font-size: 16px;
            }
        }
    }

    .additional{
        max-width: 300px;
        padding-left: 30px;
        em{
           float: right;
            display: inline-block;
        }
    }

    @media print {
        .erp-nav-container {
            display: none;
        }

        .no-print {
            display: none !important;
            * {
                display: none !important;
            }
        }

        .balance-sheet {
            .wperp-row {
                .wperp-col-sm-12 {
                    width: 100%;
                }
            }

            p {
                margin-bottom: 20px;

                em {
                    font-weight: bold;
                }
            }

            .wperp-table {
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
    }
</style>
