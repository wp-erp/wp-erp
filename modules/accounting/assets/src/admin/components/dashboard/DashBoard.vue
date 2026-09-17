<template>
    <div class="wperp-containers">

        <!-- Start .header-section -->
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Dashboard', 'erp') }}</h2>
                    <a class="wperp-btn btn--primary" href="#" @click.prevent="showTutorial = true" id="btn-tutorial-start">
                        <span class="dashicons dashicons-controls-play"></span>
                        {{ __(' Start Tutorial', 'erp') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- End .header-section -->

        <!-- Tutorial Video Modal -->
        <div class="erp-tutorial-modal" v-if="showTutorial">
            <div class="erp-tutorial-modal-overlay" @click="showTutorial = false"></div>
            <div class="erp-tutorial-modal-content">
                <div class="erp-tutorial-modal-header">
                    <h3>{{ __('Accounting Tutorial', 'erp') }}</h3>
                    <a href="#" class="erp-tutorial-modal-close" @click.prevent="showTutorial = false">&times;</a>
                </div>
                <div class="erp-tutorial-modal-body">
                    <iframe
                        width="100%"
                        height="450"
                        :src="tutorialVideoUrl"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>

        <div class="wperp-dashboard">
            <div class="wperp-row">
                <div class="wperp-col-md-9 wperp-col-sm-12 wperp-col-xs-12">
                    <!-- Start .income-expense-section -->
                    <chart></chart>
                    <!-- End .income-expense-section -->
                </div>
                <div class="wperp-col-md-3 wperp-col-sm-12 wperp-col-xs-12">
                    <!-- Start .bank-accounts-section -->
                    <accounts></accounts>
                    <!-- End .bank-accounts-section -->
                </div>
            </div>

            <div class="wperp-row">
                    <div class="wperp-col-sm-6 wperp-col-xs-12" >
                        <!-- Start .invoice-own-section -->
                        <div class="invoice-own-section wperp-panel wperp-panel-default">
                            <div class="wperp-panel-heading wperp-bg-white">
                                <h4>{{ __('Invoice payable to you', 'erp') }}</h4>
                            </div>
                            <div class="wperp-panel-body pb-0">
                                <ul class="wperp-list-unstyled list-table-content" v-if="Object.values(to_receive).length">
                                    <li>
                                        <span class="title">{{ __('1-30 days overdue', 'erp') }}</span>
                                        <span class="price">{{formatAmount(to_receive.amount.first)}}</span>
                                    </li>
                                    <li>
                                        <span class="title">{{ __('31-60 days overdue', 'erp') }}</span>
                                        <span class="price">{{formatAmount(to_receive.amount.second)}}</span>
                                    </li>
                                    <li>
                                        <span class="title">{{ __('61-90 days overdue', 'erp') }}</span>
                                        <span class="price">{{formatAmount(to_receive.amount.third)}}</span>
                                    </li>
                                    <li class="total">
                                        <span class="title">{{ __('Total Balance', 'erp') }}</span>
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
                            <div class="wperp-panel-heading wperp-bg-white">
                                <h4>{{ __('Bills you need to pay', 'erp') }}</h4>
                            </div>
                            <div class="wperp-panel-body pb-0">
                                <ul class="wperp-list-unstyled list-table-content"  v-if="Object.values(to_pay).length">
                                    <li>
                                        <span class="title">{{ __('1-30 days overdue', 'erp') }}</span>
                                        <span class="price">{{formatAmount(to_pay.amount.first)}}</span>
                                    </li>
                                    <li>
                                        <span class="title">{{ __('31-60 days overdue', 'erp') }}</span>
                                        <span class="price">{{formatAmount(to_pay.amount.second)}}</span>
                                    </li>
                                    <li>
                                        <span class="title">{{ __('61-90 days overdue', 'erp') }}</span>
                                        <span class="price">{{formatAmount(to_pay.amount.third)}}</span>
                                    </li>
                                    <li class="total">
                                        <span class="title">{{ __('Total Balance', 'erp') }}</span>
                                        <span class="price">{{total_payable}}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- End .invoice-own-section -->
                    </div>
                </div>

        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import Accounts from 'admin/components/dashboard/Accounts.vue';
import Chart from 'admin/components/dashboard/Chart.vue';

export default {
    name: 'Dashboard',

    components: {
        Chart,
        Accounts
    },

    data() {
        return {
            title1        : __('Income & Expenses', 'erp'),
            title2        : __('Bank Accounts', 'erp'),
            title3        : __('Invoices owed to you', 'erp'),
            title4        : __('Bills to pay', 'erp'),
            closable      : true,
            msg           : __('Accounting', 'erp'),
            to_receive      : [],
            to_pay          : [],
            showTutorial    : false,
            tutorialVideoUrl: erp_acct_var.erp_acct_tut_url
        };
    },

    computed: {
        total_receivable() {
            const amounts = Object.values(this.to_receive.amount);
            const total = amounts.reduce((amount, item) => {
                return amount + parseFloat(item);
            }, 0);

            return this.formatAmount(total);
        },

        total_payable() {
            const amounts = Object.values(this.to_pay.amount);
            const total = amounts.reduce((amount, item) => {
                return amount + parseFloat(item);
            }, 0);
            this.$store.dispatch('spinner/setSpinner', false);
            return this.formatAmount(total);
        }
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.fetchReceivables();
        this.fetchPayables();
    },

    methods: {
        fetchReceivables() {
            this.to_receive = [];
            HTTP.get('invoices/overview-receivable').then((res) => {
                this.to_receive = res.data;
            });
        },

        fetchPayables() {
            this.to_pay = [];
            HTTP.get('bills/overview-payable').then((res) => {
                this.to_pay = res.data;
            });
        }
    }
};
</script>

<style scoped>
.erp-tutorial-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.erp-tutorial-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}
.erp-tutorial-modal-content {
    position: relative;
    background: #fff;
    border-radius: 6px;
    width: 90%;
    max-width: 850px;
    z-index: 10000;
    overflow: hidden;
}
.erp-tutorial-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid #e5e5e5;
}
.erp-tutorial-modal-header h3 {
    margin: 0;
    font-size: 16px;
}
.erp-tutorial-modal-close {
    font-size: 24px;
    text-decoration: none;
    color: #666;
    line-height: 1;
}
.erp-tutorial-modal-close:hover {
    color: #000;
}
.erp-tutorial-modal-body {
    padding: 0;
}
.erp-tutorial-modal-body iframe {
    display: block;
}
</style>
