<template>

    <div class="wperp-transactions-section wperp-section">
        <!-- Start .wperp-crm-table -->
        <div class="table-container">
            <div class="bulk-action">
                <a href="#"><i class="flaticon-trash"></i>Trash</a>
                <a href="#" class="dismiss-bulk-action"><i class="flaticon-close"></i></a>
            </div>
            <!-- <table class="wperp-table table-striped table-dark widefat table2">
                <thead>
                    <tr>
                        <td scope="col" class="col--check">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input id="cb-select-all" class="form-check-input" type="checkbox" value="">
                                    <span class="form-check-sign">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </td>
                        <th scope="col" class="col--name column-primary">Date</th>
                        <th scope="col" class="col--email">Type</th>
                        <th scope="col" class="col--phone">Ref</th>
                        <th scope="col" class="col--life-stage">Customer</th>
                        <th scope="col" class="col--owner">Due Date</th>
                        <th scope="col" class="col--cd">Due</th>
                        <th scope="col" class="col--total">Total</th>
                        <th scope="col" class="col--status">Status</th>
                        <th scope="col" class="col--actions"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row" class="col--check">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox">
                                    <span class="form-check-sign">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                        </th>
                        <td class="col--name column-primary">26-09-18
                            <button type="button" class="wperp-toggle-row">
                                <span class="screen-reader-text">Show more details</span>
                            </button>
                        </td>
                        <td class="col--email" data-colname="Type">Invoice</td>
                        <td class="col--phone" data-colname="Ref">012</td>
                        <td class="col--life-stage subscriber" data-colname="Customer">Rizvi Amin khan</td>
                        <td class="col--owner" data-colname="Due Date">06-09-2018</td>
                        <td class="col--cd" data-colname="Due">$24,000.00</td>
                        <td class="col--cd" data-colname="Total">$24,000.00</td>
                        <td class="col--cd" data-colname="Status">Recieved</td>
                        <td class="col--actions">
                            <div class="wperp-has-dropdown dropdown right--middle">
                                <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#"><i class="flaticon-printer"></i>Print</a></li>
                                    <li><a href="#"><i class="flaticon-sent-mail"></i>Send</a></li>
                                    <li><a href="#"><i class="flaticon-alarm-clock"></i>Send Reminder</a></li>
                                    <li><a href="#"><i class="flaticon-share"></i>Share Invoice</a></li>
                                    <li><a href="#"><i class="flaticon-link-symbol"></i>Print Packing Link</a></li>
                                    <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                    <li><a href="#"><i class="flaticon-copy-content"></i>Copy</a></li>
                                    <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                    <li><a href="#"><i class="flaticon-delete"></i>Void</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="col--check">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox">
                                    <span class="form-check-sign">
                                    <span class="check"></span>
                                </span>
                                </label>
                            </div>
                        </th>
                        <td class="col--name column-primary">26-09-18 
                            <button type="button" class="wperp-toggle-row">
                                <span class="screen-reader-text">Show more details</span>
                            </button>
                        </td>
                        <td class="col--email" data-colname="Email Address">Invoice</td>
                        <td class="col--phone" data-colname="Phone">012</td>
                        <td class="col--life-stage subscriber" data-colname="Life Stage">Rizvi Amin khan</td>
                        <td class="col--owner" data-colname="Owner">06-09-2018</td>
                        <td class="col--cd" data-colname="Created At">$24,000.00</td>
                        <td class="col--cd" data-colname="Created At">$24,000.00</td>
                        <td class="col--cd" data-colname="Created At">Recieved</td>
                        <td class="col--actions">
                            <div class="wperp-has-dropdown dropdown right--middle">
                                <a href="#" class="dropdown-trigger"><i class="flaticon-menu"></i></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#"><i class="flaticon-printer"></i>Print</a></li>
                                    <li><a href="#"><i class="flaticon-sent-mail"></i>Send</a></li>
                                    <li><a href="#"><i class="flaticon-alarm-clock"></i>Send Reminder</a></li>
                                    <li><a href="#"><i class="flaticon-share"></i>Share Invoice</a></li>
                                    <li><a href="#"><i class="flaticon-link-symbol"></i>Print Packing Link</a></li>
                                    <li><a href="#"><i class="flaticon-edit"></i>View/Edit</a></li>
                                    <li><a href="#"><i class="flaticon-copy-content"></i>Copy</a></li>
                                    <li><a href="#"><i class="flaticon-trash"></i>Delete</a></li>
                                    <li><a href="#"><i class="flaticon-delete"></i>Void</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                </tbody>
            </table> -->
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
                <!-- <template slot="title" slot-scope="data">
                    <strong><a href="#">{{ data.row.title }}</a></strong>
                </template> -->
            </list-table>

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http.js'
    import ListTable from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'SalesList',

        components: {
            ListTable
        },

        data() {
            return {
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
                    'amount':         {label: 'Total'},
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
            }
        },

    }
</script>

<style scoped>

</style>
