<template>
    <div class="wperp-stats wperp-section">
        <div class="wperp-panel wperp-panel-default">
            <div class="wperp-panel-body">
                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <pie-chart v-if="chartExpense.values.length"
                                   id="payment"
                                   :title= "__('Payment', 'erp')"
                                   :labels="chartExpense.labels"
                                   :colors="chartExpense.colors"
                                   :data="chartExpense.values" />
                    </div>
                    <div class="wperp-col-sm-4">
                        <pie-chart v-if="chartStatus.values.length"
                                   id="status"
                                   :title="__('Status', 'erp')"
                                   sign=""
                                   :labels="chartStatus.labels"
                                   :colors="chartStatus.colors"
                                   :data="chartStatus.values" />
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block">
                            <h3>{{ __('Outstanding', 'erp') }}</h3>
                            <div class="wperp-total"><h2>{{ formatAmount(chartExpense.outstanding) }}</h2></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import PieChart from 'admin/components/chart/PieChart.vue';

export default {
    name: 'ExpenseStats',

    components: {
        PieChart
    },

    data() {
        return {
            chartStatus: {
                colors: ['#208DF8', '#E9485E', '#FF9900', '#2DCB67', '#9c27b0'],
                labels: [],
                values: []
            },
            chartExpense: {
                colors: ['#40c4ff', '#e91e63'],
                labels: [ __('Paid', 'erp'), __('Payable', 'erp') ],
                values: [],
                outstanding: 0
            }
        };
    },

    created() {
        this.$root.$on('transactions-filter', filters => {
            //this.getExpenseChartData(filters);
        });

        const filters = {};

        if (this.$route.query.start && this.$route.query.end) {
            filters.start_date = this.$route.query.start;
            filters.end_date = this.$route.query.end;
        }

        this.getExpenseChartData(filters);
    },

    watch: {
        $route: 'getExpenseChartData'
    },

    methods: {
        getExpenseChartData(filters = {}) {
            HTTP.get('/transactions/expense/chart-expense', {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(response => {
                this.chartExpense.outstanding = response.data.payable;

                this.chartExpense.values.push(
                    response.data.paid,
                    response.data.payable
                );
            });

            HTTP.get('/transactions/expense/chart-status', {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(response => {
                response.data.forEach(element => {
                    if (typeof element === 'object' && element !== null) {
                        this.chartStatus.labels.push(element.type_name);
                        this.chartStatus.values.push(parseInt(element.sub_total));
                    }
                });
            });
        }
    }

};
</script>

<style scoped>

</style>
