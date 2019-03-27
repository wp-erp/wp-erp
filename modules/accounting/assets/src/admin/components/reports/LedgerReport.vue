<template>
    <div class="ledger-report">
        <h2>Ledger Report</h2>

        <form action="" method="" @submit.prevent="getLedgerReport" class="query-options">
            <div class="with-multiselect">
                <multi-select v-model="selectedLedger" :options="ledgers" />
            </div>

            <div class="wperp-date-group">
                <datepicker v-model="start_date"></datepicker>
                <datepicker v-model="end_date"></datepicker>
            </div>

            <button class="wperp-btn btn--primary add-line-trigger" type="submit">Filter</button>
        </form>

        <ul class="report-header" v-if="null !== selectedLedger">
            <li><strong>Account No:</strong> <em>{{ selectedLedger.code }}</em></li>
            <li><strong>Account Name:</strong> <em>{{ selectedLedger.name }}</em></li>
            <li><strong>Currency:</strong> <em>Dollar</em></li>
            <li><strong>For the period of ( Transaction date ):</strong> <em>{{ start_date }}</em> to <em>{{ end_date }}</em></li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat ledger-table"
            :columns="columns"
            :rows="rows">
            <template slot="trn_no" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'DynamicTrnLoader', params: { id: data.row.trn_no }}">
                        #{{ data.row.trn_no }}
                    </router-link>
                </strong>
            </template>
            <template slot="balance" slot-scope="data">
                {{ data.row.balance }}
            </template>
            <template slot="debit" slot-scope="data">
                {{ data.row.debit }}
            </template>
            <template slot="credit" slot-scope="data">
                {{ data.row.credit }}
            </template>
            <template slot="tfoot">
                <tr class="tfoot">
                    <td colspan="3"></td>
                    <td>Total =</td>
                    <td>{{ totalDebit }}</td>
                    <td>{{ totalCredit }}</td>
                    <td></td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

<script>
    import HTTP        from 'admin/http'
    import ListTable   from 'admin/components/list-table/ListTable.vue'
    import Datepicker  from 'admin/components/base/Datepicker.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import DynamicTrnLoader from 'admin/components/transactions/DynamicTrnLoader.vue'

    export default {
        name: 'LedgerReport',

        components: {
            ListTable,
            Datepicker,
            MultiSelect,
            DynamicTrnLoader
        },

        data() {
            return {
                start_date    : null,
                end_date      : null,
                selectedLedger: null,
                ledgers       : [],
                openingBalance: 0,
                columns       : {
                    'trn_date'   : { label: 'Trns Date' },
                    'created_at' : { label: 'Created At' },
                    'trn_no'     : { label: 'Trns No' },
                    'particulars': { label: 'Particulars' },
                    'debit'      : { label: 'Debit' },
                    'credit'     : { label: 'Credit' },
                    'balance'    : { label: 'Balance' }
                },
                rows       : [],
                totalDebit : 0,
                totalCredit: 0
            };
        },

        watch: {
            selectedLedger(newVal) {
                if ( ! isNaN( newVal.id ) ) {
                    this.rows = [];
                    this.$router.push({ params: { id: parseInt(newVal.id) } });
                }
            }
        },

        created() {
            if ( this.$route.params.ledgerName ) {
                // Directly coming from chart of acounts
                this.selectedLedger = {
                    id  : parseInt( this.$route.params.ledgerID ),
                    name: this.$route.params.ledgerName,
                    code: this.$route.params.ledgerCode
                };

                this.getLedgerReport();
            }

            this.getLedgers();

            this.start_date = erp_acct_var.current_date;
            this.end_date   = erp_acct_var.current_date;
        },

        methods: {
            getLedgers() {
                HTTP.get('/ledgers').then(res => {
                    this.ledgers = res.data;

                    this.setDefault();
                });
            },

            setDefault() {
                if ( this.$route.params.id && ! this.$route.params.ledgerName ) {
                    // Normally refresh page
                    let ledger = this.ledgers.filter((ledger, index) => {
                        return parseInt( ledger.id ) === parseInt( this.$route.params.id );
                    })[0];

                    this.selectedLedger = {
                        id  : parseInt( ledger.id ),
                        name: ledger.name,
                        code: ledger.code
                    };

                    this.getLedgerReport();
                }
            },

            getLedgerReport() {
                if ( null === this.selectedLedger ) return;

                this.$store.dispatch( 'spinner/setSpinner', true );

                let ledger_id = this.selectedLedger.id;

                this.rows = [];

                HTTP.get('/reports/ledger-report', {
                    params: {
                        ledger_id : this.selectedLedger.id,
                        start_date: this.start_date,
                        end_date  : this.end_date
                    }
                }).then(response => {
                    this.rows        = response.data.details;
                    this.totalDebit  = response.data.extra.total_debit;
                    this.totalCredit = response.data.extra.total_credit;

                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch(e => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });
            }
        }
    }
</script>

<style lang="less">
.ledger-report {

    h2 {
        padding-top: 15px;
    }

    .tablenav,
        .column-cb,
        .check-column {
            display: none;
        }

    .query-options {
        background: #fff;
        padding: 30px 5px;
        border-radius: 3px;
        margin-bottom: 50px;
    }

    .with-multiselect {
        width: 200px;
        float: left;
        margin-right: 50px;
    }

    .wperp-date-group {
        float: left;
        margin-right: 10px;
    }

    .wperp-btn {
        margin-top: 2px;
    }

    .report-header {
        width: 420px;
        padding: 10px 0 0 0;
        margin: 50px 0 0 0;

        li {
            display: flex;
            justify-content: space-between;
        }
    }

    .ledger-table tbody tr td:last-child {
        text-align: left !important;
    }
}
</style>

