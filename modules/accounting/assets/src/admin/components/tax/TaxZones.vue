<template>
    <div class="app-tax-zones">
        <div class="content-header-section separator">
            <div class="wperp-row wperp-between-xs">
                <div class="wperp-col">
                    <h2 class="content-header__title">{{ __('Tax Zones', 'erp') }}</h2>
                    <a class="wperp-btn btn--primary" @click.prevent="showModal = true">
                        <span>{{ __('Add Tax Zone', 'erp') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <new-tax-zone v-if="showModal" :rate_name_id="rate_name_id" :is_update="is_update" @close="showModal = false"></new-tax-zone>

        <div class="wperp-row">
            <div class="table-container wperp-col-sm-8">
                <list-table
                    tableClass="wp-ListTable widefat fixed tax-zone-list wperp-table table-striped table-dark"
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

                    <template slot="default" slot-scope="data">
                        {{ '1' === data.row.default ? '&#x02713;' : '' }}
                    </template>
                </list-table>
            </div>
            <div class="wperp-col-sm-4">
                <tax-shortcuts></tax-shortcuts>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';
import NewTaxZone from 'admin/components/tax/NewTaxZone.vue';
import TaxShortcuts from 'admin/components/tax/TaxShortcuts.vue';

export default {
    name: 'TaxZones',

    components: {
        NewTaxZone,
        ListTable,
        TaxShortcuts
    },

    data() {
        return {
            modalParams: null,
            columns: {
                tax_rate_name: { label: __('Tax Zone Name', 'erp'), isColPrimary: true },
                tax_number   : { label: __('Tax Number', 'erp') },
                default      : { label: __('Default', 'erp') },
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
            taxes                 : [{}],
            buttonTitle           : '',
            pageTitle             : '',
            url                   : '',
            singleUrl             : '',
            isActiveOptionDropdown: false,
            singleTaxRateModal    : false,
            showModal             : false,
            rate_name_id          : null,
            is_update             : false
        };
    },

    created() {
        this.$root.$on('refetch_tax_data', () => {
            this.fetchItems();
            this.is_update = false;
        });
        this.$root.$on('modal_closed', () => {
            this.is_update = false;
        });
        this.fetchItems();
    },

    computed: {
        row_data() {
            const items = this.rows;
            items.map(item => {
                item.tax_id = item.id;
                item.tax_name = item.name;
            });

            return items;
        }
    },

    methods: {

        fetchItems() {
            this.$store.dispatch('spinner/setSpinner', true);

            this.rows = [];
            HTTP.get('/tax-rate-names', {
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
                name: 'PaginateTaxZones',
                params: { page: page },
                query: queries
            });

            this.fetchItems();
        },

        singleTaxRate(tax_id) {
            this.$router.push({ name: 'SingleTaxRate', params: { id: tax_id } });
        },

        onActionClick(action, row, index) {
            switch (action) {
            case 'trash':
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete('tax-rate-names' + '/' + row.id).then(response => {
                        this.$delete(this.rows, index);
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', __('Deleted !', 'erp'));
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
            if (action === 'trash') {
                if (confirm(__('Are you sure to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);
                    HTTP.delete('tax-rate-names/delete/' + items.join(',')).then(response => {
                        const toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                        if (toggleCheckbox.checked) {
                            // simulate click event to remove checked state
                            toggleCheckbox.click();
                        }

                        this.fetchItems();
                        this.$store.dispatch('spinner/setSpinner', false);
                        this.showAlert('success', __('Deleted !', 'erp'));
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
    .erp-acct-tax-menus {
        margin-left: 600px;
    }

    .combo-box {
        margin-right: 10px !important;
    }

    .app-tax-zones {
        @media (min-width: 783px) {
            .col--actions {
                float: left !important;
            }
            .row-actions {
                text-align: left !important;
            }
        }

        .check-column {
            padding: 20px !important;
        }

        tbody .column.default {
            color: #388e3c;
            font-size: 26px;
            line-height: 26px;
        }
    }
</style>
