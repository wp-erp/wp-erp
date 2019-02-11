<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Rates</h2>
                </div>
            </div>
        </div>


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
    import NewTaxRate from 'admin/components/tax/NewTaxRate.vue'
    import ComboBox from 'admin/components/select/ComboBox.vue'

    export default {
        name: 'TaxRateNames',

        components: {
            ListTable,
            NewTaxRate,
            ComboBox
        },

        data() {
            return {
                modalParams: null,
                columns: {
                    'tax_id': {label: 'ID'},
                    'tax_name': {label: 'Rate Name'},
                    'tax_number': {label: 'Tax Number'},
                    'default': {label: 'Default'},
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
            };
        },

        created() {
            this.fetchItems();
        },

        computed: {
            row_data() {
                let items = this.rows;
                items.map(item => {
                    item.tax_id = item.id;
                    item.tax_name = item.tax_rate_name;
                    item.tax_number = item.tax_number;
                    item.default = item.default;
                });
                return items;
            }
        },

        methods: {

            fetchItems() {
                this.rows = [];

                HTTP.get('tax-rate-names', {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    }
                }).then((response) => {
                    this.rows = response.data;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                }).catch((error) => {
                    console.log(error);
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

            newTaxRate() {
                this.$router.push('taxes/new-tax');
            },

            singleTaxRate(tax_id) {
                this.$router.push({name: 'SingleTaxRate', params: {id: tax_id}})
            },

            onActionClick(action, row, index) {
                switch (action) {
                    case 'trash':
                        if (confirm('Are you sure to delete?')) {
                            HTTP.delete(this.url + '/' + row.id).then(response => {
                                this.$delete(this.rows, index);
                            });
                        }
                        break;

                    case 'edit':
                        break;

                    default :
                        break;
                }
            },

            onBulkAction(action, items) {
                if ('trash' === action) {
                    if (confirm('Are you sure to delete?')) {
                        HTTP.delete('taxes/delete/' + items.join(',')).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if (toggleCheckbox.checked) {
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
    .erp-acct-tax-menus {
        margin-left: 700px;
    }
    .combo-box {
        margin-right: 10px !important;
    }
</style>
