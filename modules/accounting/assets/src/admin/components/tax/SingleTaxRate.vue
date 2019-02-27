<template>
    <div class="app-customers">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{taxrate.tax_name}}</h2>
                </div>
            </div>
        </div>

        <div v-if="!is_update" class="wperp-invoice-table">
            <!--<div class="wperp-form-group wperp-col-sm-6">-->
                <!--<label> Tax Number </label>-->
                <!--{{taxrate.tax_number}}-->
            <!--</div>-->

            <!--<div class="wperp-form-group wperp-col-sm-6">-->
                <!--<div class="form-check">-->
                    <!--<label class="form-check-label">-->
                        <!--<input type="checkbox" :value="taxrate.default" class="form-check-input">-->
                        <!--<span class="form-check-sign"></span>-->
                        <!--<span class="field-label">Is this tax default?</span>-->
                    <!--</label>-->
                <!--</div>-->
            <!--</div>-->
        </div>

        <tax-rate-line-edit v-if="showModal" :tax_id="tax_id" :row_data="row_data" @close="showModal = false"></tax-rate-line-edit>

        <div class="table-container">
            <list-table
                tableClass="wp-ListTable widefat fixed tax-rate-list wperp-table table-striped table-dark"
                action-column="actions"
                :columns="columns"
                :rows="rows"
                :total-items="paginationData.totalItems"
                :total-pages="paginationData.totalPages"
                :per-page="paginationData.perPage"
                :current-page="paginationData.currentPage"
                @pagination="goToPage"
                :actions="actions"
                @action:click="onActionClick">
            </list-table>
        </div>

    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import ComboBox from 'admin/components/select/ComboBox.vue'
    import TaxRateLineEdit from 'admin/components/tax/TaxRateLineEdit.vue'

    export default {
        name: 'SingleTaxRate',

        components: {
            ListTable,
            ComboBox,
            TaxRateLineEdit
        },

        data() {
            return {
                tax_id: null,
                row_id: null,
                row_data: null,
                modalParams: null,
                columns: {
                    'component_name': {label: 'Component'},
                    'agency_name': {label: 'Agency'},
                    'tax_cat_name': {label: 'Tax Category'},
                    'tax_rate': {label: 'Tax Rate'},
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
                taxrate: {},
                buttonTitle: '',
                pageTitle: '',
                url: '',
                singleUrl: '',
                isActiveOptionDropdown: false,
                singleTaxRateModal: false,
                showModal: false,
                is_update: false
            }
        },

        created() {
            this.fetchItems();

            if( 'EditSingleTaxRate' === this.$route.name ) {
                this.is_update = true;
            }
        },

        methods: {

            fetchItems() {
                this.rows = [];

                this.tax_id = this.$route.params.id;
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.get(`/taxes/${this.tax_id}`, {
                    params: {
                        per_page: this.paginationData.perPage,
                        page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page,
                    }
                }).then((response) => {
                    this.taxrate = response.data;
                    this.rows = response.data.tax_components;
                    this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                    this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).catch((error) => {
                    console.log(error);
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

            onActionClick(action, row, index) {
                switch (action) {
                    case 'trash':
                        if (confirm('Are you sure to delete?')) {
                            this.$store.dispatch( 'spinner/setSpinner', true );
                            HTTP.delete(this.url + '/' + row.id).then(response => {
                                this.$delete(this.rows, index);
                                this.$store.dispatch( 'spinner/setSpinner', false );
                                this.showAlert( 'success', 'Deleted !' );
                            });
                        }
                        break;

                    case 'edit':
                        this.row_id = row.id;
                        this.row_data = this.rows[index];
                        this.showModal = true;
                        break;

                    default :
                        break;
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
