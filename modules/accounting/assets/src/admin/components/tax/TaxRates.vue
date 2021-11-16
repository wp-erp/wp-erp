<template>
    <div class="app-taxes">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Tax Rates', 'erp') }}</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="newTaxRate" id="add-tax-rate">
                        <span>{{ __('Add Tax Rate', 'erp') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="wperp-row">
            <div class="table-container wperp-col-sm-8">
                <list-table
                    tableClass="wp-ListTable widefat fixed tax-rate-list wperp-table table-striped table-dark table-taxrates"
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

                    <template slot="tax_rate_name" slot-scope="data">
                        <strong>
                            <a href="#" @click.prevent="singleTaxRate(data.row.tax_id, data.row.tax_rate_name)"> {{ data.row.tax_rate_name }}</a>
                        </strong>
                    </template>
                </list-table>
            </div>
            <div class="wperp-col-sm-4">
                <tax-shortcuts></tax-shortcuts>
            </div>
        </div>

        <new-tax-zone v-if="taxrateModal" @close="taxrateModal = false"/>
        <new-tax-category v-if="taxcatModal" @close="taxcatModal = false"/>
        <new-tax-agency v-if="taxagencyModal" @close="taxagencyModal = false"/>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';
import NewTaxZone from 'admin/components/tax/NewTaxZone.vue';
import NewTaxCategory from 'admin/components/tax/NewTaxCategory.vue';
import NewTaxAgency from 'admin/components/tax/NewTaxAgency.vue';
import TaxShortcuts from 'admin/components/tax/TaxShortcuts.vue';

export default {
    name: 'TaxRates',

    components: {
        ListTable,
        NewTaxZone,
        NewTaxCategory,
        NewTaxAgency,
        TaxShortcuts
    },

    data() {
        return {
            modalParams: null,
            columns: {
                tax_rate_name: { label:  __('Tax Zone Name', 'erp'), isColPrimary: true },
                actions      : { label: __('Actions', 'erp') }
            },
            rows: [],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            actions: [
                { key: 'edit', label: __('Edit', 'erp'), iconClass: 'flaticon-edit' },
                { key: 'trash', label: __('Delete', 'erp'), iconClass: 'flaticon-trash' }
            ],
            bulkActions: [
                {
                    key: 'trash',
                    label: __('Move to Trash', 'erp'),
                    iconClass: 'flaticon-trash'
                }
            ],
            new_entities: [
                { namedRoute: 'NewTaxZone', name: __('New Tax Zone', 'erp') },
                { namedRoute: 'NewTaxCategory', name: __('New Tax Category', 'erp') },
                { namedRoute: 'NewTaxAgency', name: __('New Tax Agency', 'erp') }
            ],
            taxes                 : [{}],
            buttonTitle           : '',
            pageTitle             : '',
            url                   : '',
            singleUrl             : '',
            tax_rate              : null,
            isActiveOptionDropdown: false,
            tax_rate_id           : null,
            taxrateModal          : false,
            taxcatModal           : false,
            taxagencyModal        : false
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.fetchItems();

        this.$root.$on('comboSelected', (data) => {
            switch (data.namedRoute) {
            case 'NewTaxZone':
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
        });
    },

    computed: {
        row_data() {
            const items = this.rows;

            if (items.length) {
                items.map(item => {
                    item.tax_id = item.id;
                    if (item.default === 0) {
                        item.default = '-';
                    } else {
                        item.default = __('Default', 'erp');
                    }
                });

                return items;
            }

            return [];
        }
    },

    methods: {

        fetchItems() {
            this.rows = [];

            HTTP.get('/taxes', {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page
                }
            }).then((response) => {
                this.rows = response.data;
                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);
                this.$store.dispatch('spinner/setSpinner', false);
            }).catch((error) => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },
        goToPage(page) {
            const queries = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name: 'PaginateTaxRates',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        },

        newTaxRate() {
            this.$router.push({ name: 'NewTaxRate' });
        },

        singleTaxRate(tax_id, tax_rate_name) {
            this.$router.push({ name: 'SingleTaxRate', params: { id: tax_id, name: tax_rate_name } });
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete('/taxes/' + row.id).then(response => {
                        this.$delete(this.rows, index);
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', __('Deleted !', 'erp'));
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
                break;

            case 'edit':
                this.$router.push({ name: 'EditSingleTaxRate', params: { id: row.id } });
                break;

            default :
                break;
            }
        },

        onBulkAction(action, items) {
            if (action === 'trash') {
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);

                    HTTP.delete('taxes/delete/' + items.join(',')).then(response => {
                        const toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                        if (toggleCheckbox.checked) {
                            // simulate click event to remove checked state
                            toggleCheckbox.click();
                        }

                        this.fetchItems();
                        this.$store.dispatch('spinner/setSpinner', false);
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
            }
        }
    }
};
</script>
<style lang="less">
    .app-taxes {
        .table-container {
            width: 600px;
        }

        .check-column {
            padding: 20px !important;
        }

        @media (min-width: 783px) {
            .actions {
                text-align: right;
            }

            .col--actions {
                float: right !important;
            }
            .row-actions {
                text-align: right !important;
            }
        }
    }
</style>
