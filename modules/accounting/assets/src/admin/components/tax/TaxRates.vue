<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">Tax Rates</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="newTaxRate">
                        <span>Add Tax Rate</span>
                    </a>
                    <div class="erp-acct-tax-menus">
                        <!--<combo-box-->
                            <!--:options="new_entities"-->
                            <!--placeholder="New Tax Entity" />-->

                        <combo-box
                            :options="entity_lists"
                            :hasUrl="true"
                            placeholder="Tax Entity Lists" />
                    </div>
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

                <template slot="tax_id" slot-scope="data">
                    <strong>
                        <a href="#" @click.prevent="singleTaxRate(data.row.tax_id)"> #{{ data.row.tax_id }}</a>
                    </strong>
                </template>
            </list-table>

            <tax-rate-quick-edit v-if="taxRateQuickEditModal" :tax_id="tax_rate_id" @close="taxRateQuickEditModal = false" > </tax-rate-quick-edit>
        </div>

        <new-tax-rate-name v-if="taxrateModal" @close="taxrateModal = false"/>
        <new-tax-category v-if="taxcatModal" @close="taxcatModal = false"/>
        <new-tax-agency v-if="taxagencyModal" @close="taxagencyModal = false"/>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import ComboBox from 'admin/components/select/ComboBox.vue'
    import NewTaxRate from 'admin/components/tax/NewTaxRate.vue'
    import NewTaxRateName from 'admin/components/tax/NewTaxRateName.vue'
    import NewTaxCategory from 'admin/components/tax/NewTaxCategory.vue'
    import NewTaxAgency from 'admin/components/tax/NewTaxAgency.vue'
    import TaxRateQuickEdit from 'admin/components/tax/TaxRateQuickEdit.vue'

    export default {
        name: 'TaxRates',

        components: {
            ListTable,
            ComboBox,
            NewTaxRate,
            NewTaxRateName,
            NewTaxCategory,
            NewTaxAgency,
            TaxRateQuickEdit
        },

        data() {
            return {
                modalParams: null,
                columns: {
                    'tax_name': {label: 'Tax Name'},
                    'tax_number': {label: 'Tax Number'},
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
                    {key: 'quick_edit', label: 'Quick Edit', iconClass: 'flaticon-edit'},
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
                new_entities: [
                    {namedRoute: 'NewTaxRateName', name: 'New Tax Rate Name'},
                    {namedRoute: 'NewTaxCategory', name: 'New Tax Category'},
                    {namedRoute: 'NewTaxAgency', name: 'New Tax Agency'},
                ],
                entity_lists: [
                    {namedRoute: 'TaxRateNames', name: 'Tax Rate Names'},
                    {namedRoute: 'TaxCategories', name: 'Tax Categories'},
                    {namedRoute: 'TaxAgencies', name: 'Tax Agencies'},
                ],
                taxes: [{}],
                buttonTitle: '',
                pageTitle: '',
                url: '',
                singleUrl: '',
                tax_rate: null,
                isActiveOptionDropdown: false,
                tax_rate_id: null,
                taxRateQuickEditModal: false,
                taxrateModal: false,
                taxcatModal: false,
                taxagencyModal: false
            };
        },

        created() {
            this.$store.dispatch( 'spinner/setSpinner', true );
            this.fetchItems();

            this.$root.$on('comboSelected', (data) => {
                switch (data.namedRoute) {
                    case 'NewTaxRateName':
                        this.taxrateModal = true;
                        break;
                    case 'NewTaxCategory':
                        this.taxcatModal = true;
                        break;
                    case 'NewTaxAgency':
                        this.taxagencyModal = true;
                        break;
                    default:
                        break;
                }
            } );
        },

        computed: {
            row_data() {
                let items = this.rows;
                items.map(item => {
                    item.tax_id = item.id;
                });
                return items;
            }
        },

        methods: {

            fetchItems() {
                this.rows = [];

                HTTP.get('/taxes', {
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
                    console.log(error);
                });
            },

            goToPage(page) {
                let queries = Object.assign({}, this.$route.query);
                this.paginationData.currentPage = page;
                this.$router.push({
                    name: 'PaginateTaxRates',
                    params: {page: page},
                    query: queries
                });

                this.fetchItems();
            },

            newTaxRate() {
                this.$router.push('taxes/new');
            },

            singleTaxRate(tax_id) {
                this.$router.push({name: 'SingleTaxRate', params: {id: tax_id}})
            },

            onActionClick(action, row, index) {
                switch (action) {
                    case 'trash':
                        if (confirm('Are you sure to delete?')) {
                            this.$store.dispatch( 'spinner/setSpinner', true );
                            HTTP.delete('/taxes/' + row.id).then(response => {
                                this.$delete(this.rows, index);
                                this.$store.dispatch( 'spinner/setSpinner', false );
                                this.showAlert( 'success', 'Deleted' );
                            });
                        }
                        break;

                    case 'quick_edit':
                        this.taxRateQuickEditModal = true;
                        this.tax_rate_id = row.id;
                        break;

                    case 'edit':
                        this.$router.push({name: 'EditSingleTaxRate', params: {id: row.id}});
                        break;

                    default :
                        break;
                }
            },

            onBulkAction(action, items) {
                if ('trash' === action) {
                    if (confirm('Are you sure to delete?')) {
                        this.$store.dispatch( 'spinner/setSpinner', true );
                        HTTP.delete('taxes/delete/' + items.join(',')).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if (toggleCheckbox.checked) {
                                // simulate click event to remove checked state
                                toggleCheckbox.click();
                            }

                            this.fetchItems();
                            this.$store.dispatch( 'spinner/setSpinner', false );
                        });
                    }
                }
            },
        }
    }
</script>
<style lang="less">

</style>
