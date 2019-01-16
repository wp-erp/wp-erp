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
                    </div>
                </div>
                <div class="wperp-col-md-3 wperp-col-sm-12 wperp-col-xs-12">
                    <!-- Start .bank-accounts-section -->
                    <div :class="['bank-accounts-section', 'wperp-panel', 'wperp-panel-default', ( isEditSettingsEnabled ? 'open-edit':'' )]">
                        <div class="wperp-panel-heading wperp-bg-white">
                            <h4>Bank Accounts</h4>
                            <a href="#" @click.prevent="isEditSettingsEnabled = !isEditSettingsEnabled" id="bank-account-edit" class="panel-badge">
                                <span v-if="isEditSettingsEnabled">Save</span>
                                <i v-else class="flaticon-quick-edit"></i>
                            </a>
                        </div>
                        <div class="wperp-panel-body pb-0">
                            <ul class="wperp-list-unstyled list-table-content list-table-content--border list-table-content--bg-space">
                                <li>
                                    <div class="left">
                                        <i class="flaticon-menu-1"></i>
                                        <a href="#" class="title">Checking</a>
                                    </div>
                                    <div class="right">
                                        <span class="price">$165,290.00</span>
                                        <i class="flaticon-trash"></i>
                                    </div>
                                </li>
                                <li>
                                    <div class="left">
                                        <i class="flaticon-menu-1"></i>
                                        <a href="#" class="title">Patty Cash</a>
                                    </div>
                                    <div class="right">
                                        <span class="price">$165,290.00</span>
                                        <i class="flaticon-trash"></i>
                                    </div>
                                </li>
                                <li>
                                    <div class="left">
                                        <i class="flaticon-menu-1"></i>
                                        <a href="#" class="title">Savings</a>
                                    </div>
                                    <div class="right">
                                        <span class="price">$165,290.00</span>
                                        <i class="flaticon-trash"></i>
                                    </div>
                                </li>
                                <li>
                                    <div class="left">
                                        <i class="flaticon-menu-1"></i>
                                        <a href="#" class="title">Master Card</a>
                                    </div>
                                    <div class="right">
                                        <span class="price">$165,290.00</span>
                                        <i class="flaticon-trash"></i>
                                    </div>
                                </li>
                                <li>
                                    <div class="left">
                                        <i class="flaticon-menu-1"></i>
                                        <a href="#" class="title">Visa</a>
                                    </div>
                                    <div class="right">
                                        <span class="price">$165,290.00</span>
                                        <i class="flaticon-trash"></i>
                                    </div>
                                </li>
                            </ul>
                            
                            <dropdown placement="bottom-start">
                                <template slot="button">
                                    <a href="#" class="dropdown-trigger wperp-btn btn--default">
                                        <i class="flaticon-add-plus-button"></i>
                                        <span>Add New</span>
                                    </a>
                                </template>
                                <template slot="dropdown">
                                    <ul slot="action-items" role="menu">
                                        <li><a href="#">Master Card</a></li>
                                        <li><a href="#">Paypal Account</a></li>
                                        <li><a href="#">Skrill</a></li>
                                        <li><a href="#">Payza Account</a></li>
                                        <li><a href="#">Master Card</a></li>
                                    </ul>
                                </template>
                            </dropdown>

                        </div>
                        <div class="wperp-panel-footer mt-50">
                            <div class="bank-accounts-total">
                                <span class="title">Total Balance</span>
                                <span class="price">$165,290.00</span>
                            </div>
                        </div>
                    </div>
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

    export default {
        name: 'Dashboard',

        components: {
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
                isEditSettingsEnabled: false
            }
        },

        mounted: function () {
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
            window.addEventListener('load', () => {
                let bar_chart_ctx = this.$refs['bar_chart'].getContext("2d");
                if( bar_chart_ctx !== null || bar_chart_ctx !== undefined ){
                    new Chart(bar_chart_ctx, config);
                }
            });

        },

        computed: {
            function(){
                
            }
        },

    }
</script>

<style scoped>
    
</style>
