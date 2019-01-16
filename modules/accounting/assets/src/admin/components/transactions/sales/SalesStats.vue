<template>
    <div class="wperp-stats wperp-section">
        <div class="wperp-panel wperp-panel-default">
            <div class="wperp-panel-body">
                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <pie-chart 
                            id="payment"
                            title="Payment"
                            :sign="getCurrencySign()"
                            :labels="chartPayment.labels"
                            :colors="chartPayment.colors"
                            :data="chartPayment.values" />
                    </div>
                    <div class="wperp-col-sm-4">
                        <pie-chart 
                            id="status"
                            title="Status"
                            sign=""
                            :labels="chartStatus.labels"
                            :colors="chartStatus.colors"
                            :data="chartStatus.values" />
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block">
                            <h3>Outstanding</h3>
                            <div class="wperp-total"><h2>{{ formatAmount(chartPayment.outstanding) }}</h2></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import PieChart from 'admin/components/chart/PieChart.vue'

    export default {
        name: 'SalesStats',

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
                chartPayment: {
                    colors: ['#40c4ff', '#e91e63'],
                    labels: ['Received', 'Outstanding'],
                    values: [],
                    outstanding: 0
                },
            };
        },

        created() {
            this.getSalesChartData();
        },

        methods: {
            getSalesChartData() {
                HTTP.get('/transactions/sales/chart-payment').then( response => {                    
                    this.chartPayment.outstanding = response.data.outstanding;

                    this.chartPayment.values.push(
                        response.data.received,
                        response.data.outstanding
                    );
                });

                HTTP.get('/transactions/sales/chart-status').then( response => {
                    response.data.forEach(element => {
                        this.chartStatus.labels.push(element.invoice_type)
                        this.chartStatus.values.push(element.sub_total)
                    });
                });
            }
        }

    }
</script>

<style scoped>

</style>