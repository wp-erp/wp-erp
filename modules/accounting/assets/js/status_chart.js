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
			aspectRatio: 1.8,
			cutout: '45%',
			plugins: {
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
									text.push(`<span class="chart-value">${datasets[0].data[i]}</span>`);
								}

								if (labels[i]) {
									text.push(`<span class="chart-label"> ${labels[i]}</span>`);
								}

								text.push(`</div></li>`);
							}

							text.push(`</ul>`);

							// Set the custom legend HTML
							document.getElementById('status_legend').innerHTML = text.join('');

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
		let status_chart_ctx = document.getElementById('status_chart');

		if ( status_chart_ctx !== null ) {
			status_chart_ctx = status_chart_ctx.getContext("2d");
			new Chart(status_chart_ctx, config);
		}
	}, 1000);
