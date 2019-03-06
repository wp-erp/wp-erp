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
                            <strong>Phone:</strong>
                            <span>{{ user.phone }}</span>
                        </li>
                        <li>
                            <strong>Mobile:</strong>
                            <span>{{ user.mobile }}</span>
                        </li>
                        <li>
                            <strong style="margin-right: 10px">Department:</strong>
                            <span v-if="user.department">{{ user.department.title }}</span>
                        </li>
                        <li>
                            <strong style="margin-right: 10px">Designation:</strong>
                            <span v-if="user.designation">{{ user.designation.title }}</span>
                        </li>
                        <li>
                            <strong>Address:</strong>
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
                            <pie-chart
                                id="payment"
                                :sign="getCurrencySign()"
                                :title="paymentChart.title"
                                :labels="paymentChart.labels"
                                :colors="paymentChart.colors"
                                :data="paymentChart.data">
                            </pie-chart>
                        </div>
                        <div class="wperp-col-sm-4">
                            <pie-chart
                                id="status"
                                :sign="getCurrencySign()"
                                :title="statusChart.title"
                                :labels="statusChart.labels"
                                :colors="statusChart.colors"
                                :data="statusChart.data">
                            </pie-chart>
                        </div>
                        <div class="wperp-col-sm-4">
                            <div class="wperp-chart-block">
                                <h3>Outstanding</h3>
                                <div class="wperp-total"><h2>$20000,00</h2></div>
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
    import HTTP from 'admin/http'
    import UserBasicInfo from 'admin/components/userinfo/UserBasic.vue'
    import PieChart from 'admin/components/chart/PieChart.vue'
    import PeopleTransaction from 'admin/components/people/PeopleTransaction.vue'

    export default {
        name: 'EmployeeDetails',
        components: {
            UserBasicInfo,
            PieChart,
            PeopleTransaction
        },

        data(){
            return {
                userId : '',
                user: {},
                userData : {
                    'id': '',
                    'name': '************',
                    'email': '************',
                    // 'img_url': erp_acct_var.acct_assets  + '/images/dummy-user.png',
                    'meta': {
                        'company': '**********',
                        'website': '**********',
                        'phone': '**********',
                        'mobile': '*************',
                        'address': '*********',
                    }
                },
                url: '',
                paymentChart: {
                    title: 'Payment',
                    labels: ['Recieved', 'Outstanding'],
                    colors: ['#55D8FE', '#FF8373'],
                    data: [794, 458],
                },
                statusChart: {
                    title: 'Status',
                    labels: ['Paid', 'Overdue', 'Partial', 'Draft'],
                    colors: ['#208DF8', '#E9485E', '#FF9900', '#2DCB67'],
                    data: [2, 1, 2, 3],
                },

                transactions: [],
            }
        },

        created(){
            this.url = this.$route.params.route;
            this.userId = this.$route.params.id;
            if ( typeof this.url === 'undefined' ) {
                this.url = this.$route.path.split('/')[1];
            }
            this.fetchItem( this.userId );
            this.getTransactions();
            this.$root.$on( 'people-transaction-filter', filter => {
                this.filterTransaction( filter );
            } );
        },

        computed: {

        },

        methods: {
            fetchItem( id ) {
                HTTP.get( this.url+'/'+id, {
                    params: { 'include': 'department,designation,reporting_to,avatar' }
                })
                    .then((response) => {
                        this.user = response.data;
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .then(() => {
                        //ready
                    });
            },
            getTransactions() {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get( this.url + '/' + this.userId + '/transactions' ).then( res => {
                    this.transactions = res.data;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            },
            filterTransaction( filters = {} ) {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get('customers/' + this.userId + '/transactions/filter', {
                    params: {
                        start_date: filters.start_date,
                        end_date: filters.end_date
                    }
                } ).then( res => {
                    this.transactions = res.data;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            }
        }
    }
</script>

<style lang="less">

</style>
