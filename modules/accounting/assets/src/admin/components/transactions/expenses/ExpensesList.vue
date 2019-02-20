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
                :rows="row_items"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                @action:click="onActionClick">
                <template slot="trn_no" slot-scope="data">
                    <strong>
                        <router-link :to="data.row.singleView">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>

            </list-table>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import ListTable from 'admin/components/list-table/ListTable.vue';

    export default {
        name: 'ExpensesList',

        components: {
            ListTable, HTTP
        },

        data() {
            return {
                columns: {
                    'trn_no'     : {label: 'Voucher No.'},
                    'type'       : {label: 'Type'},
                    'ref'        : {label: 'Ref'},
                    'vendor_name': {label: 'Vendor'},
                    'trn_date'   : {label: 'Trn Date'},
                    'due_date'   : {label: 'Due Date'},
                    'due'        : {label: 'Due'},
                    'amount'     : {label: 'Total'},
                    'status'     : {label: 'Status'},
                    'actions'    : {label: ''},
                },
                rows: [],
                paginationData: {
                    totalItems : 0,
                    totalPages : 0,
                    perPage    : 10,
                    currentPage: this.$route.params.page === undefined ? 1: parseInt(this.$route.params.page)
                },
                actions : [
                    { key: 'edit', label: 'Edit' },
                    { key: 'trash', label: 'Delete' }
                ]
            };
        },

        created() {
            this.$root.$on('transactions-filter', filters => {
                this.$router.push({ path: '/transactions/sales', query: { start: filters.start_date, end: filters.end_date } });
                this.fetchItems(filters);
            });

            let filters = {};

            // Get start & end date from url on page load
            if ( this.$route.query.start && this.$route.query.end ) {
                filters.start_date = this.$route.query.start;
                filters.end_date   = this.$route.query.end;
            }

            this.fetchItems(filters);
        },

        watch: {
            '$route': 'fetchItems'
        },

        methods: {
            fetchItems(filters = {}) {
                this.rows = [];

                HTTP.get('/transactions/expenses', {
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
                }).catch((error) => {
                        console.log(error);
                });
            },

            onActionClick(action, row, index) {

                switch ( action ) {
                    case 'trash':
                        if ( confirm('Are you sure to delete?') ) {
                            HTTP.delete('invoices/' + row.id).then( response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        if ( 'Expense' == row.type ) {
                            this.$router.push({ name: 'ExpenseEdit', params: { id: row.id } })
                        }

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
        },

        computed: {
            row_items() {

                if (!this.rows.length) {
                    return this.rows;
                }
                let temp;
                let items = this.rows.map( item => {
                    switch ( item.type ){
                        case 'pay_bill':
                            temp = {
                                'id'         : item.id,
                                'trn_no'     : item.id,
                                'type'       : 'Pay Bill',
                                'ref'        : item.ref ? item.ref : '-',
                                'vendor_name': item.pay_bill_vendor_name,
                                'trn_date'   : item.pay_bill_trn_date,
                                'due_date'   : '-',
                                'due'        : '-',
                                'amount'     : this.formatAmount(item.pay_bill_amount),
                                'status'     : 'Paid',
                                'singleView' : { name: 'PayBillSingle', params: { id: item.id }}
                            };
                            break;

                        case 'bill':
                            temp = {
                                'id'         : item.id,
                                'trn_no'     : item.id,
                                'type'       : 'Bill',
                                'ref'        : item.ref ? item.ref : '-',
                                'vendor_name': item.vendor_name,
                                'trn_date'   : item.bill_trn_date,
                                'due_date'   : item.due_date,
                                'due'        : this.formatAmount(item.due),
                                'amount'     : this.formatAmount(item.amount),
                                'status'     : item.status,
                                'singleView' : { name: 'BillSingle', params: { id: item.id }}
                            };
                            break;

                        case 'expense':
                            temp = {
                                'id'         : item.id,
                                'trn_no'     : item.id,
                                'type'       : 'Expense',
                                'ref'        : item.ref ? item.ref : '-',
                                'vendor_name': item.expense_people_name,
                                'trn_date'   : item.expense_people_name,
                                'due_date'   : '-',
                                'due'        : '-',
                                'amount'     : this.formatAmount(item.expense_amount),
                                'status'     : 'Paid',
                                'singleView' : { name: 'ExpenseSingle', params: { id: item.id }}
                            };
                            break;

                        case 'check':
                            temp = {
                                'id'         : item.id,
                                'trn_no'     : item.id,
                                'type'       : 'Check',
                                'ref'        : item.ref ? item.ref : '-',
                                'vendor_name': item.expense_people_name,
                                'trn_date'   : item.expense_people_name,
                                'due_date'   : '-',
                                'due'        : '-',
                                'amount'     : this.formatAmount(item.expense_amount),
                                'status'     : 'Paid',
                                'singleView' : { name: 'CheckSingle', params: { id: item.id }}
                            };
                            break;

                        default :
                            break;

                    }

                    return temp;
                } );

                return items;
            }
        }

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

