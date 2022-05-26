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

export default {
    name: 'PieChart',

    props: ['id', 'title', 'labels', 'colors', 'data'],

    data() {
        return {};
    },

    methods: {
        makeChart() {
            let self = this;
            let colors = this.colors;
            let labels = this.labels;
            let data = this.data;
            let bgColor = colors;
            let dataChart = {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: bgColor
                }]
            };
            let config = {
                type: 'doughnut',
                data: dataChart,
                options: {
                    maintainAspectRatio: true,
                    aspectRatio: 1.8,
                    cutout: '45%',
                    plugins: {
                        // Custom Tooltip
                        tooltip: {
                            yPadding: 10,
                            callbacks: {
                                label: function(context) {
                                    let total = 0;
                                    const { dataset, label, raw, formattedValue } = context;

                                    dataset.data.forEach(function(element) {
                                        if (element !== 0) {
                                            total += parseFloat(element);
                                        }
                                    });

                                    const percentTxt = Math.round(raw / total * 100);
                                    return `${label} : ${formattedValue} (${percentTxt}%)`;
                                }
                            }
                        },

                        // Custom Legend Generator
                        legend: {
                            display: true,
                            labels: {
                                generateLabels: function(chart) {
                                    const { data } = chart;
                                    const { datasets, labels } = data;

                                    if (! datasets.length) {
                                        return [];
                                    }

                                    let text = [];
                                    text.push('<ul class="chart-labels-list">');

                                    for (let i = 0; i < datasets[0].data.length; ++i) {
                                        text.push(`<li>
                                            <div class="label-icon-wrapper">
                                                <span class="chart-label-icon" style="background-color:${datasets[0].backgroundColor[i]}"></span>
                                            </div>
                                            <div class="chart-label-values">
                                        `);

                                        if (datasets[0].data[i]) {
                                            if (self.id === 'payment') {
                                                text.push(`<span class="chart-value">${self.moneyFormat(datasets[0].data[i])}</span><br>`);
                                            } else {
                                                text.push(`<span class="chart-value">${datasets[0].data[i]}</span>`);
                                            }
                                        }

                                        if (labels[i]) {
                                            text.push(`<span class="chart-label"> ${labels[i]}</span>`);
                                        }

                                        text.push(`</div></li>`);
                                    }

                                    text.push(`</ul>`);

                                    // Set the custom legend HTML
                                    document.getElementById(self.id + '_legend').innerHTML = text.join('');

                                    // We don't need to manage legend items,
                                    // as if we're just updated the Inner HTML element
                                    return [];
                                }
                            },
                        }
                    }
                }
            };

            setTimeout(function() {
                let chartCtx = document.getElementById(self.id + '_chart');

                if (chartCtx !== null) {
                    chartCtx = chartCtx.getContext('2d');
                    new Chart(chartCtx, config);
                }
            }, 1000);
        }
    },

    created() {
        this.makeChart();
    }
};

</script>

<style lang="less">

</style>
