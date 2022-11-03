<template>
    <div class="sales-tax-report">
        <h2 class="title-container">
            <span>{{ __( 'Sales Tax Report (Customer Based)', 'erp' ) }}</span>

            <router-link
                class="wperp-btn btn--primary"
                :to="{ name: 'SalesTaxReportOverview' }">
                {{ __( 'Back', 'erp' ) }}
            </router-link>
        </h2>

        <form @submit.prevent="getReport" class="query-options no-print">
            <div class="wperp-date-group">
                <div class="with-multiselect">
                    <multi-select v-model="customer" :options="customers" @input="getReport" />
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

        <ul class="report-header" v-if="null !== customer">
            <li>
                <strong>{{ __( 'Customer Name', 'erp' ) }}:</strong>
                <em> {{ customer.name }}</em>
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
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table sales-tax-table-customer"
            :columns="columns"
            :rows="taxes"
            :showCb="false">

            <template slot="voucher_no" slot-scope="data">
                <strong>
                    <router-link
                        :to="{
                            name   : 'DynamicTrnLoader',
                            params : {
                                id : data.row.voucher_no
                            }
                        }">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>

            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat( data.row.tax_amount ) }}
            </template>

            <template slot="tfoot">
                <tr class="tfoot">
                    <td></td>
                    <td>{{ __( 'Total', 'erp' ) }} =</td>
                    <td>{{ moneyFormat( totalTax ) }}</td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

<script>
    import HTTP         from 'admin/http';
    import ListTable    from '../../list-table/ListTable.vue';
    import Datepicker   from '../../base/Datepicker.vue';
    import MultiSelect  from '../../select/MultiSelect.vue';
    import { mapState } from 'vuex';

    export default {
        name: 'SalesTaxReportCustomerBased',

        components: {
            ListTable,
            Datepicker,
            MultiSelect
        },

        data() {
            return {
                startDate : null,
                endDate   : null,
                customer  : null,
                taxes     : [],
                symbol    : erp_acct_var.symbol,
                columns   : {
                    voucher_no : {
                        label  : __( 'Voucher No', 'erp' )
                    },
                    trn_date   : {
                        label  : __( 'Transaction Date', 'erp' )
                    },
                    tax_amount : {
                        label  : __( 'Tax Amount', 'erp' )
                    },
                },
            };
        },

        computed: {
            ...mapState({
                customers: state => state.sales.customers,
            }),

            totalTax() {
                let total = 0;

                this.taxes.forEach(item => {
                    total += parseFloat( item.tax_amount )
                });

                return total;
            }
        },

        watch: {
            customer() {
                this.taxes = [];
            }
        },

        created() {
            this.$nextTick(() => {
                const dateObj  = new Date();
                const month    = ( '0' + ( dateObj.getMonth() + 1 ) ).slice( -2 );
                const year     = dateObj.getFullYear();

                this.startDate = `${year}-${month}-01`;
                this.endDate   = erp_acct_var.current_date;

                if ( ! this.customers.length ) {
                    this.$store.dispatch('sales/fillCustomers', []);
                }

                if ( this.customers[0] !== undefined ) {
                    this.customer = this.customers[0];
                }

                this.getReport();
            });
        },

        methods: {
            getReport() {
                if ( ! this.customer ) {
                    return;
                }

                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get('/reports/sales-tax', {
                    params: {
                        customer_id : this.customer.id,
                        start_date  : this.startDate,
                        end_date    : this.endDate
                    }
                }).then(response => {
                    this.taxes = response.data;
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
@media screen and ( max-width: 782px ) {
    .sales-tax-table-customer {
        thead {
            th {
                &.column.trn_date, &.column.tax_amount {
                    display: none;
                }
            }
        }

        tfoot tr.tfoot {
            td:first-child {
                display: none !important;
            }
        }
    }
}
</style>
