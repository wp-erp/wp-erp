<template>

    <div class="wperp-transactions-section wperp-section">

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>{{ __('Trash', 'erp') }}</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>

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
                :actions="[]"
                @pagination="goToPage"
                @action:click="onActionClick">
                <template slot="trn_no" slot-scope="data">
                    <strong>
                        <router-link :to="{ name: 'SalesSingle', params: {
                            id: data.row.id,
                            type: isPayment(data.row) ? 'payment' : 'invoice'
                        }}">
                            #{{ data.row.id }}
                        </router-link>
                    </strong>
                </template>
                <template slot="type" slot-scope="data">
                    {{ getTrnType(data.row) }}
                </template>
                <template slot="ref" slot-scope="data">
                    {{ data.row.ref ? data.row.ref : '-' }}
                </template>
                <template slot="customer_name" slot-scope="data">
                    {{ isPayment(data.row) ? data.row.pay_cus_name : data.row.inv_cus_name }}
                </template>
                <template slot="trn_date" slot-scope="data">
                    {{ isPayment(data.row) ? data.row.payment_trn_date : data.row.invoice_trn_date }}
                </template>
                <template slot="due_date" slot-scope="data">
                    {{ isPayment(data.row) ? '-' : data.row.due_date }}
                </template>
                <template slot="due" slot-scope="data">
                    {{ isPayment(data.row) ? '-' : formatAmount(data.row.due) }}
                </template>
                <template slot="amount" slot-scope="data">
                    {{ isPayment(data.row) ? formatAmount(data.row.payment_amount) : formatAmount(data.row.sales_amount)
                    }}
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
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';
/* global __ */
export default {
    name: 'SalesList',

    components: {
        ListTable
    },

    data() {
        return {
            columns       : {
                trn_no       : { label: 'Voucher No.' },
                type         : { label: 'Type' },
                ref          : { label: 'Ref' },
                customer_name: { label: 'Customer' },
                trn_date     : { label: 'Trn Date' },
                due_date     : { label: 'Due Date' },
                due          : { label: 'Due' },
                amount       : { label: 'Total' },
                status       : { label: 'Status' },
                actions      : { label: '' }

            },
            listLoading   : false,
            rows          : [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            }
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.$root.$on('transactions-filter', filters => {
            this.$router.push({
                path : '/transactions/sales',
                query: { start: filters.start_date, end: filters.end_date, status: filters.status }
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

    // watch: {
    //     $route: 'fetchItems'
    // },

    methods: {
        fetchItems(filters = {}) {
            this.rows = [];

            this.$store.dispatch('spinner/setSpinner', true);
            HTTP.get('/transactions/sales', {
                params: {
                    per_page  : this.paginationData.perPage,
                    page      : this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    start_date: filters.start_date,
                    end_date  : filters.end_date,
                    status    : filters.status
                }
            }).then(response => {
                this.rows = response.data.map(item => {
                    if (item.estimate === '1' || item.status_code === '1') {
                        item['actions'] = [
                            { key: 'edit', label: 'Edit' },
                            { key: 'to_invoice', label: 'Make Invoice' }
                        ];
                    } else if (item.status_code === '8') {
                        item['actions'] = [
                            { key: '#', label: __('No actions found', 'erp') }
                        ];
                    } else if (item.type === 'invoice' && item.status_code !== '4') {
                        if (item.status_code === '7') {
                            delete item['actions'];
                        } else if (item.status_code === '2' || item.status_code === '3' || item.status_code === '5') {
                            item['actions'] = [
                                { key: 'receive', label: __('Receive Payment', 'erp') },
                                { key: 'edit', label: __('Edit', 'erp') },
                                { key: 'void', label: 'Void' }
                            ];
                        } else {
                            item['actions'] = [
                                { key: 'void', label: 'Void' }
                            ];
                        }
                    } else {
                        item['actions'] = [
                            { key: '#', label: __('No actions found', 'erp') }
                        ];
                    }

                    return item;
                });

                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.listLoading = false;
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.listLoading = false;
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm('Are you sure to delete?')) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete('invoices/' + row.id).then(response => {
                        this.$delete(this.rows, index);

                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', 'Deleted !');
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
                break;

            case 'edit':
                if (row.type === 'invoice') {
                    this.$router.push({ name: 'InvoiceEdit', params: { id: row.id } });
                }

                if (row.type === 'payment') {
                    this.$router.push({ name: 'RecPaymentEdit', params: { id: row.id } });
                }
                break;

            case 'receive':
                this.$router.push({
                    name  : 'RecPaymentCreate',
                    params: {
                        customer_id  : row.inv_cus_id,
                        customer_name: row.inv_cus_name
                    }
                });
                break;

            case 'void':
                if (confirm('Are you sure to void the transaction?')) {
                    if (row.type === 'invoice') {
                        HTTP.post('invoices/' + row.id + '/void').then(response => {
                            this.showAlert('success', 'Transaction has been void!');
                        }).catch(error => {
                            throw error;
                        });
                    }
                    if (row.type === 'payment') {
                        HTTP.post('payments/' + row.id + '/void').then(response => {
                            this.showAlert('success', 'Transaction has been void!');
                        }).then(() => {
                            this.$router.push({ name: 'Sales' });
                        }).catch(error => {
                            throw error;
                        });
                    }
                }
                break;

            case 'to_invoice':
                this.$router.push({ name: 'InvoiceEdit', params: { id: row.id }, query: { convert: true } });
                break;

            default:
                break;
            }
        },

        goToPage(page) {
            this.listLoading                = true;
            const queries                   = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name  : 'PaginateSales',
                params: { page: page },
                query : queries
            });

            this.fetchItems();
        },

        isPayment(row) {
            return row.type === 'payment';
        },

        getTrnType(row) {
            if (row.type === 'invoice') {
                if (row.estimate == '1') {
                    return 'Estimate';
                }
                return 'Invoice';
            } else {
                return 'Payment';
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
