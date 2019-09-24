<template>
    <div class="wperp-container">
        <!-- Start .header-section -->
        <!-- <UserBasicInfo :userData="resData"></UserBasicInfo> -->
        <div class="wperp-panel wperp-panel-default mt-20">
            <div class="wperp-panel-body wperp-customer-panel">
            <!-- <people-modal  :people="userData" :title="title" v-if="showModal"></people-modal> -->
            <!-- edit customers info trigger -->
            <!-- <span class="edit-badge" data-toggle="wperp-modal" data-target="wperp-edit-customer-modal">
                <i class="flaticon-edit" @click="showModal = true"></i>
            </span> -->
            <div class="wperp-row">
                <div class="wperp-col-lg-3 wperp-col-md-4 wperp-col-sm-4 wperp-col-xs-12">
                    <div class="customer-identity">
                        <img :src="user.avatar_url" :alt=user.name style="border-radius: 100%">
                        <div class="">
                            <h3>{{user.first_name}}  {{ user.last_name }}</h3>
                            <span>{{user.user_email}}</span>
                        </div>
                    </div>
                </div>
                <div class="wperp-col-lg-9 wperp-col-md-8 wperp-col-sm-8 wperp-col-xs-12">
                    <ul class="customer-meta">
                        <li>
                            <strong>{{ __('Phone', 'erp') }}:</strong>
                            <span>{{ user.phone }}</span>
                        </li>
                        <li>
                            <strong>{{ __('Mobile', 'erp') }}:</strong>
                            <span>{{ user.mobile }}</span>
                        </li>
                        <li>
                            <strong style="margin-right: 10px">{{ __('Department', 'erp') }}:</strong>
                            <span v-if="user.department">{{ user.department }}</span>
                        </li>
                        <li>
                            <strong style="margin-right: 10px">{{ __('Designation', 'erp') }}:</strong>
                            <span v-if="user.designation">{{ user.designation }}</span>
                        </li>
                        <li>
                            <strong>{{ __('Address', 'erp') }}:</strong>
                            <span >{{ user.address }} </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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
        <people-transaction :rows="transactions"></people-transaction>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import PieChart from 'admin/components/chart/PieChart.vue';
import PeopleTransaction from 'admin/components/people/PeopleTransaction.vue';

export default {
    name: 'EmployeeDetails',
    components: {
        PieChart,
        PeopleTransaction
    },

    data() {
        return {
            userId : '',
            user: {},
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

            transactions: [],
            paymentData: [],
            statusLabel: [],
            statusData: [],
            outstanding: 0,
            temp: null
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

    computed: {

    },

    methods: {
        fetchItem(id) {
            HTTP.get('/employees/' + this.userId, {
                params: { include: 'department,designation,reporting_to,avatar' }
            }).then((response) => {
                this.user = response.data;
            });
        },

        getTransactions() {
            HTTP.get('/employees/' + this.userId + '/transactions').then(res => {
                this.transactions = res.data;
            });
        },

        filterTransaction(filters = {}) {
            HTTP.get('/employees/' + this.userId + '/transactions/filter', {
                params: {
                    start_date: filters.start_date,
                    end_date: filters.end_date
                }
            }).then(res => {
                this.transactions = res.data;
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
