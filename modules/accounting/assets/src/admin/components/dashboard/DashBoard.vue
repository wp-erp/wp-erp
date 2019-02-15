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
                    <chart></chart>
                    <!-- End .income-expense-section -->

                    <div class="wperp-row">
                        <div class="wperp-col-sm-6 wperp-col-xs-12" >
                            <!-- Start .invoice-own-section -->
                            <div class="invoice-own-section wperp-panel wperp-panel-default">
                                <div class="wperp-panel-heading wperp-bg-white"><h4>Invoice payable to you</h4></div>
                                <div class="wperp-panel-body pb-0">
                                    <ul class="wperp-list-unstyled list-table-content" v-if="Object.values(to_receive).length">
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">{{formatAmount(to_receive.amount.first)}}</span>
                                        </li>
                                        <li>
                                            <span class="title">31-60 days overdue</span>
                                            <span class="price">{{formatAmount(to_receive.amount.second)}}</span>
                                        </li>
                                        <li>
                                            <span class="title">61-90 days overdue</span>
                                            <span class="price">{{formatAmount(to_receive.amount.third)}}</span>
                                        </li>
                                        <li class="total">
                                            <span class="title">Total Balance</span>
                                            <span class="price">{{total_receivable}}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- End .invoice-own-section -->
                        </div>
                        <div class="wperp-col-sm-6 wperp-col-xs-12 ">
                            <!-- Start .invoice-own-section -->
                            <div class="invoice-own-section wperp-panel wperp-panel-default">
                                <div class="wperp-panel-heading wperp-bg-white"><h4>Bills you need to pay</h4></div>
                                <div class="wperp-panel-body pb-0">
                                    <ul class="wperp-list-unstyled list-table-content"  v-if="Object.values(to_pay).length">
                                        <li>
                                            <span class="title">1-30 days overdue</span>
                                            <span class="price">{{formatAmount(to_pay.amount.first)}}</span>
                                        </li>
                                        <li>
                                            <span class="title">31-60 days overdue</span>
                                            <span class="price">{{formatAmount(to_pay.amount.second)}}</span>
                                        </li>
                                        <li>
                                            <span class="title">61-90 days overdue</span>
                                            <span class="price">{{formatAmount(to_pay.amount.third)}}</span>
                                        </li>
                                        <li class="total">
                                            <span class="title">Total Balance</span>
                                            <span class="price">{{total_payable}}</span>
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

    import Dropdown from 'admin/components/base/Dropdown.vue'
    import MetaBox from 'admin/components/wp/MetaBox.vue'
    import Accounts from 'admin/components/dashboard/Accounts.vue'
    import HTTP from 'admin/http';
    import Chart from "admin/components/dashboard/Chart.vue";

    export default {
        name: 'Dashboard',

        components: {
            Chart,
            Accounts,
            MetaBox,
            Dropdown,
            HTTP
        },

        data () {
            return {
                title1: 'Income & Expenses',
                title2: 'Bank Accounts',
                title3: 'Invoices owed to you',
                title4: 'Bills to pay',
                closable: true,
                msg: 'Accounting',

                to_receive: [],
                to_pay: []
            }
        },

        mounted () {

        },

       created(){
            this.fetchReceivables();
            this.fetchPayables();
       },

        computed: {
            total_receivable() {
                let amounts = Object.values(this.to_receive.amount);
                let total = amounts.reduce( ( amount, item ) => {
                    return amount + parseFloat(item);
                }, 0 );

                return this.formatAmount(total);
            },
            total_payable() {
                let amounts = Object.values(this.to_pay.amount);
                let total = amounts.reduce( ( amount, item ) => {
                    return amount + parseFloat(item);
                }, 0 );

                return this.formatAmount(total);
            },
        },

        methods: {
            fetchReceivables(){
                this.to_receive = [];
                HTTP.get( 'invoices/overview-receivable' ).then( (res) => {
                   this.to_receive = res.data;
                } );
            },

            fetchPayables(){
                this.to_pay = [];
                HTTP.get( 'bills/overview-payable' ).then( (res) => {
                    this.to_pay = res.data;
                } );
            }

        }

    }
</script>

<style scoped>

</style>
