<template>
    <div class="sales-tax-report">
        <h2>{{ __('Sales Tax Report', 'erp') }}</h2>

        <form action="" method="" @submit.prevent="getSalesTaxReport" class="query-options no-print">


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


        <list-table
            tableClass="wperp-table table-striped table-dark widefat sales-tax-table"
            :columns="columns"
            :rows="taxes"
            :showCb="false">
            <template slot="trn_no" slot-scope="data">
                <strong>
                    <router-link :to="{ name: 'SalesSingle', params: {
                            id: data.row.voucher_no,
                            type: 'invoice'
                        }}">
                        <span v-if="data.row.voucher_no">#{{ data.row.voucher_no }}</span>
                    </router-link>
                </strong>
            </template>
            <template slot="tax_amount" slot-scope="data">
                {{ moneyFormat( parseFloat(data.row.tax_amount) ) }}
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
                columns: {
                    trn_date: {label: 'Trns Date'},
                    trn_no: {label: 'Trns No'},
                    tax_amount: {label: 'Tax Amount'}
                },
                taxes: []
            };
        },
        computed: {
            totalTax() {
                let total = 0;
                this.taxes.forEach(item => {
                    total += parseFloat(item.tax_amount)
                });
                return total
            }
        },
        created() {
            // ? why is nextTick here ...? i don't know.
            this.$nextTick(function () {
                const dateObj = new Date();

                // with leading zero, and JS month are zero index based
                const month = ('0' + (dateObj.getMonth() + 1)).slice(-2);

                this.start_date = `${dateObj.getFullYear()}-${month}-01`;
                this.end_date = erp_acct_var.current_date; /* global erp_acct_var */
            });

        },

        methods: {
            getSalesTaxReport() {


                this.$store.dispatch('spinner/setSpinner', true);

                this.rows = [];

                HTTP.get('/tax-reports/sales', {
                    params: {
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
