<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Checks</h2>
                </div>
            </div>
        </div>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-records-list wperp-table table-striped table-dark"
                action-column="actions"
                :columns="columns"
                :rows="row_data"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                :bulk-actions="bulkActions"
                @action:click="onActionClick"
                @bulk:click="onBulkAction">
            </list-table>
        </div>

    </div>
</template>

<script>
    import HTTP          from 'admin/http'
    import ListTable     from 'admin/components/list-table/ListTable.vue'

    export default {
        name: 'ChecksList',

        components: {
            ListTable
        },

        data () {
            return {
                trn_no: 0,
                payee_name: '',
                trn_date: '',
                status: '',
                amount: 0,
                modalParams: null,
                columns: {
                    'trn_no': {label: 'Voucher No'},
                    'payee_name': {label: 'Payee'},
                    'trn_date': {label: 'Date'},
                    'amount': {label: 'Amount'},
                    'status': {label: 'Status'},
                    'actions': { label: 'Actions' }
                },
                rows: [],
                paginationData: {
                    totalItems: 0,
                    totalPages: 0,
                    perPage: 10,
                    currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
                },
                actions : [
                    { key: 'accept', label: 'Approve', iconClass: 'flaticon-edit' },
                    { key: 'bounce', label: 'Bounce', iconClass: 'flaticon-trash' }
                ],
                bulkActions: [
                    {
                        key: 'accept',
                        label: 'Approve',
                        iconClass: 'flaticon-edit'
                    },
                    {
                        key: 'bounce',
                        label: 'Bounce',
                        iconClass: 'flaticon-trash'
                    },
                ],
                taxes: [{}],
                buttonTitle: '',
                pageTitle: '',
                url: '',
                singleUrl: '',
                isActiveOptionDropdown: false,
                showModal: false
            };
        },

        created() {
            this.$on('tax-modal-close', function() {
                this.showModal = false;
            });

            this.pageTitle      =   this.$route.name;
            this.url            =   this.$route.name.toLowerCase();

            this.fetchItems();
        },

        computed: {
            row_data(){
                let items = this.rows;
                items.map( item => {
                    item.trn_no = item.trn_no;
                    item.payee_name = item.payee_name;
                    item.trn_date = item.trn_date;
                    item.status = item.status;
                    item.amount = item.amount;
                } );
                return items;
            }
        },

        methods: {
            close() {
                this.showModal = false;
            },
            fetchItems(){
                this.rows = [];
                HTTP.get('checks', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    }
                })
                    .then( (response) => {
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

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
                this.paginationData.currentPage = page;
                this.$router.push({
                    name: 'PaginateChecksList',
                    params: { page: page },
                    query: queries
                });

                this.fetchItems();
            },

            onActionClick(action, row, index) {
                switch ( action ) {
                    case 'accept':
                        if ( confirm('Are you sure to approve the check?') ) {
                            HTTP.post( 'checks' + '/' + row.trn_no + '/approve' ).then( response => {
                                this.$swal({
                                    position: 'center',
                                    type: 'success',
                                    title: 'Check Approved!',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                            });
                        }
                        break;

                    case 'bounce':
                        if ( confirm('Are you sure to bounce the check?') ) {
                            HTTP.post( 'checks' + '/' + row.trn_no + '/bounce' ).then( response => {
                                this.$swal({
                                    position: 'center',
                                    type: 'success',
                                    title: 'Check Bounced!',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                            });
                        }
                        break;

                    default :
                        break;
                }
            },

            onBulkAction(action, items) {
                if ( 'trash' === action ) {
                    if ( confirm('Are you sure to delete?') ) {
                        HTTP.delete('taxes/delete/' + items.join(',')).then(response => {
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
        }
    }
</script>
<style lang="less">

</style>


