<template>

    <div class="wperp-transactions-section wperp-section">

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>{{ __('Trash', 'erp') }}</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>


            <h4 class="top-title-bar">{{ __('Purchase Return', 'erp') }}
                <router-link class="wperp-btn btn--primary add-line-trigger pull-right" :to="{ name: 'purchasesReturn' }" > {{ __( "Create Return Invoice ", "erp" ) }}</router-link>
            </h4>
            <transactions-filter :status="false" :people="{title: 'Vendor', items: vendors}"/>

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
                    <strong>
                        <router-link :to="{ name: 'purchasesReturnDetails', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <template slot="type" slot-scope="data">
                   Purchase Return
                </template>
                <template slot="customer_name" slot-scope="data">
                    {{ isPayment(data.row) ? data.row.pay_bill_vendor_name : data.row.vendor_name }}
                </template>
                <template slot="trn_date" slot-scope="data">
                    {{ isPayment(data.row) ? data.row.pay_bill_trn_date : data.row.bill_trn_date }}
                </template>
                <template slot="amount" slot-scope="data">
                    {{ isPayment(data.row) ? formatAmount(data.row.pay_bill_amount) : formatAmount(data.row.amount) }}
                </template>
                <template slot="status" slot-scope="data">
                    {{ __('Returned', 'erp') }}
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
import HTTP from 'admin/http';
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

            filterTypes:[{id: 'purchase', name: 'Purchase'}, {id: 'pay_purchase', name: 'Purchase Payment'}],
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

        if(!this.vendors.length){
            this.$store.dispatch('purchase/fetchVendors');
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

            let returnList = await getRequest('/purchase-return/list', data ) ;
            if(returnList){
                this.rows = returnList;

                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.listLoading = false;
            }

        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm('Are you sure to delete?')) {
                    HTTP.delete('purchases/' + row.id).then(response => {
                        this.$delete(this.rows, index);
                    });
                }
                break;

            case 'edit':
                if (row.type === 'purchase') {
                    this.$router.push({ name: 'PurchaseEdit', params: { id: row.id } });
                }

                break;

            case 'payment':
                if (row.type === 'purchase') {
                    this.$router.push({
                        name  : 'PayPurchaseCreate',
                        params: {
                            vendor_id  : row.vendor_id,
                            vendor_name: row.vendor_name
                        }
                    });
                }
                break;

            case 'void':
                if (confirm('Are you sure to void the transaction?')) {
                    if (row.type === 'purchase') {
                        HTTP.post('purchases/' + row.id + '/void').then(response => {
                            this.showAlert('success', 'Transaction has been void!');
                        }).catch(error => {
                            throw error;
                        });
                    }
                    if (row.type === 'pay_purchase') {
                        HTTP.post('pay-purchases/' + row.id + '/void').then(response => {
                            this.showAlert('success', 'Transaction has been void!');
                        }).then(() => {
                            this.$router.push({ name: 'Purchases' });
                        }).catch(error => {
                            throw error;
                        });
                    }
                }
                break;

            case 'to_purchase':
                this.$router.push({ name: 'PurchaseEdit', params: { id: row.id }, query: { convert: true } });
                break;

            default :
                break;
            }
        },

        goToPage(page) {
            this.listLoading                = true;
            const queries                   = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name  : 'PaginatePurchases',
                params: { page: page },
                query : queries
            });

            this.fetchItems();
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
