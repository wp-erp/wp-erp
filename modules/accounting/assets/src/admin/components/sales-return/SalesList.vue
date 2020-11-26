<template>


    <div class="wperp-transactions-section wperp-section">

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>{{ __('Trash', 'erp') }}</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>


            <h4>{{ __('Sales Return  List', 'erp') }}</h4>
            <transactions-filter  />

            <list-table
                :loading="listLoading"
                tableClass="wperp-table table-striped table-dark widefat table2 transactions-table"
                action-column="actions"
                :columns="columns"
                :rows="rows"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="[]"
                @action:click="onActionClick">
                <template slot="trn_no" slot-scope="data">
                    <strong v-if="isPayment(data.row)">
                        <router-link :to="{ name: 'PayPurchaseSingle', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                    <strong v-else>
                        <router-link :to="{ name: 'SalesReturnDetails', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <template slot="type" slot-scope="data">
                   Sales Return
                </template>
                <template slot="customer_name" slot-scope="data">
                    {{   data.row.customer_name }}
                </template>
                <template slot="trn_date" slot-scope="data">
                    {{ data.row.trn_date }}
                </template>
                <template slot="amount" slot-scope="data">
                    {{ formatAmount(data.row.amount) }}
                </template>
                <template slot="status" slot-scope="data">
                    {{ data.row.status }}
                </template>

                <!-- custom row actions -->
                <template slot="action-list" slot-scope="data">
                    <li v-for="(action, index) in data.row.actions" :key="action.key" :class="action.key">
                        <a href="#" @click.prevent="onActionClick(action.key, data.row, index)">
                            <i :class="action.iconClass"></i>{{ action.label }}
                        </a>
                    </li>
                </template>

            </list-table>

        </div>
    </div>
</template>

<script>

import ListTable from 'admin/components/list-table/ListTable.vue';
import TransactionsFilter from 'admin/components/transactions/TransactionsFilter.vue';
import {mapState} from "vuex";

export default {
    name: 'PurchaseList',

    components: {
        ListTable,
        TransactionsFilter
    },

    data() {
        return {
            columns       : {
                trn_no       : { label:  __('Voucher No.', 'erp') },
                type         : { label: __('Type', 'erp') },
                customer_name: { label: __('Customer', 'erp') },
                trn_date     : { label: __('Trn Date', 'erp') },
                amount       : { label: __('Total', 'erp') },
                status       : { label: __('Status', 'erp') },
                actions      : { label: '' }
            },
            listLoading   : false,
            rows          : [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions       : [],
            fetched       : false,
        };
    },
    computed: mapState({
        vendors: state => state.purchase.vendors
    }),
    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.$root.$on('transactions-filter', filters => {
          /*  this.$router.push({
                path : '/transactions/purchases',
                query: { start: filters.start_date, end: filters.end_date, status: filters.status }
            });
            */
            this.fetchItems(filters);
            this.fetched = true;
        });

        const filters = {};

        // Get start & end date from url on page load
        if (this.$route.query.start && this.$route.query.end) {
            filters.start_date = this.$route.query.start;
            filters.end_date   = this.$route.query.end;
        }
        if (this.$route.query.status) {
            filters.status = this.$route.query.status;
        }

        if (!this.fetched) {
            this.fetchItems(filters);
        }
    },

    methods: {

        async fetchItems(filters = {}) {
            this.rows = [];
            let data =  {
                per_page  : this.paginationData.perPage,
                page      : this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                start_date: filters.start_date,
                end_date  : filters.end_date,
                status    : filters.status,
                type    : filters.type,
                vendor_id: filters.people_id
            } ;

            let returnList = await getRequest('/sales-return/list', data ) ;
            if(returnList){
                this.rows = returnList;

                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.listLoading = false;
            }

        },

        onActionClick(action, row, index) {

        },

        goToPage(page) {

        },

        isPayment(row) {
            return row.type === 'pay_purchase';
        },

        getTrnType(row) {
            if (row.type === 'purchase') {
                if (row.purchase_order === '1') {
                    return 'Purchase Order';
                }
                return 'Purchase';
            } else {
                return 'Pay Purchase';
            }
        }
    }

};
</script>

<style lang="less">
    .transactions-table {
        .tablenav,
        .column-cb,
        .check-column {
            display: none;
        }
    }
</style>
