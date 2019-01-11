<template>

    <div class="wperp-transactions-section wperp-section">
        <sales-report
            v-if="salesReportModal"
            :id="modalParams.voucher_no"
            :type="modalParams.type"
            :totalDue="modalParams.due"
            :totalAmount="modalParams.amount" />

        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>Trash</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>

            <list-table
                tableClass="wperp-table table-striped table-dark widefat table2"
                action-column="actions"
                :columns="columns"
                :rows="rows"
                :bulk-actions="bulkActions"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                @action:click="onActionClick"
                @bulk:click="onBulkAction">
                <template slot="trn_date" slot-scope="data">
                    <strong>
                        <!-- <router-link :to="{ name: 'user', params: { id:  }}">{{ data.row.trn_date }}</router-link> -->

                        <a href="#" @click.prevent="showSalesReportModal(data.row)">
                            {{ data.row.trn_date }}
                        </a>
                    </strong>
                </template>
            </list-table>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import ListTable from 'admin/components/list-table/ListTable.vue';
    import SalesReport from 'admin/components/reports/SalesReport.vue';

    export default {
        name: 'SalesList',

        components: {
            ListTable,
            SalesReport
        },

        data() {
            return {
                salesReportModal: false,
                modalParams: null,
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png'
                    }
                ],
                columns: {
                    'trn_date':      {label: 'Date'},
                    'type':          {label: 'Type'},
                    'ref':           {label: 'Ref'},
                    'customer_name': {label: 'Customer'},
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
                    { key: 'edit', label: 'Edit' },
                    { key: 'trash', label: 'Delete' }
                ]
            };
        },

        created() {
            this.$root.$on('sales-filter', filters => {
                this.fetchItems(filters);
            });

            this.$root.$on('sales-modal-close', () => {
                this.salesReportModal = false;
            });

            this.fetchItems();
        },

        methods: {
            fetchItems(filters = {}) {
                this.rows = [];

                HTTP.get('invoices', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                        start_date: filters.start_date,
                        end_date: filters.end_date
                    }
                }).then( (response) => {
                    response.data.forEach(element => {
                        element['type'] = 'Invoice';
                        this.rows.push(element);
                    });

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
                            HTTP.delete('invoices/' + row.id).then( response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        //TODO
                        break;

                    default :

                }
            },

            onBulkAction(action, items) {
                if ( 'trash' === action ) {
                    if ( confirm('Are you sure to delete?') ) {
                        HTTP.delete(`invoices/delete/${items.join(',')}`).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if ( toggleCheckbox.checked ) {
                                // simulate click event to remove checked state
                                toggleCheckbox.click();
                            }

                            this.fetchItems();
                        });
                    }
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

            showSalesReportModal(row) {
                this.modalParams = row;

                this.salesReportModal = true;
            }
        },

    }
</script>
