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
                        <router-link :to="{ name: 'PurchaseSingle', params: { id: data.row.id }}">
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
    name: 'PurchaseList',

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
            },
            actions       : [],
            fetched       : false
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.$root.$on('transactions-filter', filters => {
            this.$router.push({
                path : '/transactions/purchases',
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
            HTTP.get('/transactions/purchases', {
                params: {
                    per_page  : this.paginationData.perPage,
                    page      : this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    start_date: filters.start_date,
                    end_date  : filters.end_date,
                    status    : filters.status
                }
            }).then((response) => {
                const mappedData = response.data.map(item => {
                    if (item.purchase_order === '1' || item.status_code === '1') {
                        item['actions'] = [
                            { key: 'edit', label: 'Edit' },
                            { key: 'to_purchase', label: 'Make Purchase' }
                        ];
                    } else if (item.status_code === '8') {
                        item['actions'] = [
                            { key: '#', label: __('No actions found', 'erp') }
                        ];
                    } else if (item.type === 'purchase' && item.status_code !== '4') {
                        if (item.status_code === '7') {
                            delete item['actions'];
                        } else if (item.status_code === '2' || item.status_code === '3' || item.status_code === '5') {
                            item['actions'] = [
                                { key: 'payment', label: __('Make Payment', 'erp') },
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

                this.rows = mappedData;

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
