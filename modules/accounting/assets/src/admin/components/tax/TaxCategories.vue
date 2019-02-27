<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Categories</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="showModal = true">
                        <span>Add Tax Category</span>
                    </a>
                </div>
            </div>
        </div>

        <new-tax-category v-if="showModal" :cat_id="cat_id" :is_update="is_update" @close="showModal = false"></new-tax-category>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-cats-list"
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
    import HTTP            from 'admin/http'
    import ListTable       from 'admin/components/list-table/ListTable.vue'
    import NewTaxCategory  from 'admin/components/tax/NewTaxCategory.vue'

    export default {
        name: 'TaxCategories',

        components: {
            ListTable,
            NewTaxCategory
        },

        data() {
            return {
                showModal: false,
                modalParams: null,
                columns: {
                    'tax_cat_id': {label: 'ID'},
                    'tax_cat_name': {label: 'Category Name'},
                    'tax_cat_desc': {label: 'Description'},
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
                tax_cats: [{}],
                buttonTitle: '',
                pageTitle: '',
                url: '',
                singleUrl: '',
                isActiveOptionDropdown: false,
                cat_id: null,
                is_update: false
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
                    item.tax_cat_id = item.id;
                    item.tax_cat_name = item.name;
                    item.tax_cat_desc = item.description;
                } );
                return items;
            }
        },

        methods: {
            fetchItems() {
                this.rows = [];
                HTTP.get('tax-cats', {
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
                    name: 'PaginateTaxCategories',
                    params: { page: page },
                    query: queries
                });

                this.fetchItems();
            },


            singleTaxCategory(tax_id) {
                this.$router.push({ name: 'SingleTaxCategory', params: { id: tax_id } })
            },

            onActionClick(action, row, index) {
                switch ( action ) {
                    case 'trash':
                        if ( confirm('Are you sure to delete?') ) {
                            HTTP.delete( 'tax-cats' + '/' + row.id).then( response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        this.showModal = true;
                        this.cat_id = row.id;
                        this.is_update = true;
                        this.fetchItems();
                        break;

                    default :
                        break;
                }
            },

            onBulkAction(action, items) {
                if ( 'trash' === action ) {
                    if ( confirm('Are you sure to delete?') ) {
                        HTTP.delete('tax-cats/delete/' + items.join(',')).then(response => {
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
