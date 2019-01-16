<template>
    <div class="wperp-containers">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Dashboard</h2>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <div class="wperp-dashboard">
            <div class="wperp-row">
                <div class="wperp-col-md-9 wperp-col-sm-12 wperp-col-xs-12">
                    <!-- Start .income-expense-section -->
                    <div class="income-expense-section wperp-panel wperp-panel-default">
                        <div class="wperp-panel-heading wperp-bg-white"><h4>Income & Expense</h4></div>
                        <div class="wperp-panel-body">
                            <div class="wperp-custom-select wperp-custom-select--inline-block wperp-pull-right mb-20">
                                <select name="query_time" class="wperp-form-field" id="att-filter-duration">
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_quarter">This Quarter</option>
                                    <option value="last_quarter" selected="selected">Last Quarter</option>
                                    <option value="this_year">This Year</option>
                                    <option value="last_year">Last Year</option>
                                    <option value="custom">Custom</option>
                                </select>
                                <i class="flaticon-arrow-down-sign-to-navigate"></i>
                            </div>

                            <div class="wperp-chart-block">
                                <canvas id="bar_chart" ref="bar_chart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- End .income-expense-section -->

                    <div class="wperp-row">
                        <div class="wperp-col-sm-6 wperp-col-xs-12">
                            <!-- Start .invoice-own-section -->
                            <div class="invoice-own-section wperp-panel wperp-panel-default">
                                <div class="wperp-panel-heading wperp-bg-white"><h4>Invoices owed to you</h4></div>
                                <div class="wperp-panel-body pb-0">
                                    <ul class="wperp-list-unstyled list-table-content">
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                        <li class="total">
                                            <span class="title">Total Balance</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- End .invoice-own-section -->
                        </div>
                        <div class="wperp-col-sm-6 wperp-col-xs-12">
                            <!-- Start .invoice-own-section -->
                            <div class="invoice-own-section wperp-panel wperp-panel-default">
                                <div class="wperp-panel-heading wperp-bg-white"><h4>Invoices owed by you</h4></div>
                                <div class="wperp-panel-body pb-0">
                                    <ul class="wperp-list-unstyled list-table-content">
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                        <li class="total">
                                            <span class="title">Total Balance</span>
                                            <span class="price">$165,290.00</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- End .invoice-own-section -->
                        </div>
                    </div>
                </div>
                <div class="wperp-col-md-3 wperp-col-sm-12 wperp-col-xs-12">
                    <!-- Start .bank-accounts-section -->
                    <accounts></accounts>
                    <!-- End .bank-accounts-section -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import 'assets/js/plugins/chart.min'
    import Dropdown from 'admin/components/base/Dropdown.vue'
    import MetaBox from 'admin/components/wp/MetaBox.vue'
    import Accounts from 'admin/components/dashboard/Accounts.vue'

    export default {
        name: 'Dashboard',

        components: {
            Accounts,
            MetaBox,
            Dropdown
        },

        data () {
            return {
                title1: 'Income & Expenses',
                title2: 'Bank Accounts',
                title3: 'Invoices owed to you',
                title4: 'Bills to pay',
                closable: true,
                msg: 'Accounting',
            }
        },

        mounted () {
            let colors = ['#208DF8'],
                labels2 = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                data = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 20],
                bgColor = colors,
                dataChart = {
                    labels: labels2,
                    datasets: [{
                        label: 'Dataset 1',
                        data: data,
                        backgroundColor: '#208DF8'
                    }]
                },
                config = {
                    type: 'bar',
                    data: dataChart,
                    options: {
                        maintainAspectRatio: true,
                        responsive: true,
                        legend: {
                            display: false
                        }
                    }
                };
                let bar_chart_ctx = this.$refs['bar_chart'].getContext("2d");
                if( bar_chart_ctx !== null || bar_chart_ctx !== undefined ){
                    new Chart(bar_chart_ctx, config);
                }

        },

       created(){

       },

        computed: {

        },

        methods: {

        }

    }
</script>

<style scoped>

</style>
