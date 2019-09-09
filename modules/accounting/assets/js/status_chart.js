var colors = ['#208DF8', '#E9485E', '#FF9900', '#2DCB67'],
	labels = ['Paid', 'Overdue', 'Partial', 'Draft'],
	data = [2, 1, 2, 3],
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
							text.push('<span class="chart-value">' + chart.data.datasets[0].data[i] + '</span>');
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
		}
	};
	
	setTimeout(function() {
		var status_chart_ctx = document.getElementById('status_chart');

		if ( status_chart_ctx !== null ) {
			status_chart_ctx = status_chart_ctx.getContext("2d"),
			status_chart = new Chart(status_chart_ctx, config),
			status_legend = status_chart.generateLegend(),
			status_legendHolder = document.getElementById("status_legend");
			status_legendHolder.innerHTML = status_legend
		}
	
	}, 1000);
