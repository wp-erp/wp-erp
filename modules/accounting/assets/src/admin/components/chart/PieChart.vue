<template>
    <div class="wperp-chart-block has-separator">
        <h3>{{ title }}</h3>
        <div class="payment-chart">
            <div class="chart-container">
                <canvas :id="`${id}_chart`" hieght="84"></canvas>
            </div>
            <div :id="`${id}_legend`" class="chart-legend"></div>
        </div>
    </div>
</template>

<script>
    import 'assets/js/plugins/chart.min'

    export default {
        name: 'PieChart',

        props: ['id', 'title', 'sign', 'labels', 'colors', 'data'],

        data() {
            return {}
        },

        methods: {
            makeChart() {
                var self = this;
                var colors = this.colors,
                    labels = this.labels,
                    data = this.data,
                    bgColor = colors,
                    dataChart = {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: bgColor
                        }]
                    },
                    config = {
                        type: 'doughnut',
                        data: dataChart,
                        options: {
                            maintainAspectRatio: true,
                            cutoutPercentage: 45,
                            legend: {
                                display: false
                            },
                            // generate custom labels
                            legendCallback: function (chart) {
                                var text = [];
                                text.push('<ul class="chart-labels-list">');
                                if (chart.data.datasets.length) {
                                    for (var i = 0; i < chart.data.datasets[0].data.length; ++i) {
                                        text.push('<li><div class="label-icon-wrapper">\
                                        <span class="chart-label-icon" style="background-color:' + chart.data.datasets[0].backgroundColor[i] + '"></span>\
                                        </div><div class="chart-label-values">');
                                        if (chart.data.datasets[0].data[i]) {
                                            if (self.id == 'payment') {
                                                text.push('<span class="chart-value">' + self.sign + chart.data.datasets[0].data[i] + '</span><br>');
                                            } else {
                                                text.push('<span class="chart-value">' + chart.data.datasets[0].data[i]);
                                            }

                                        }
                                        if (chart.data.labels[i]) {
                                            text.push('<span class="chart-label"> ' + chart.data.labels[i] + '</span>');
                                        }
                                        text.push('</div></li>');
                                    }
                                }
                                text.push('</ul>');

                                return text.join("");
                            },
                            // generate custom tooltips
                            tooltips: {
                                yPadding: 10,
                                callbacks: {
                                    label: function (tooltipItem, data) {
                                        var total = 0;

                                        data.datasets[tooltipItem.datasetIndex].data.forEach(function (element /*, index, array*/) {
                                            total += parseFloat(element);
                                        });

                                        var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                        var percentTxt = Math.round(value / total * 100);
                                        return data.labels[tooltipItem.index] + ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + ' (' + percentTxt + '%)';
                                    }
                                }
                            }
                        }
                    };

                var self = this;

                setTimeout(function () {
                    var chart_ctx = document.getElementById(self.id + '_chart');

                    if (chart_ctx !== null) {
                        var chart_ctx = chart_ctx.getContext("2d"),
                            chart = new Chart(chart_ctx, config),
                            legend = chart.generateLegend(),
                            legendHolder = document.getElementById(self.id + "_legend");
                        legendHolder.innerHTML = legend
                    }
                }, 1000);
            }
        },

        created() {
            this.makeChart();
        }
    }

</script>

<style lang="less">

</style>
