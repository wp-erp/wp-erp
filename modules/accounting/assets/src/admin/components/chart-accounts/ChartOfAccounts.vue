<template>
    <div class="wperp-container chart-accounts">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col-6">
                    <h2 class="content-header__title">
                        {{ __('Chart of Accounts', 'erp') }}
                        <router-link class="wperp-btn btn--primary" :to="{ name: 'AddChartAccounts'}">
                            {{ __('Add New', 'erp') }}
                        </router-link>
                    </h2>
                </div>
                <div class="wperp-col-6">
                    <h4>{{ __( 'Search Ledger', 'erp' ) }}</h4>
                    <input type="text" class="wperp-form-field" v-model="search"/>
                </div>
            </div>
        </div>

        <ul v-if="search">
            <list-table
                tableClass="wperp-table table-striped table-dark widefat table2 chart-list"
                action-column="actions"
                :columns="columns"
                :actions="actions"
                :showCb="false"
                :rows="filteredLedgers"
                @action:click="onActionClick">
                <template slot="ledger_name" slot-scope="data">
                    <router-link :to="{ name: 'LedgerSingle', params: {
                            id        : data.row.id,
                            ledgerID  : data.row.id,
                            ledgerName: data.row.name,
                            ledgerCode: data.row.code
                            }}">{{ data.row.name }}
                    </router-link>
                </template>
                <template slot="trn_count" slot-scope="data">
                    <router-link :to="{ name: 'LedgerReport', params: {
                            id        : data.row.id,
                            ledgerID  : data.row.id,
                            ledgerName: data.row.name,
                            ledgerCode: data.row.code
                            }}">{{ data.row.trn_count }}
                    </router-link>
                </template>
                <template slot="row-actions" slot-scope="data" v-if="data.row.system != null">
                    <strong class="sys-acc">{{ __('System', 'erp') }}</strong>
                </template>
            </list-table>
        </ul>
        <ul v-else>
            <li :key="index" v-for="(chart, index) in chartAccounts">
                <h3>{{ chart.label }}</h3>

                <list-table
                    tableClass="wperp-table table-striped table-dark widefat table2 chart-list"
                    action-column="actions"
                    :columns="columns"
                    :actions="actions"
                    :showCb="false"
                    :rows="ledgers[parseInt(chart.id)]"
                    @action:click="onActionClick">
                    <template slot="ledger_name" slot-scope="data">
                        <router-link :to="{ name: 'LedgerSingle', params: {
                            id        : data.row.id,
                            ledgerID  : data.row.id,
                            ledgerName: data.row.name,
                            ledgerCode: data.row.code
                            }}">{{ data.row.name }}
                        </router-link>
                    </template>
                    <template slot="trn_count" slot-scope="data">
                        <router-link :to="{ name: 'LedgerReport', params: {
                            id        : data.row.id,
                            ledgerID  : data.row.id,
                            ledgerName: data.row.name,
                            ledgerCode: data.row.code
                            }}">{{ data.row.trn_count }}
                        </router-link>
                    </template>
                    <template slot="row-actions" slot-scope="data" v-if="data.row.system != null">
                        <strong class="sys-acc">{{ __('System', 'erp') }}</strong>
                    </template>
                </list-table>
            </li>
        </ul>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';

export default {
    name: 'ChartAccounts',

    data() {
        return {
            columns: {
                code       : { label: 'Code' },
                ledger_name: { label: 'Name' },
                balance    : { label: 'Balance' },
                trn_count  : { label: 'Count' },
                actions    : { label: 'Actions' }
            },
            actions : [
                { key: 'edit', label: 'Edit' }
                // { key: 'trash', label: 'Delete' }
            ],

            chartAccounts: [],
            ledgers: [],
            temp_ledgers: erp_acct_var.ledgers, /* global erp_acct_var */
            search : '',
            curSymbol: erp_acct_var.symbol || '$'
        };
    },

    computed: {
        filteredLedgers() {
            var self = this;
            return this.temp_ledgers.filter(function(ledger) {
                return ledger.name.toLowerCase().indexOf(self.search.toLowerCase()) >= 0;
            });
        }
    },

    components: {
        ListTable
    },

    created() {
        this.fetchChartAccounts();
        this.fetchLedgers();
    },

    methods: {
        groupBy(arr, fn) { /* https://30secondsofcode.org/ */
            return arr.map(typeof fn === 'function' ? fn : val => val[fn]).reduce((acc, val, i) => {
                acc[val] = (acc[val] || []).concat(arr[i]);
                return acc;
            }, {});
        },

        fetchChartAccounts() {
            this.chartAccounts = [];
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/ledgers/accounts').then(response => {
                this.chartAccounts = response.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        fetchLedgers() {
            this.temp_ledgers.forEach((ledger) => {
                ledger.balance = this.transformBalance(ledger.balance);
            });
            this.ledgers = this.groupBy(this.temp_ledgers, 'chart_id');
        },

        transformBalance(val) {
            if (val === null && typeof val === 'object') {
                val = 0;
            }

            if (typeof val === 'string') {
                val = val.split(this.curSymbol)[1];
            }

            if (val < 0) {
                return `Cr. ${this.moneyFormat(Math.abs(val))}`;
            }

            return `Dr. ${this.moneyFormat(val)}`;
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm('Are you sure to delete?')) {
                    this.$store.dispatch('spinner/setSpinner', true);

                    HTTP.delete(`/ledgers/${row.id}`).then(response => {
                        this.fetchChartAccounts();

                        this.$store.dispatch('spinner/setSpinner', false);
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
                break;

            case 'edit':
                this.$router.push({ name: 'ChartAccountsEdit', params: { id: row.id } });
                break;

            default :
            }
        }

    }
};
</script>

<style lang="less">
    .chart-accounts {
        .tablenav,
        .tablenav,
        .column-cb,
        .check-column {
            display: none !important;
        }

        li {
            margin-bottom: 20px;
        }

        .chart-list {
            .sys-acc {
                color: #ff6f00;
            }

            thead,
            tfoot {
                width: 25%;

                th:last-child {
                    text-align: right;
                }
            }
        }

        th.column.actions {
            float: right;
        }
    }

    .chart-list tr .ledger_name {
        width: 40%;
    }
</style>
