<template>
    <div class="income-expense-section wperp-panel wperp-panel-default">
        <div class="wperp-panel-heading wperp-bg-white">
            <h4>{{ __('Income & Expense', 'erp') }}</h4>

            <div class="wperp-custom-select wperp-custom-select--inline-block wperp-pull-right" v-if="showDropdown">
                <select name="query_time" class="wperp-form-field" id="att-filter-duration" v-model="chartRange" >
                    <option value="this_month">{{ __('This Month', 'erp') }}</option>
                    <option value="last_month">{{ __('Last Month', 'erp') }}</option>
                    <option value="this_quarter" v-if="thisQuarter.labels.length">{{ __('This Quarter', 'erp') }}</option>
                    <option value="last_quarter" v-if="lastQuarter.labels.length">{{ __('Last Quarter', 'erp') }}</option>
                    <option value="this_year">{{ __('This Year', 'erp') }}</option>
                    <option value="last_year" v-if="lastYear.labels.length">{{ __('Last Year', 'erp') }}</option>
                </select>
                <i class="flaticon-arrow-down-sign-to-navigate"></i>
            </div>
        </div>
        <div class="wperp-panel-body">
            <div class="wperp-chart-block">
                <canvas id="bar_chart" ref="bar_chart" height="90"></canvas>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import 'admin/components/chart/chart.min';

export default {
    name: 'Chart',

    data() {
        return {
            items       : [],
            chart       : '',
            chartRange  : 'this_year',
            respData    : '',
            showDropdown: false
        };
    },

    created() {
        this.fetchData();
    },

    computed: {
        thisMonth() {
            return {
                labels : this.respData.thisMonth.labels,
                datasets : [
                    {
                        label: __('Income', 'erp'),
                        data: this.respData.thisMonth.income,
                        backgroundColor: '#208DF8'
                    },
                    {
                        label: __('Expense', 'erp'),
                        data: this.respData.thisMonth.expense,
                        backgroundColor: '#f86e2d'
                    }
                ]
            };
        },

        lastMonth() {
            return {
                labels : this.respData.lastMonth.labels,
                datasets : [
                    {
                        label: __('Income', 'erp'),
                        data: this.respData.lastMonth.income,
                        backgroundColor: '#208DF8'
                    },
                    {
                        label: __('Expense', 'erp'),
                        data: this.respData.lastYear.expense,
                        backgroundColor: '#f86e2d'
                    }
                ]
            };
        },

        thisYear() {
            return {
                labels : this.respData.thisYear.labels,
                datasets : [
                    {
                        label: __('Income', 'erp'),
                        data: this.respData.thisYear.income,
                        backgroundColor: '#208DF8'
                    },
                    {
                        label: __('Expense', 'erp'),
                        data: this.respData.thisYear.expense,
                        backgroundColor: '#f86e2d'
                    }
                ]
            };
        },

        lastYear() {
            return {
                labels : this.respData.lastYear.labels,
                datasets : [
                    {
                        label: __('Income', 'erp'),
                        data: this.respData.lastYear.income,
                        backgroundColor: '#208DF8'
                    },
                    {
                        label: __('Expense', 'erp'),
                        data: this.respData.lastYear.expense,
                        backgroundColor: '#f86e2d'
                    }
                ]
            };
        },

        thisQuarter() {
            const quarter = this.getQuarterRange();
            const labels = this.thisYear.labels.slice(quarter.start, quarter.end + 1);
            const newIncome = this.thisYear.datasets[0].data.slice(quarter.start, quarter.end + 1);
            const newExpense = this.thisYear.datasets[1].data.slice(quarter.start, quarter.end + 1);

            return {
                labels : labels,
                datasets : [
                    {
                        label: __('Income', 'erp'),
                        data: newIncome,
                        backgroundColor: '#208DF8'
                    },
                    {
                        label: __('Expense', 'erp'),
                        data: newExpense,
                        backgroundColor: '#f86e2d'
                    }
                ]
            };
        },

        lastQuarter() {
            const quarter = this.getQuarterRange('previous');

            let yearData = this.thisYear;

            if (quarter.start < 0) {
                yearData = this.lastYear;
                quarter.start = quarter.start + 12;
                quarter.end = quarter.end + 12;
            }

            const labels = yearData.labels.slice(quarter.start, quarter.end + 1);
            const newIncome = yearData.datasets[0].data.slice(quarter.start, quarter.end + 1);
            const newExpense = yearData.datasets[1].data.slice(quarter.start, quarter.end + 1);

            return {
                labels : labels,
                datasets : [
                    {
                        label: __('Income', 'erp'),
                        data: newIncome,
                        backgroundColor: '#208DF8'
                    },
                    {
                        label: __('Expense', 'erp'),
                        data: newExpense,
                        backgroundColor: '#f86e2d'
                    }
                ]
            };
        }

    },

    methods: {
        createChart() {
            const dataChart = {
                labels: this.thisYear.labels,
                datasets: this.thisYear.datasets
            };
            const config = {
                type: 'bar',
                data: dataChart,
                options: {
                    maintainAspectRatio: true,
                    responsive: true,
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value, index, values) {
                                    return erp_acct_var.symbol + value;
                                }
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItems, data) {
                                return erp_acct_var.symbol + tooltipItems.yLabel.toString();
                            }
                        }
                    }
                }
            };

            const bar_chart_ctx = this.$refs['bar_chart'].getContext('2d');
            this.chart = new Chart(bar_chart_ctx, config); /* global Chart */
        },

        fetchData() {
            HTTP.get('/transactions/income-expense-overview').then(response => {
                this.respData = response.data;
                this.createChart();
                this.showDropdown = true;
            });
        },

        updateRange() {
            let newlabels, newset;
            switch (this.chartRange) {
            case 'this_month':
                newlabels = this.thisMonth.labels;
                newset = this.thisMonth.datasets;
                break;
            case 'last_month':
                newlabels = this.lastMonth.labels;
                newset = this.lastMonth.datasets;
                break;
            case 'last_year':
                newlabels = this.lastYear.labels;
                newset = this.lastYear.datasets;
                break;
            case 'this_quarter':
                newlabels = this.thisQuarter.labels;
                newset = this.thisQuarter.datasets;
                break;
            case 'last_quarter':
                newlabels = this.lastQuarter.labels;
                newset = this.lastQuarter.datasets;
                break;

            default:
                newlabels = this.thisYear.labels;
                newset = this.thisYear.datasets;
                break;
            }
            // update labels
            this.chart.data.labels = newlabels;

            // update values
            this.chart.data.datasets = newset;

            // update Chart view
            this.chart.update();
        },

        getQuarterRange(val = 'current') {
            const today = new Date();
            const quarter = Math.floor((today.getMonth() / 3));
            const startDate = new Date(today.getFullYear(), quarter * 3, 1);

            const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 3, 0);

            if (val === 'current') {
                return { start : startDate.getMonth(), end : endDate.getMonth() };
            }

            return { start : startDate.getMonth() - 3, end : endDate.getMonth() - 3 };
        }

    },

    watch: {
        chartRange() {
            this.updateRange();
        }
    }
};
</script>

<style lang="less" scoped>
    .wperp-panel-heading.wperp-bg-white {
        padding: 10px;
    }
</style>
