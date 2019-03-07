<template>
    <div class="products">
        <product-modal v-if="showModal" :product.sync="product"></product-modal>
        <h2 class="add-new-product">
            <span>Products</span>
            <a href="" id="erp-product-new" @click.prevent="showModal = true">+ Add New Product</a>
        </h2>
        <list-table
            tableClass="wp-ListTable widefat fixed product-list"
            action-column="actions"
            :columns="columns"
            :rows="products"
            :bulk-actions="bulkActions"
            @action:click="onActionClick"
            @bulk:click="onBulkAction"
            :actions="[
                { key: 'edit', label: 'Edit' },
                { key: 'trash', label: 'Delete' }
            ]">

        </list-table>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import Menu from 'admin/components/menu/ERPMenu.vue'
    import ListTable from 'admin/components/list-table/ListTable.vue'
    import ProductModal from 'admin/components/products/ProductModal.vue'
    import Modal from 'admin/components/modal/Modal.vue'

    export default {
        name: 'Products',

        components: {
            Menu,
            ListTable,
            Modal,
            ProductModal
        },

        data() {
            return {
                products: [],
                product: null,
                showModal: false,
                columns: {
                    'name': {
                        label: 'Product Name'
                    },
                    'sale_price': {
                        label: 'Sale Price'
                    },
                    'cost_price': {
                        label: 'Cost Price',
                    },
                    'cat_name': {
                        label: 'Product Category'
                    },
                    'tax_cat_name': {
                        label: 'Tax Category'
                    },
                    'product_type_name': {
                        label: 'Product Type'
                    },
                    'vendor_name': {
                        label: 'Vendor'
                    },
                    'actions': { label: 'Actions' }
                },
                bulkActions: [
                    {
                        key: 'trash',
                        label: 'Move to Trash',
                        img: erp_acct_var.erp_assets + '/images/trash.png',
                    }
                ],
            }
        },
        methods: {
            getProducts() {
                this.products = [];
                HTTP.get('products').then( response => {
                    this.products = response.data;
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } );
            },
            createProduct() {

            },
            onActionClick( action, row, index ) {
                if ( 'edit' == action ) {
                    this.showModal = true;
                    this.product = row;
                } else if ( 'trash' == action ) {
                    if ( confirm( 'Are sure want to Delete ?' ) ) {
                        this.$store.dispatch( 'spinner/setSpinner', true );

                        HTTP.delete( 'products/' + row.id ).then(response => {
                            this.$delete( this.products, index );
                            this.getProducts();

                            this.$store.dispatch( 'spinner/setSpinner', false );
                        } ).catch( error => {
                            this.$store.dispatch( 'spinner/setSpinner', false );
                        } );
                    }
                }
            },
            onBulkAction( action, items ) {
                if ( 'trash' == action ) {
                    if ( confirm( 'Are you sure want to delete?' ) ) {
                        this.$store.dispatch( 'spinner/setSpinner', true );

                        HTTP.delete('products/delete/' + items).then(response => {
                            let toggleCheckbox = document.getElementsByClassName('column-cb')[0].childNodes[0];

                            if ( toggleCheckbox.checked ) {
                                toggleCheckbox.click();
                            }
                            this.getProducts();

                            this.$store.dispatch( 'spinner/setSpinner', false );
                        }).catch( error => {
                            this.$store.dispatch( 'spinner/setSpinner', false );
                        } );
                    }
                }
            }

        },
        created() {
            this.$store.dispatch( 'spinner/setSpinner', true );
            this.getProducts();
            this.$on( 'close', function() {
                this.showModal = false;
            } );
        }
    }
</script>

<style lang="less">
    .products {
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
        .product-list {
            border-radius: 3px;
            tbody {
                background: #FAFAFA;
            }
            tfoot th,
            thead th {
                color: #1A9ED4;
                font-weight: bold;
            }
            th ul,
            th li {
                margin: 0;
            }
            th li {
                display: flex;
                align-items: center;
                img {
                    width: 22px;
                    padding-right: 5px;
                }
            }
            .column.title {
                &.selected {
                    color: #1A9ED4;
                }
                a {
                    color: #222;
                    font-weight: normal;
                    &:hover {
                        color: #1A9ED4;
                    }
                }
            }
            .check-column input {
                border-color: #E7E7E7;
                box-shadow: none;
                border-radius: 3px;
                &:checked {
                    background: #1ABC9C;
                    border-color: #1ABC9C;
                    border-radius: 3px;
                    &:before {
                        color: #fff;
                    }
                }
            }
            .row-actions {
                padding-left: 20px;
            }
        }
        .widefat {
            tfoot td,
            tbody th {
                line-height: 2.5em;
            }
            tbody td {
                line-height: 3em;
            }
        }
    }
</style>
