<template>
    <div class="wperp-container">
        <!-- Start .header-section -->
        <UserBasicInfo :userData="resData"></UserBasicInfo>
        <!-- End .header-section -->
        <div class="wperp-stats wperp-section">
            <div class="wperp-panel wperp-panel-default">
                <div class="wperp-panel-body">
                    <div class="wperp-row">
                        <div class="wperp-col-sm-4">
                            <pie-chart v-if="paymentData.length"
                                id="payment"
                                :title="paymentChart.title"
                                :labels="paymentChart.labels"
                                :colors="paymentChart.colors"
                                :data="paymentData"/>
                        </div>
                        <div class="wperp-col-sm-4">
                            <pie-chart v-if="statusData.length"
                                id="status"
                                :title="statusChart.title"
                                :labels="statusLabel"
                                :colors="statusChart.colors"
                                :data="statusData"/>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-chart-block">
                                <h3>{{ __('Outstanding', 'erp') }}</h3>
                                <div class="wperp-total"><h2>{{ moneyFormat( outstanding ) }}</h2></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <people-transaction :rows.sync="transactions"></people-transaction>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import UserBasicInfo from 'admin/components/userinfo/UserBasic.vue';
import PieChart from 'admin/components/chart/PieChart.vue';
import PeopleTransaction from 'admin/components/people/PeopleTransaction.vue';

export default {
    name: 'PeopleDetails',
    components: {
        UserBasicInfo,
        PieChart,
        PeopleTransaction
    },

    data() {
        return {
            userId : '',
            resData: {},
            userData : {
                id: '',
                name: '-',
                email: '-',
                // 'img_url': erp_acct_var.acct_assets  + '/images/dummy-user.png',
                meta: {
                    company: '-',
                    website: '-',
                    phone: '-',
                    mobile: '-',
                    address: '-'
                }
            },
            url: '',
            paymentChart: {
                title: 'Payment',
                labels: ['Recieved', 'Outstanding'],
                colors: ['#55D8FE', '#FF8373']
            },
            statusChart: {
                title: 'Status',
                colors: ['#208DF8', '#E9485E']
            },

            paymentData: [],
            statusLabel: [],
            statusData: [],
            transactions: [],
            opening_balance: 0,
            people_balance: 0,
            outstanding: 0,
            temp: null,
            req_url: ''
        };
    },

    created() {
        this.url = this.$route.params.route;
        this.userId = this.$route.params.id;
        if (typeof this.url === 'undefined') {
            this.url = this.$route.path.split('/')[1];
        }
        this.fetchItem(this.userId);
        this.getTransactions();
        this.getChartData();
        this.$root.$on('people-transaction-filter', filter => {
            this.filterTransaction(filter);
        });
    },

    watch: {
        transactions(newVal) {
            this.transactions = newVal;
        }
    },

    methods: {
        fetchItem(id) {
            if (this.$route.name === 'VendorDetails') {
                this.req_url = 'vendors';
            } else if (this.$route.name === 'CustomerDetails') {
                this.req_url = 'customers';
            }

            HTTP.get(this.req_url + '/' + id, {
                params: {}
            }).then((response) => {
                console.log(response.data);

                this.resData = response.data;
            });
        },

        getTransactions() {
            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get(this.req_url + '/' + this.userId + '/transactions').then(res => {
                this.transactions = res.data;

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        filterTransaction(filters = {}) {
            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get(this.url + '/' + this.userId + '/transactions/filter', {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(res => {
                this.transactions = res.data;
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        formatLineItems() {
            this.transactions.forEach(line => {
                if (line.balance === null && typeof line.balance === 'object') {
                    line.balance = 0;
                }
                line.type = this.formatTrnStatus(line.type);
            });
        },

        getChartData(filters = {}) {
            HTTP.get(`/transactions/people-chart/trn-amount/${this.userId}`, {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(response => {
                this.outstanding = response.data.payable;
                this.paymentData.push(
                    response.data.paid,
                    response.data.payable
                );
            });

            HTTP.get(`/transactions/people-chart/trn-status/${this.userId}`, {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(response => {
                this.temp = response.data;
                response.data.forEach(element => {
                    this.statusLabel.push(element.type_name);
                    this.statusData.push(element.sub_total);
                });
            });
        }
    }
};
</script>

<style lang="less">

</style>
