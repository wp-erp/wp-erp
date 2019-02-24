<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Agencies</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="showModal = true">
                        <span>Add Tax Agency</span>
                    </a>
                </div>
            </div>
        </div>

        <new-tax-agency v-if="showModal" @close="showModal = false"></new-tax-agency>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-agencies-list"
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
    import NewTaxAgency  from 'admin/components/tax/NewTaxAgency.vue'

    export default {
        name: 'TaxAgencies',

        components: {
            ListTable,
            NewTaxAgency
        },

        data() {
            return {
                showModal: false,
                modalParams: null,
                columns: {
                    'tax_agency_id': {label: 'ID'},
                    'tax_agency_name': {label: 'Agency Name'},
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
                    { key: 'edit', label: 'Edit', iconClass: 'flaticon-edit' },
                    { key: 'trash', label: 'Delete', iconClass: 'flaticon-trash' }
                ],
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Trash',
                        iconClass: 'flaticon-trash'
                    }
                ],
                tax_agencies: [{}],
                buttonTitle: '',
                pageTitle: '',
                url: '',
                singleUrl: '',
                isActiveOptionDropdown: false
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
            row_data() {
                let items = this.rows;
                items.map( item => {
                    item.tax_agency_id = item.id;
                    item.tax_agency_name = item.name;
                } );
                return items;
            }
        },

        methods: {
            fetchItems() {
                this.rows = [];
                HTTP.get('tax-agencies', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    }
                }).then( (response) => {
                    this.rows = response.data;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                }).catch((error) => {
                    console.log(error);
                }).then(() => {
                    //ready
                });
            },

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
                this.paginationData.currentPage = page;
                this.$router.push({
                    name: 'PaginateTaxAgencies',
                    params: { page: page },
                    query: queries
                });

                this.fetchItems();
            },

            singleTaxAgency(tax_id) {
                this.$router.push({ name: 'SingleTaxAgency', params: { id: tax_id } })
            },

            onActionClick(action, row, index) {
                switch ( action ) {
                    case 'trash':
                        if ( confirm('Are you sure to delete?') ) {
                            HTTP.delete( this.url + '/' + row.id).then( response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        this.showModal = true;
                        this.tax_agencies = row;
                        break;

                    default :
                        break;
                }
            },

            onBulkAction(action, items) {
                if ( 'trash' === action ) {
                    if ( confirm('Are you sure to delete?') ) {
                        HTTP.delete('tax-agencies/delete/' + items.join(',')).then(response => {
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
