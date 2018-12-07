var colors = ['#55D8FE', '#FF8373'],
	labels = ['Recieved', 'Outstanding'],
	data = [794, 458],
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
			legendCallback: function(chart) {
				var text = [];
				text.push('<ul class="chart-labels-list">');
				if (chart.data.datasets.length) {
					for (var i = 0; i < chart.data.datasets[0].data.length; ++i) {
						text.push('<li><div class="label-icon-wrapper">\
							<span class="chart-label-icon" style="background-color:' + chart.data.datasets[0].backgroundColor[i] + '"></span>\
							</div><div class="chart-label-values">');
						if (chart.data.datasets[0].data[i]) {
							text.push('<span class="chart-value">$ ' + chart.data.datasets[0].data[i] + '</span><br>');
						}
						if (chart.data.labels[i]) {
							text.push('<span class="chart-label">' + chart.data.labels[i] + '</span>');
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
					label: function(tooltipItem, data) {
						var total = 0;
						data.datasets[tooltipItem.datasetIndex].data.forEach(function(element /*, index, array*/ ) {
							total += element;
						});
						var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
						var percentTxt = Math.round(value / total * 100);
						return data.labels[tooltipItem.index] + ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + ' (' + percentTxt + '%)';
					}
				}
			}
		}
	},

	payment_chart_ctx = document.getElementById("payment_chart").getContext("2d"),
	payment_chart = new Chart(payment_chart_ctx, config),

	payment_legend = payment_chart.generateLegend(),
	payment_legendHolder = document.getElementById("payment_legend");

	// legendHolder.innerHTML = legend + '<div style="font-size: smaller">Total : <strong>' + 1703 + '</strong></div>';
	payment_legendHolder.innerHTML = payment_legend
