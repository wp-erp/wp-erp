<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Rate Names</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="showModal = true">
                        <span>Add Tax Rate Name</span>
                    </a>
                </div>
            </div>
        </div>

        <new-tax-rate-name v-if="showModal" :rate_name_id="rate_name_id" :is_update="is_update" @close="showModal = false"></new-tax-rate-name>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-rate-list wperp-table table-striped table-dark"
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
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import NewTaxRateName from 'admin/components/tax/NewTaxRateName.vue'

    export default {
        name: 'TaxRateNames',

        components: {
            NewTaxRateName,
            ListTable,
        },

        data() {
            return {
                modalParams: null,
                columns: {
                    'tax_id': {label: 'ID'},
                    'tax_name': {label: 'Rate Name'},
                    'actions': {label: 'Actions'}
                },
                rows: [],
                paginationData: {
                    totalItems: 0,
                    totalPages: 0,
                    perPage: 10,
                    currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
                },
                actions: [
                    {key: 'edit', label: 'Edit', iconClass: 'flaticon-edit'},
                    {key: 'trash', label: 'Delete', iconClass: 'flaticon-trash'}
                ],
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        iconClass: 'flaticon-trash'
                    }
                ],
                taxes: [{}],
                buttonTitle: '',
                pageTitle: '',
                url: '',
                singleUrl: '',
                isActiveOptionDropdown: false,
                singleTaxRateModal: false,
                showModal: false,
                rate_name_id: null,
                is_update: false
            }
        },

        created() {
            this.$root.$on('refetch_tax_data',() => {
                this.fetchItems();
            });
            this.fetchItems();
        },

        computed: {
            row_data() {
                let items = this.rows;
                items.map(item => {
                    item.tax_id = item.id;
                    item.tax_name = item.name;
                });
                return items;
            }
        },

        methods: {

            fetchItems() {
                this.rows = [];
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get('tax-rate-names', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    }
                }).then((response) => {
                    this.rows = response.data;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch((error) => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                });
            },

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
                this.paginationData.currentPage = page;
                this.$router.push({
                    name: 'PaginateTaxRateNames',
                    params: {page: page},
                    query: queries
                });

                this.fetchItems();
            },

            singleTaxRate(tax_id) {
                this.$router.push({name: 'SingleTaxRate', params: {id: tax_id}})
            },

            onActionClick(action, row, index) {
                switch (action) {
                    case 'trash':
                        if (confirm('Are you sure to delete?')) {
                            this.$store.dispatch( 'spinner/setSpinner', true );
                            HTTP.delete('tax-rate-names' + '/' + row.id).then(response => {
                                this.$delete(this.rows, index);
                                this.$store.dispatch( 'spinner/setSpinner', false );
                                this.showAlert( 'success', 'Deleted !' );
                            });
                        }
                        break;

                    case 'edit':
                        this.showModal = true;
                        this.rate_name_id = row.id;
                        this.is_update = true;
                        this.fetchItems();
                        break;

                    default :
                        break;
                }
            },

            onBulkAction(action, items) {
                if ('trash' === action) {
                    if (confirm('Are you sure to delete?')) {
                        this.$store.dispatch( 'spinner/setSpinner', true );
                        HTTP.delete('tax-rate-names/delete/' + items.join(',')).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if (toggleCheckbox.checked) {
                                // simulate click event to remove checked state
                                toggleCheckbox.click();
                            }

                            this.fetchItems();
                            this.$store.dispatch( 'spinner/setSpinner', false );
                            this.showAlert( 'success', 'Deleted !' );
                        });
                    }
                }
            },
        }
    }
</script>
<style lang="less">
    .erp-acct-tax-menus {
        margin-left: 600px;
    }
    .combo-box {
        margin-right: 10px !important;
    }
</style>
