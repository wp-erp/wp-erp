<template>
    <div class="wperp-products">
        <product-modal v-if="showModal" :product.sync="product"></product-modal>
        <h2 class="add-new-product">
            <span>{{ __('Products', 'erp') }}</span>
            <a href="" id="erp-product-new" @click.prevent="showModal = true">{{ __('Add New Product', 'erp') }}</a>
        </h2>

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
                { key: 'edit', label: 'Edit' },
                { key: 'trash', label: 'Delete' }
            ]">

        </list-table>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import ListTable from 'admin/components/list-table/ListTable.vue';
import ProductModal from 'admin/components/products/ProductModal.vue';

export default {
    name: 'Products',

    components: {
        ListTable,
        ProductModal
    },

    data() {
        return {
            products : [],
            product  : null,
            showModal: false,
            columns  : {
                name: {
                    label: __('Product Name', 'erp')
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
                actions: { label: 'Actions' }
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
            }
        };
    },
    methods: {
        getProducts() {
            this.products = [];
            HTTP.get('/products', {
                params: {
                    per_page: this.paginationData.perPage,
                    page: this.$route.params.page === undefined ? this.paginationData.currentPage : this.$route.params.page
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
    created() {
        this.$store.dispatch('spinner/setSpinner', true);
        this.getProducts();

        this.$on('close', function() {
            this.showModal = false;
            this.product = null;
        });
    }
};
</script>

<style lang="less">
    .wperp-products {
        .add-new-product {
            margin-top:15px;
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
                width: 135px;
            }
        }

        .check-column {
            padding: 20px !important;
        }

        .product-list {
            .col--actions {
                float: left !important;
            }
        }
    }
</style>
