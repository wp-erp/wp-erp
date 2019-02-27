<template>
    <div class="wperp-container">
        <div id="wperp-invoice-modal" class="wperp-modal wperp-modal-open wperp-custom-scroll" role="dialog">
            <div class="wperp-modal-dialog">
                <div class="wperp-modal-content">
                    <div class="wperp-modal-body">
                        <ul class="errors" v-if="error_msg.length">
                            <li v-for="(error, index) in error_msg" :key="index">* {{ error }}</li>
                        </ul>
                        <!-- modal body title -->
                        <!-- add new product form -->
                        <form action="" method="post" class="add-product-form wperp-form-horizontal">
                            <!-- product name field -->
                            <div class="wperp-form-group">
                                <input type="text" class="wperp-form-field" placeholder="Enter Product Name Here"
                                       v-model="ProductFields.name">
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
                                            <label>Product Type</label>
                                        </div>
                                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                                            <div class="with-multiselect">
                                                <multi-select
                                                    v-model="ProductFields.type"
                                                    :options="productType"
                                                    :multiple="false"/>
                                                <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="wperp-row">
                                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                                            <label>Category</label>
                                        </div>
                                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                                            <div class="with-multiselect">
                                                <multi-select
                                                    v-model="ProductFields.categories"
                                                    :options="categories"
                                                    :multiple="false"/>
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
                                            <input type="text" name="cost-price" id="cost-price" value="0"
                                                   class="dk-form-field" v-model="ProductFields.costPrice">
                                        </div>
                                    </div>
                                    <div class="wperp-row">
                                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                                            <label for="sale-price">Sale Price</label>
                                        </div>
                                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                                            <input type="text" name="sale-price" id="sale-price" value="0"
                                                   class="dk-form-field" v-model="ProductFields.salePrice">
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
                                            <label>Vendor</label>
                                        </div>
                                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                                            <div class="with-multiselect">
                                                <multi-select
                                                    v-model="ProductFields.vendor"
                                                    :options="vendors"
                                                    :multiple="false"/>
                                                <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                            </div>
                                        </div>
                                        <div class="wperp-col-sm-3 wperp-col-xs-12">
                                            <label>Tax Category</label>
                                        </div>
                                        <div class="wperp-col-sm-9 wperp-col-xs-12">
                                            <div class="with-multiselect">
                                                <multi-select
                                                    v-model="ProductFields.tax_cat_id"
                                                    :options="tax_cats"
                                                    :multiple="false"/>
                                                <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- buttons -->
                            <div class="buttons-wrapper text-right">
                                <button class="wperp-btn btn--default" @click.prevent="$parent.$emit('close')">Cancel
                                </button>
                                <button v-if="!product" class="wperp-btn btn--primary" @click.prevent="saveProduct">
                                    Publish
                                </button>
                                <button v-else class="wperp-btn btn--primary" @click.prevent="saveProduct">Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
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
                error_msg: [],
                ProductFields: {
                    id: null,
                    name: '',
                    type: 0,
                    categories: 0,
                    costPrice: 0,
                    salePrice: 0,
                    vendor: 0,
                    tax_cat_id: 0
                },
                vendors: [],
                categories: [],
                tax_cats: [],
                productType: [],
            }
        },

        created() {
            if (this.product) {
                let product = this.product;
                this.ProductFields.name = product.name;
                this.ProductFields.id = product.id;
                this.ProductFields.type = {id: product.product_type_id, name: product.type_name};
                this.ProductFields.categories = {id: product.category_id, name: product.cat_name};
                this.ProductFields.tax_cat_id = {id: product.category_id, name: product.cat_name};
                this.ProductFields.vendor = {id: product.vendor, name: product.vendor_name};
                this.ProductFields.salePrice = product.sale_price;
                this.ProductFields.costPrice = product.cost_price;
            }
            this.loaded();
        },

        methods: {
            saveProduct() {
                if (!this.checkForm()) {
                    return false;
                }

                this.$store.dispatch( 'spinner/setSpinner', true );
                if (!this.product) {
                    var type = 'post';
                    var url = 'products';
                } else {
                    var type = 'put';
                    var url = 'products/' + this.ProductFields.id;
                }
                var data = {
                    name: this.ProductFields.name,
                    product_type_id: this.ProductFields.type,
                    category_id: this.ProductFields.categories,
                    tax_cat_id: this.ProductFields.tax_cat_id,
                    vendor: this.ProductFields.vendor,
                    cost_price: this.ProductFields.costPrice,
                    sale_price: this.ProductFields.salePrice,
                };
                HTTP[type](url, data).then(response => {
                    this.$parent.$emit('close');
                    this.$parent.getProducts();
                    this.resetForm();
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert('success', 'Product Created!');
                });
            },

            loaded() {
                this.getVendors();
                this.getCategories();
                this.getTaxCategories();
                this.getProductTypes();
            },

            getVendors() {
                HTTP.get('vendors').then(response => {
                    if (response.data) {
                        for (let i in response.data) {
                            var vendor = response.data[i];
                            var object = {id: vendor.id, name: vendor.first_name + ' ' + vendor.last_name};
                            this.vendors.push(object);
                        }
                    }
                })
            },

            getCategories() {
                HTTP.get('product-cats').then(response => {
                    this.categories = response.data;
                })
            },

            getTaxCategories() {
                HTTP.get('tax-cats').then(response => {
                    this.tax_cats = response.data;
                })
            },

            getProductTypes() {
                HTTP.get('products/types').then(response => {
                    this.productType = response.data;
                })
            },

            resetForm() {
                this.ProductFields.id = null;
                this.ProductFields.name = '';
                this.ProductFields.type = [];
                this.ProductFields.categories = [];
                this.ProductFields.vendor = [];
                this.ProductFields.costPrice = '';
                this.ProductFields.salePrice = '';
            },

            checkForm() {
                this.error_msg = [];

                if (this.ProductFields.name && this.ProductFields.type && this.ProductFields.vendor && this.ProductFields.costPrice && this.ProductFields.salePrice) {
                    return true;
                }

                if (!this.ProductFields.name) {
                    this.error_msg.push('Product name is required');
                }

                if (!this.ProductFields.type) {
                    this.error_msg.push('Product type is required');
                }

                if (!this.ProductFields.costPrice) {
                    this.error_msg.push('Product cost price is required');
                }

                if (!this.ProductFields.salePrice) {
                    this.error_msg.push('Product sale price is required');
                }

                if (!this.ProductFields.vendor) {
                    this.error_msg.push('Vendor is required');
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
