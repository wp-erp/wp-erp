<template>
    <modal title="Add New Product" class="product-modal" @close="$parent.$emit('close')">
        <template slot="body">
            <div class="wperp-container">
                <div id="wperp-invoice-modal" class="wperp-modal wperp-modal-open wperp-custom-scroll" role="dialog">
                    <div class="wperp-modal-dialog">
                        <div class="wperp-modal-content">
                            <div class="wperp-modal-body">
                                <ul class="errors" v-if="errors.length">
                                    <li v-for="(error, index) in errors" :key="index">* {{ error }}</li>
                                </ul>
                                <!-- modal body title -->
                                <!-- add new product form -->
                                <form action="" method="post" class="add-product-form wperp-form-horizontal">
                                    <!-- product name field -->
                                    <div class="wperp-form-group">
                                        <input type="text" class="wperp-form-field" placeholder="Enter Product Name Here" v-model="fields.name">
                                    </div>

                                    <!-- product/service details panel -->
                                    <div class="wperp-panel wperp-panel-default panel-product-details">
                                        <div class="wperp-panel-heading">
                                            <span class="panel-badge panel-badge-primary"></span>
                                            <span>Product/Service Details</span>
                                        </div>
                                        <div class="wperp-panel-body">
                                            <div class="wperp-row">
                                                <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                    <label for="product-type">Product Type</label>
                                                </div>
                                                <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                    <div class="with-multiselect">
                                                        <multi-select
                                                        v-model="fields.type"
                                                        :options="productType"
                                                        :multiple="false" />
                                                        <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wperp-row">
                                                <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                    <label for="product-category">Category</label>
                                                </div>
                                                <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                    <div class="with-multiselect">
                                                        <multi-select
                                                        v-model="fields.categories"
                                                        :options="categories"
                                                        :multiple="false" />
                                                        <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- product/service details panel -->
                                    <div class="wperp-panel wperp-panel-default panel-product-info">
                                        <div class="wperp-panel-heading">
                                            <span class="panel-badge panel-badge-info"></span>
                                            <span>Product Information</span>
                                        </div>
                                        <div class="wperp-panel-body">
                                            <div class="wperp-row">
                                                <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                    <label for="cost-price">Cost Price</label>
                                                </div>
                                                <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                    <input type="text" name="cost-price" id="cost-price" value="0" class="dk-form-field" v-model="fields.costPrice">
                                                </div>
                                            </div>
                                            <div class="wperp-row">
                                                <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                    <label for="sale-price">Sale Price</label>
                                                </div>
                                                <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                    <input type="text" name="sale-price" id="sale-price" value="0" class="dk-form-field" v-model="fields.salePrice">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Miscellaneous panel -->
                                    <div class="wperp-panel wperp-panel-default panel-miscellaneous">
                                        <div class="wperp-panel-heading">
                                            <span class="panel-badge panel-badge-secondary"></span>
                                            <span>Miscellaneous</span>
                                        </div>
                                        <div class="wperp-panel-body">
                                            <div class="wperp-row">
                                                <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                    <label for="vendor">Vendor</label>
                                                </div>
                                                <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                    <div class="with-multiselect">
                                                        <multi-select
                                                        v-model="fields.vendor"
                                                        :options="vendors"
                                                        :multiple="false" />
                                                        <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- buttons -->
                                    <div class="buttons-wrapper text-right">
                                        <button v-if="!product" class="wperp-btn btn--primary" @click.prevent="saveProduct">Publish</button>
                                        <button v-else class="wperp-btn btn--primary" @click.prevent="saveProduct">Update</button>
                                        <button  class="wperp-btn btn--default" @click.prevent="$parent.$emit('close')">Cancel</button>
                                    </div>
                               </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import HTTP from 'admin/http.js'
    import Modal from 'admin/components/modal/Modal.vue'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {
        name: 'ProductModal',

        components: {
            Modal,
            MultiSelect
        },

        props: {
            product: {
                type: Object,
                default: {}
            }
        },
        data() {
            return {
                errors: [],
                fields: {
                    id: null,
                    name: '',
                    type: 0,
                    categories: 0,
                    costPrice: 0,
                    salePrice: 0,
                    vendor: 0,
                },
                vendors: [],
                categories: [],
                productType: [],
            }
        },
        created() {
            if ( this.product ) {
                let product = this.product;
                this.fields.name = product.name;
                this.fields.id   = product.id;
                this.fields.type = { id: product.product_type_id, name: product.type_name };
                this.fields.categories = { id: product.category_id, name: product.cat_name };
                this.fields.vendor = { id: product.vendor, name: product.vendor_name };
                this.fields.salePrice = product.sale_price;
                this.fields.costPrice = product.cost_price;
            }
            this.loaded();
        },
        methods: {
            saveProduct() {
                if ( ! this.checkForm() ) {
                    return false;
                }
                if( ! this.product ) {
                    var type = 'post';
                    var url  = 'products';
                } else {
                    var type = 'put';
                    var url  = 'products/' + this.fields.id;
                }
                var data = {
                    name: this.fields.name,
                    product_type_id: this.fields.type,
                    category_id: this.fields.categories,
                    vendor: this.fields.vendor,
                    cost_price: this.fields.costPrice,
                    sale_price: this.fields.salePrice,
                }
                HTTP[type](url, data).then( response => {
                    this.$parent.$emit('close');
                    this.$parent.getProducts();
                    this.resetForm();
                } );
            },
            loaded() {
                this.getVendors();
                this.getCategories();
                this.getProductTypes();
            },
            getVendors() {
                HTTP.get('vendors').then( response => {
                    if ( response.data ) {
                        for ( let i in response.data ) {
                            var vendor = response.data[i];
                            var object = { id: vendor.id, name: vendor.first_name + ' ' + vendor.last_name };
                            this.vendors.push( object );
                        }
                    }
                } )
            },
            getCategories() {
                HTTP.get('product-cats').then( response => {
                    this.categories = response.data;
                } )
            },
            getProductTypes() {
                HTTP.get('products/types').then( response => {
                    this.productType = response.data;
                } )
            },
            resetForm() {
                this.fields.id = null;
                this.fields.name = '';
                this.fields.type = [];
                this.fields.categories = [];
                this.fields.vendor = [];
                this.fields.costPrice = '';
                this.fields.salePrice;
            },
            checkForm() {
                this.errors = [];

                if ( this.fields.name && this.fields.type && this.fields.vendor && this.fields.costPrice && this.fields.salePrice ) {
                    return true;
                }

                if ( ! this.fields.name ) {
                    this.errors.push( 'Product name is required' );
                }

                if ( ! this.fields.type ) {
                    this.errors.push( 'Product type is required' );
                }

                if ( ! this.fields.costPrice ) {
                    this.errors.push( 'Product cost price is required' );
                }

                if ( ! this.fields.salePrice ) {
                    this.errors.push( 'Product sale price is required' );
                }

                if ( ! this.fields.vendor ) {
                   this.errors.push( 'Vendor is required' );
                }
                return false;
            }
        }
    }
</script>

<style lang="less">
    .product-modal {
        .modal .modal-content {
            top: 15% !important;
            width: 800px !important;
        }
        .wperp-modal {
            border: 0 !important;
            top: 20% !important;
        }

        .wperp-modal .wperp-modal-content {
            -webkit-box-shadow: none !important;
            box-shadow: none;
            border: 0 !important;
            border-radius: 0 !important;
        }
        .errors {
            margin: 0;
            color: #f44336;
            li {
                background: #f3f3f3;
                padding: 2px 10px;
            }
        }
    }
</style>
