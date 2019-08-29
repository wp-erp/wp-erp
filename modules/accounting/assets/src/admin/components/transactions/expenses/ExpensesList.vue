<template>

    <div class="wperp-transactions-section wperp-section">

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>{{ __('Trash', 'erp') }}</a>
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
                :actions="[]"
                @action:click="onActionClick">
                <template slot="trn_no" slot-scope="data">
                    <strong>
                        <router-link :to="data.row.singleView">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <!-- custom row actions -->
                <template slot="action-list" slot-scope="data">
                    <li v-for="(action, index) in data.row.actions" :key="action.key" :class="action.key">
                        <a href="#" @click.prevent="onActionClick(action.key, data.row, index)">
                            <i :class="action.iconClass"></i>{{ action.label }}
                        </a>
                    </li>
                </template>
                <template slot="status" slot-scope="data">
                    {{ data.row.status }}
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
        ListTable
    },

    data() {
        return {
            columns       : {
                trn_no     : {label: 'Voucher No.'},
                type       : {label: 'Type'},
                ref        : {label: 'Ref'},
                vendor_name: {label: 'Vendor'},
                trn_date   : {label: 'Trn Date'},
                due_date   : {label: 'Due Date'},
                due        : {label: 'Due'},
                amount     : {label: 'Total'},
                status     : {label: 'Status'},
                actions    : {label: ''}
            },
            rows          : [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 10,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions       : [],
            fetched       : false
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.$root.$on('transactions-filter', filters => {
            this.$router.push({
                path : '/transactions/expenses',
                query: {start: filters.start_date, end: filters.end_date, status: filters.status}
            });
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

    watch: {
        $route: 'fetchItems'
    },

    methods: {
        fetchItems(filters = {}) {
            this.rows = [];

            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/transactions/expenses', {
                params: {
                    per_page  : this.paginationData.perPage,
                    page      : this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    start_date: filters.start_date,
                    end_date  : filters.end_date,
                    status    : filters.status
                }
            }).then((response) => {
                this.rows = response.data;

                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch((error) => {
                throw error;
            });
        },

        onActionClick(action, row, index) {
            switch (action) {
                case 'trash':
                    // if ( confirm('Are you sure to delete?') ) {
                    //     HTTP.delete('invoices/' + row.id).then( response => {
                    //         this.$delete(this.rows, index);
                    //     });
                    // }
                    break;

                case 'edit':
                    if (row.type === 'Expense') {
                        this.$router.push({name: 'ExpenseEdit', params: {id: row.id}});
                    }

                    if (row.type === 'Bill') {
                        this.$router.push({name: 'BillEdit', params: {id: row.id}});
                    }

                    if (row.type === 'Check') {
                        this.$router.push({name: 'CheckEdit', params: {id: row.id}});
                    }

                    break;

                case 'payment':
                    if (row.type === 'Bill') {
                        this.$router.push({
                            name  : 'PayBillCreate',
                            params: {
                                vendor_id  : row.vendor_id,
                                vendor_name: row.vendor_name
                            }
                        });
                    }
                    break;

                default :
                    break;
            }
        },

        goToPage(page) {
            const queries                   = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name  : 'PaginateExpenses',
                params: {page: page},
                query : queries
            });

            this.fetchItems();
        }
    },

    computed: {
        row_items() {
            if (!this.rows.length) {
                return this.rows;
            }
            if (item.status_code === '1') {
                item['actions'] = [
                    {key: 'edit', label: 'Edit'}
                ];
            } else if (item.status_code === '7') {
                delete item['actions'];
            } else if (item.status_code === '2' || item.status_code === '3' || item.status_code === '5') {
                item['actions'] = [
                    {key: 'edit', label: __('Edit', 'erp')},
                    {key: 'payment', label: __('Make Payment', 'erp')}
                ];
            } else {
                item['actions'] = [
                    {key: '#', label: __('No actions found', 'erp')}
                ];
            }

            return items;
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
