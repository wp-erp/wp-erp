<template>
    <div class="wperp-stats wperp-section">
        <div class="wperp-panel wperp-panel-default">
            <div class="wperp-panel-body">
                <div class="wperp-row">
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block has-separator">
                            <h3>Payment</h3>
                            <div class="payment-chart">
                                <div class="chart-container"><div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;"><div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div><div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
                                    <canvas id="payment_chart" hieght="84" style="display: block; height: 84px; width: 168px;" width="336" height="168" class="chartjs-render-monitor"></canvas>
                                </div>
                                <div id="payment_legend" class="chart-legend"><ul class="chart-labels-list"><li><div class="label-icon-wrapper">							<span class="chart-label-icon" style="background-color:#55D8FE"></span>							</div><div class="chart-label-values"><span class="chart-value">$ 794</span><br><span class="chart-label">Recieved</span></div></li><li><div class="label-icon-wrapper">							<span class="chart-label-icon" style="background-color:#FF8373"></span>							</div><div class="chart-label-values"><span class="chart-value">$ 458</span><br><span class="chart-label">Outstanding</span></div></li></ul></div>
                            </div>
                        </div>
                    </div>
                    <div class="wperp-col-sm-4">
                        <div class="wperp-chart-block has-separator">
                            <h3>Status</h3>
                            <div class="payment-chart">
                                <div class="chart-container"><div class="chartjs-size-monitor" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; overflow: hidden; pointer-events: none; visibility: hidden; z-index: -1;"><div class="chartjs-size-monitor-expand" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:1000000px;height:1000000px;left:0;top:0"></div></div><div class="chartjs-size-monitor-shrink" style="position:absolute;left:0;top:0;right:0;bottom:0;overflow:hidden;pointer-events:none;visibility:hidden;z-index:-1;"><div style="position:absolute;width:200%;height:200%;left:0; top:0"></div></div></div>
                                    <canvas id="status_chart" hieght="84" width="336" height="168" class="chartjs-render-monitor" style="display: block; height: 84px; width: 168px;"></canvas>
                                </div>
                                <div id="status_legend" class="chart-legend"><ul class="chart-labels-list"><li><div class="label-icon-wrapper">							<span class="chart-label-icon" style="background-color:#208DF8"></span>							</div><div class="chart-label-values"><span class="chart-value">2</span><span class="chart-label"> Paid</span></div></li><li><div class="label-icon-wrapper">							<span class="chart-label-icon" style="background-color:#E9485E"></span>							</div><div class="chart-label-values"><span class="chart-value">1</span><span class="chart-label"> Overdue</span></div></li><li><div class="label-icon-wrapper">							<span class="chart-label-icon" style="background-color:#FF9900"></span>							</div><div class="chart-label-values"><span class="chart-value">2</span><span class="chart-label"> Partial</span></div></li><li><div class="label-icon-wrapper">							<span class="chart-label-icon" style="background-color:#2DCB67"></span>							</div><div class="chart-label-values"><span class="chart-value">3</span><span class="chart-label"> Draft</span></div></li></ul></div>
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
        name: "stat-chart"
    }
</script>

<style scoped>

</style>
