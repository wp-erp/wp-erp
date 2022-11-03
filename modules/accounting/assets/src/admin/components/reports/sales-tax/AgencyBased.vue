<template>
    <div class="sales-tax-report">
        <h2 class="title-container">
            <span>{{ __( 'Sales Tax Report (Agency Based)', 'erp' ) }}</span>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'SalesTaxReportOverview' }">
                {{ __( 'Back', 'erp' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
                <div class="with-multiselect">
                    <multi-select v-model="selectedAgency" :options="taxAgencies" @input="getReport" />
                </div>

                <datepicker v-model="startDate" />

                <datepicker v-model="endDate" />

                <button class="wperp-btn btn--primary add-line-trigger" type="submit">
                    {{ __( 'Filter', 'erp' ) }}
                </button>
            </div>


            <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                <i class="flaticon-printer-1"></i>
                &nbsp; {{ __( 'Print', 'erp' ) }}
            </a>
        </form>

        <ul class="report-header" v-if="null !== selectedAgency">
            <li>
                <strong>{{ __( 'Agency Name', 'erp' ) }}:</strong>
                <em> {{ selectedAgency.name }}</em>
            </li>

            <li>
                <strong>{{ __( 'Currency', 'erp' ) }}:</strong>
                <em> {{ symbol }}</em>
            </li>

            <li v-if="startDate && endDate">
                <strong>{{ __( 'For the period of (Transaction date)', 'erp' ) }}:</strong>
                <em> {{ formatDate( startDate ) }}</em> to <em>{{ formatDate( endDate ) }}</em>
            </li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="rows"
            :showCb="false">

            <template slot="trn_no" slot-scope="data">
                <strong>
                    <router-link
                        :to="{
                            name   : 'DynamicTrnLoader',
                            params : {
                                id : data.row.trn_no
                            }
                        }">
                        <span v-if="data.row.trn_no">#{{ data.row.trn_no }}</span>
                    </router-link>
                </strong>
            </template>

            <template slot="debit" slot-scope="data">
                {{ moneyFormat( data.row.debit ) }}
            </template>

            <template slot="credit" slot-scope="data">
                {{ moneyFormat( data.row.credit ) }}
            </template>

            <template slot="balance" slot-scope="data">
                {{ moneyFormat( data.row.balance ) }}
            </template>

            <template slot="tfoot">
                <tr class="tfoot">
                    <td colspan="3"></td>
                    <td data-left-align>{{ __( 'Total', 'erp' ) }} =</td>
                    <td data-colname="Debit">{{ moneyFormat( totalDebit ) }}</td>
                    <td data-colname="Credit">{{ moneyFormat( totalCredit ) }}</td>
                    <td></td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

<script>
    import HTTP        from 'admin/http';
    import ListTable   from '../../list-table/ListTable.vue';
    import Datepicker  from '../../base/Datepicker.vue';
    import MultiSelect from '../../select/MultiSelect.vue';

    export default {
        name : 'SalesTaxReportCategoryBased',

        components : {
            ListTable,
            Datepicker,
            MultiSelect
        },

        data() {
            return {
                startDate       : null,
                endDate         : null,
                selectedAgency  : null,
                taxAgencies     : [],
                openingBalance  : 0,
                rows            : [],
                totalDebit      : 0,
                totalCredit     : 0,
                symbol          : erp_acct_var.symbol,
                columns         : {
                    trn_no      : {
                        label       : __( 'Voucher No', 'erp' ),
                        isColPrimary: true
                    },
                    trn_date    : {
                        label   : __( 'Transaction Date', 'erp' )
                    },
                    particulars : {
                        label   : __( 'Particulars', 'erp' )
                    },
                    debit       : {
                        label   : __( 'Debit', 'erp' )
                    },
                    credit      : {
                        label   : __( 'Credit', 'erp' )
                    },
                    balance     : {
                        label   : __( 'Balance', 'erp' )
                    }
                },
            };
        },

        watch: {
            selectedAgency() {
                this.rows = [];
            }
        },

        created() {
            this.$nextTick(() => {
                const dateObj  = new Date();
                const month    = ('0' + (dateObj.getMonth() + 1)).slice(-2);
                const year     = dateObj.getFullYear();

                this.startDate = `${year}-${month}-01`;
                this.endDate   = erp_acct_var.current_date;

                this.fetchData();
            });
        },

        methods: {
            fetchData() {
                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get('/tax-agencies').then(res => {
                    this.taxAgencies    = res.data;
                }).then(() => {
                    if ( this.taxAgencies && this.taxAgencies[0] !== undefined ) {
                        this.selectedAgency = this.taxAgencies[0];
                        this.getReport();
                    }
                });
            },

            getReport() {
                if ( ! this.selectedAgency ) {
                    return this.$store.dispatch('spinner/setSpinner', false);
                }

                this.$store.dispatch('spinner/setSpinner', true);
                this.rows = [];

                HTTP.get('/reports/sales-tax', {
                    params: {
                        agency_id  : this.selectedAgency.id,
                        start_date : this.startDate,
                        end_date   : this.endDate
                    }
                }).then(response => {
                    this.rows        = response.data.details;
                    this.totalDebit  = response.data.extra.total_debit;
                    this.totalCredit = response.data.extra.total_credit;

                    this.rows.forEach(item => {
                        item.trn_date   = this.formatDate(item.trn_date);
                        item.created_at = this.formatDate(item.created_at);
                    });

                    this.$store.dispatch('spinner/setSpinner', false);
                }).catch(_ => {
                    this.$store.dispatch('spinner/setSpinner', false);
                });
            },

            printPopup() {
                window.print();
            }
        }
    };
</script>

<style scoped lang="less">
    .sales-tax-table {
        @media screen {
            @media( max-width: 782px ) {
                tfoot {
                    tr:not(.inline-edit-row):not(.no-items) td {
                        padding: 10px 10px 10px 35%;
                    }

                    tr {
                        td:first-child {
                            display: none !important;
                        }

                        td[data-left-align] {
                            padding-left: 10px !important;
                        }
                    }
                }
            }
        }
    }
</style>
