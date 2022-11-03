<template>
    <div class="wperp-products">
        <div class="products-header">
            <h2 class="add-new-product">
                <span>{{ __('Products', 'erp') }}</span>
                <a href="" id="erp-product-new" @click.prevent="showModal = true">{{ __('Add New', 'erp') }}</a>
            </h2>

            <div class="erp-btn-group">
                <button @click.prevent="showImportModal = true">{{ __( 'Import', 'erp' ) }}</button>
                <button @click.prevent="showExportModal = true">{{ __( 'Export', 'erp' ) }}</button>
            </div>

            <!-- top search bar -->
            <product-search v-model="search" />
        </div>

        <product-modal v-if="showModal" :product.sync="product" />

        <export-modal v-if="showExportModal" />

        <import-modal v-if="showImportModal" />

        <list-table
            tableClass="wperp-table table-striped table-dark widefat table2 product-list"
            action-column="actions"
            :columns="columns"
            :rows="products"
            :bulk-actions="bulkActions"
            @action:click="onActionClick"
            @bulk:click="onBulkAction"
            :total-items="paginationData.totalItems"
            :total-pages="paginationData.totalPages"
            :per-page="paginationData.perPage"
            :current-page="paginationData.currentPage"
            @pagination="goToPage"
            :actions="[
                { key: 'edit', label: __('Edit', 'erp') },
                { key: 'trash', label: __('Delete', 'erp') }
            ]">

        </list-table>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from '../list-table/ListTable.vue';
import ProductModal from './ProductModal.vue';
import ProductSearch from './Search.vue'
import ExportModal from './ExportModal.vue';
import ImportModal from './ImportModal.vue';

export default {
    name: 'Products',

    components: {
        ListTable,
        ProductModal,
        ExportModal,
        ImportModal,
        ProductSearch,
    },

    data() {
        return {
            products : [],
            product  : null,
            search   : '',
            showModal: false,
            columns  : {
                name: {
                    label: __('Product Name', 'erp'),
                    isColPrimary: true
                },
                sale_price: {
                    label: __('Sale Price', 'erp')
                },
                cost_price: {
                    label: __('Cost Price', 'erp')
                },
                cat_name: {
                    label: __('Product Category', 'erp')
                },
                tax_cat_name: {
                    label: __('Tax Category', 'erp')
                },
                product_type_name: {
                    label: __('Product Type', 'erp')
                },
                vendor_name: {
                    label: __('Vendor', 'erp')
                },
                actions: {
                    label: __('Actions', 'erp')
                }
            },
            bulkActions: [
                {
                    key: 'trash',
                    label: __('Move to Trash', 'erp'),
                    img: erp_acct_var.erp_assets + '/images/trash.png' /* global erp_acct_var */
                }
            ],
            paginationData: {
                totalItems : 0,
                totalPages : 0,
                perPage    : 20,
                currentPage: this.$route.params.page === undefined ? 1 : parseInt(this.$route.params.page)
            },
            showExportModal: false,
            showImportModal: false,
        };
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.getProducts();

        this.$on('close', function() {
            this.showModal       = false;
            this.showImportModal = false;
            this.showExportModal = false;
            this.product         = null;
        });

        this.$root.$on('imported-products', () => {
            this.showImportModal = false;
            this.getProducts();
        });
    },

    watch: {
        search(newVal, oldVal) {
            this.getProducts();
        }
    },

    methods: {
        getProducts() {
            this.products = [];

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP.get('/products', {
                params: {
                    per_page: this.paginationData.perPage,
                    page    : this.$route.params.page === undefined
                            ? this.paginationData.currentPage
                            : this.$route.params.page,
                    s       : this.search
                }
            }).then(response => {
                this.products = response.data;

                this.paginationData.totalItems = parseInt(response.headers['x-wp-total']);
                this.paginationData.totalPages = parseInt(response.headers['x-wp-totalpages']);

                this.$store.dispatch('spinner/setSpinner', false);
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            });
        },

        onActionClick(action, row, index) {
            if (action === 'edit') {
                this.showModal = true;
                this.product   = row;
            } else if (action === 'trash') {
                if (confirm(__('Are you sure want to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);

                    HTTP.delete('products/' + row.id).then(response => {
                        this.$delete(this.products, index);
                        this.getProducts();

                        this.$store.dispatch('spinner/setSpinner', false);
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
            }
        },

        onBulkAction(action, items) {
            if (action === 'trash') {
                if (confirm(__('Are you sure want to delete?', 'erp'))) {
                    this.$store.dispatch('spinner/setSpinner', true);

                    HTTP.delete('products/delete/' + items).then(response => {
                        const toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                        if (toggleCheckbox.checked) {
                            toggleCheckbox.click();
                        }
                        this.getProducts();

                        this.$store.dispatch('spinner/setSpinner', false);
                    }).catch(error => {
                        this.$store.dispatch('spinner/setSpinner', false);
                        throw error;
                    });
                }
            }
        },

        goToPage(page) {
            const queries = Object.assign({}, this.$route.query);
            this.paginationData.currentPage = page;
            this.$router.push({
                name  : 'PaginateProducts',
                params: { page: page },
                query : queries
            });

            this.getProducts();
        }

    },
};
</script>

<style lang="less">
    .wperp-products {
        .products-header {
            display: flex;
            align-items: center;

            .add-new-product {
                margin-top: 15px;
                align-items: center;
                display: flex;

                span {
                    font-size: 18px;
                    font-weight: bold;
                }

                a {
                    background: #1a9ed4;
                    border-radius: 3px;
                    color: #fff;
                    font-size: 12px;
                    height: 29px;
                    line-height: 29px;
                    margin-left: 13px;
                    text-align: center;
                    text-decoration: none;
                    width: 80px !important;

                    @media (max-width: 782px) and (min-width: 768px) {
                        margin-right: 18rem;
                        margin-bottom: 3px;
                        max-width: 120px;
                    }

                    @media (max-width: 767px) and (min-width: 707px) {
                        margin-right: 16rem;
                        margin-bottom: 3px;
                    }

                    @media (max-width: 706px) and (min-width: 651px) {
                        margin-right: 14rem;
                        margin-bottom: 3px;
                    }

                    @media (max-width: 650px) {
                        margin-right: 12rem;
                        margin-bottom: 3px;
                    }
                }
            }
        }

        .check-column {
            padding: 20px !important;
        }

        @media (min-width: 783px) {
            .product-list {
                .col--actions {
                    float: left !important;
                }
                .row-actions {
                    text-align: left !important;
                }
            }
        }
    }

    .search-btn {
        @media (max-width: 650px) {
            display: none;
        }
    }

    .people-search {
        @media (max-width: 479px) {
            margin-top: 20px;
        }
    }
</style>
