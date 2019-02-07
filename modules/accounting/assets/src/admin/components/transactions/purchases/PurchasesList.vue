<template>

    <div class="wperp-transactions-section wperp-section">

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>Trash</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>

            <list-table
                tableClass="wperp-table table-striped table-dark widefat table2 transactions-table"
                action-column="actions"
                :columns="columns"
                :rows="rows"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                @action:click="onActionClick">
                <template slot="trn_no" slot-scope="data">
                    <strong v-if="isPayment(data.row)">
                        <router-link :to="{ name: 'PayPurchaseSingle', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                    <strong v-else>
                        <router-link :to="{ name: 'PurchaseSingle', params: { id: data.row.id }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <template slot="type" slot-scope="data">
                    {{ isPayment(data.row) ? 'Pay Purchase' : 'Purchase Order' }}
                </template>
                <template slot="ref" slot-scope="data">
                    {{ data.row.ref ? data.row.ref : '-' }}
                </template>
                <template slot="customer_name" slot-scope="data">
                    {{ isPayment(data.row) ? data.row.pay_bill_vendor_name : data.row.vendor_name }}
                </template>
                <template slot="trn_date" slot-scope="data">
                    {{ isPayment(data.row) ? data.row.pay_bill_trn_date : data.row.bill_trn_date }}
                </template>
                <template slot="due_date" slot-scope="data">
                    {{ isPayment(data.row) ? '-' : data.row.due_date }}
                </template>
                <template slot="due" slot-scope="data">
                    {{ isPayment(data.row) ? '-' : formatAmount(data.row.due) }}
                </template>
                <template slot="amount" slot-scope="data">
                    {{ isPayment(data.row) ? formatAmount(data.row.pay_bill_amount) : formatAmount(data.row.amount) }}
                </template>
                <template slot="status" slot-scope="data">
                    {{ isPayment(data.row) ? 'Paid' : data.row.status }}
                </template>

            </list-table>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import ListTable from 'admin/components/list-table/ListTable.vue';

    export default {
        name: 'PurchaseList',

        components: {
            ListTable,
        },

        data() {
            return {
                columns: {
                    'trn_no':        {label: 'Voucher No.'},
                    'type':          {label: 'Type'},
                    'ref':           {label: 'Ref'},
                    'customer_name': {label: 'Customer'},
                    'trn_date':      {label: 'Trn Date'},
                    'due_date':      {label: 'Due Date'},
                    'due':           {label: 'Due'},
                    'amount':        {label: 'Total'},
                    'status':        {label: 'Status'},
                    'actions':       {label: ''},

                },
                rows: [],
                paginationData: {
                    totalItems: 0,
                    totalPages: 0,
                    perPage: 10,
                    currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
                },
                actions : [
                    { key: 'trash', label: 'Delete' }
                ]
            };
        },

        created() {
            this.$root.$on('transactions-filter', filters => {
                this.$router.push({ path: '/transactions/purchases', query: { start: filters.start_date, end: filters.end_date } });
                this.fetchItems(filters);
            });

            let filters = {};

            // Get start & end date from url on page load
            if ( this.$route.query.start && this.$route.query.end ) {
                filters.start_date = this.$route.query.start;
                filters.end_date = this.$route.query.end;
            }

            this.fetchItems(filters);
        },

        watch: {
            '$route': 'fetchItems'
        },

        methods: {
            fetchItems(filters = {}) {
                this.rows = [];

                HTTP.get('/transactions/purchases', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                        start_date: filters.start_date,
                        end_date: filters.end_date
                    }
                }).then( (response) => {
                    this.rows = response.data;

                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                })
                    .catch((error) => {
                        console.log(error);
                    })
                    .then( () => {
                        //ready
                    } );
            },

            onActionClick(action, row, index) {

                switch ( action ) {
                    case 'trash':
                        if ( confirm('Are you sure to delete?') ) {
                            // HTTP.delete('purchases/' + row.id).then( response => {
                            //     this.$delete(this.rows, index);
                            // });
                        }
                        break;

                    case 'edit':
                        //TODO
                        break;

                    default :

                }
            },

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
                this.paginationData.currentPage = page;
                this.$router.push({
                    name: 'PaginateInvoices',
                    params: { page: page },
                    query: queries
                });

                this.fetchItems();
            },

            isPayment(row) {
                return row.type === 'pay_purchase' ? true : false;
            }
        },

    }
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

