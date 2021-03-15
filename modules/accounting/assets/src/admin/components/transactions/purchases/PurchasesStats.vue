<template>
    <div class="wperp-stats wperp-section">
        <div class="wperp-panel wperp-panel-default">
            <div class="wperp-panel-body">
                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <pie-chart v-if="chartPurchase.values.length"
                            id="payment"
                            :title="__('Payment', 'erp')"
                            :labels="chartPurchase.labels"
                            :colors="chartPurchase.colors"
                            :data="chartPurchase.values" />
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
                            <div class="wperp-total"><h2>{{ formatAmount(chartPurchase.outstanding) }}</h2></div>
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
    name: 'PurchaseStats',

    components: {
        PieChart
    },

    data() {
        return {
            chartStatus: {
                colors: ['#208DF8', '#E9485E', '#FF9900', '#2DCB67', '#9c27b0', '#e3ff66'],
                labels: [],
                values: []
            },
            chartPurchase: {
                colors: ['#40c4ff', '#e91e63'],
                labels: [ __('Paid', 'erp'), __('Payable', 'erp') ],
                values: [],
                outstanding: 0
            }
        };
    },

    created() {
        this.$root.$on('transactions-filter', filters => {
           // this.getChartData(filters);
        });

        const filters = {};

        if (this.$route.query.start && this.$route.query.end) {
            filters.start_date = this.$route.query.start;
            filters.end_date = this.$route.query.end;
        }

        this.getChartData(filters);
    },

    watch: {
        $route: 'getChartData'
    },

    methods: {
        getChartData(filters = {}) {
            HTTP.get('/transactions/purchase/chart-purchase', {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(response => {
                this.chartPurchase.outstanding = response.data.payable;

                this.chartPurchase.values.push(
                    response.data.paid,
                    response.data.payable
                );
            });

            HTTP.get('/transactions/purchase/chart-status', {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(response => {
                response.data.forEach(element => {
                    this.chartStatus.labels.push(element.type_name);
                    this.chartStatus.values.push(parseInt(element.sub_total));
                });
            });
        }
    }

};
</script>

<style scoped>

</style>
