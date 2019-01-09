<template>
    <div class="wperp-stats wperp-section">
        <div class="wperp-panel wperp-panel-default">
            <div class="wperp-panel-body">
                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block has-separator">
                            <h3>Payment</h3>
                            <div class="payment-chart">
                                <div class="chart-container">
                                    <canvas id="payment_chart" hieght="84"></canvas>
                                </div>
                                <div id="payment_legend" class="chart-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block has-separator">
                            <h3>Status</h3>
                            <div class="payment-chart">
                                <div class="chart-container">
                                    <canvas id="status_chart" hieght="84"></canvas>
                                </div>
                                <div id="status_legend" class="chart-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block">
                            <h3>Outstanding</h3>
                            <div class="wperp-total"><h2>$20000,00</h2></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ExpensesStats'
    }
</script>

<style scoped>

</style>