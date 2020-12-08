<template>
    <div class="sales-tax-report">
        <h2>{{ __('Sales Tax Report Customer Based', 'erp') }}</h2>

        <form action="" method="" @submit.prevent="getSalesTaxReport" class="query-options no-print">
            <div class="with-multiselect">
                <multi-select v-model="customer" :options="customers   "/>
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

        <ul class="report-header" v-if="null !== customer">
            <li><strong>{{ __('Customer Name', 'erp') }}:</strong> <em>{{ customer.name }}</em></li>
            <li><strong>{{ __('Currency', 'erp') }}:</strong> <em>{{ symbol }}</em></li>
            <li><strong>{{ __('For the period of ( Transaction date )', 'erp') }}:</strong> <em>{{
                formatDate(start_date) }}</em> to <em>{{ formatDate(end_date) }}</em></li>
        </ul>

        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="taxes"
            :showCb="false">
            <template slot="voucher_no" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'DynamicTrnLoader', params: { id: data.row.trn_no }}">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>
            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat(data.row.tax_amount) }}
            </template>
            <template slot="tfoot">
                <tr class="tfoot">
                    <td></td>
                    <td>{{ __('Total', 'erp') }} =</td>
                    <td>{{ moneyFormat(totalTax) }}</td>
                </tr>
            </template>
        </list-table>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import ListTable from 'admin/components/list-table/ListTable.vue';
    import Datepicker from 'admin/components/base/Datepicker.vue';
    import MultiSelect from 'admin/components/select/MultiSelect.vue';
    import {mapState} from "vuex";

    export default {
        name: 'SalesTax',

        components: {
            ListTable,
            Datepicker,
            MultiSelect
        },

        data() {
            return {
                start_date: null,
                end_date: null,
                customer: null,
                taxes: [],
                columns: {
                    trn_date: {label: 'Trns Date'},
                    voucher_no: {label: 'Voucher No'},
                    tax_amount: {label: 'Tax Amount'},
                },
                symbol: erp_acct_var.symbol
            };
        },

        computed: mapState({
            customers: state => state.sales.customers,
            totalTax() {
                let total = 0;
                this.taxes.forEach(item => {
                    total += parseFloat(item.tax_amount)
                });
                return total;
            }
        }),
        created() {
            // ? why is nextTick here ...? i don't know.
            this.$nextTick(function () {
                const dateObj = new Date();

                // with leading zero, and JS month are zero index based
                const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);

                this.start_date = `${dateObj.getFullYear()}-${month}-01`;
                this.end_date = erp_acct_var.current_date; /* global erp_acct_var */
            });

            if (!this.customers.length) {
                this.$store.dispatch('sales/fillCustomers', []);
            }
        },

        methods: {
            getSalesTaxReport() {
                if (this.customer === null) return;

                this.$store.dispatch('spinner/setSpinner', true);

                HTTP.get('/tax-reports/customer-wise-sales', {
                    params: {
                        customer_id: this.customer.id,
                        start_date: this.start_date,
                        end_date: this.end_date
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

<style>

</style>
