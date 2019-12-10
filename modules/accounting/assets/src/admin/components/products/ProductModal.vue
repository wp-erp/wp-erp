<template>
    <div id="wperp-product-modal">
        <div class="wperp-container">
            <div class="wperp-modal wperp-modal-open has-form" role="dialog">
                <div class="wperp-modal-dialog">
                    <div class="wperp-modal-content">
                        <div class="wperp-modal-header">
                            <h3 v-if="!product">{{ __('Add', 'erp') }} {{ title }}</h3>
                            <h3 v-else>{{ __('Update', 'erp') }} {{ title }}</h3>
                            <span class="modal-close">
                                <i class="flaticon-close" @click.prevent="$parent.$emit('close')"></i>
                            </span>
                        </div>
                        <div class="wperp-modal-body">
                            <ul class="errors" v-if="error_msg.length">
                                <li v-for="(error, index) in error_msg" :key="index">* {{ error }}</li>
                            </ul>
                            <!-- modal body title -->
                            <!-- add new product form -->
                            <form action="" method="post" @submit.prevent="saveProduct"
                                  class="add-product-form wperp-form-horizontal">
                                <!-- product name field -->

                                <div class="wperp-row">
                                    <div class="wperp-col-sm-3 wperp-col-xs-12">
                                        <label>{{ __('Product Name', 'erp') }} <span class="wperp-required-sign">*</span></label>
                                    </div>
                                    <div class="wperp-col-sm-9 wperp-col-xs-12">
                                        <input type="text" class="wperp-form-field"
                                               :placeholder="__('Enter Product Name Here', 'erp')"
                                               v-model="ProductFields.name" required>
                                    </div>
                                </div>

                                <!-- product/service details panel -->
                                <div class="wperp-panel wperp-panel-default panel-product-details">
                                    <div class="wperp-panel-heading">
                                        <span class="panel-badge panel-badge-primary"></span>
                                        <span>{{ __('Product/Service Details', 'erp') }}</span>
                                    </div>
                                    <div class="wperp-panel-body">
                                        <div class="wperp-row">
                                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                <label>{{ __('Product Type', 'erp') }} <span class="wperp-required-sign">*</span></label>
                                            </div>
                                            <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                <div class="with-multiselect">
                                                    <multi-select
                                                        v-model="ProductFields.type"
                                                        :options="productType"
                                                        :disabled="isDisabled"
                                                        :multiple="false"/>
                                                    <!-- <i class="flaticon-arrow-down-sign-to-navigate"></i> -->
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wperp-row">
                                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                <label>{{ __('Category', 'erp') }}</label>
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
                                        <span>{{ __('Product Information', 'erp') }}</span>
                                    </div>
                                    <div class="wperp-panel-body">
                                        <div class="wperp-row">
                                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                <label for="cost-price">{{ __('Cost Price', 'erp') }}</label>
                                            </div>
                                            <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                <input type="text" name="cost-price" id="cost-price" value="0"
                                                       class="dk-form-field" v-model="ProductFields.costPrice">
                                            </div>
                                        </div>
                                        <div class="wperp-row">
                                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                <label for="sale-price">{{ __('Sale Price', 'erp') }} <span class="wperp-required-sign">*</span></label>
                                            </div>
                                            <div class="wperp-col-sm-9 wperp-col-xs-12">
                                                <input type="text" name="sale-price" id="sale-price" value="0"
                                                       class="dk-form-field" v-model="ProductFields.salePrice" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Miscellaneous panel -->
                                <div class="wperp-panel wperp-panel-default panel-miscellaneous">
                                    <div class="wperp-panel-heading">
                                        <span class="panel-badge panel-badge-secondary"></span>
                                        <span>{{ __('Miscellaneous', 'erp') }}</span>
                                    </div>
                                    <div class="wperp-panel-body">
                                        <div class="wperp-row">
                                            <div class="wperp-col-sm-3 wperp-col-xs-12">
                                                <label>{{ __('Vendor', 'erp') }} <span class="wperp-required-sign">*</span></label>
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
                                                <label>{{ __('Tax Category', 'erp') }}</label>
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
                                    <button class="wperp-btn btn--default" @click.prevent="$parent.$emit('close')">
                                        {{ __('Cancel', 'erp') }}
                                    </button>
                                    <button v-if="!product" class="wperp-btn btn--primary">{{ __('Save', 'erp') }}</button>
                                    <button v-else class="wperp-btn btn--primary">{{ __('Update', 'erp') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'ProductModal',

    components: {
        MultiSelect
    },

    props: {
        product: {
            type   : Object,
            default: () => []
        }
    },

    data() {
        return {
            error_msg    : [],
            ProductFields: {
                id        : null,
                name      : '',
                type      : 0,
                categories: 0,
                costPrice : 0,
                salePrice : 0,
                vendor    : 0,
                tax_cat_id: 0
            },
            vendors      : [],
            categories   : [],
            tax_cats     : [],
            productType  : [],
            title        : __( 'Product', 'erp' ),
            isDisabled   : false
        };
    },

    created() {
        if (this.product) {
            const product                   = this.product;
            this.ProductFields.name       = product.name;
            this.ProductFields.id         = product.id;
            this.ProductFields.type       = { id: product.product_type_id, name: product.type_name };
            this.ProductFields.categories = { id: product.category_id, name: product.cat_name };
            this.ProductFields.tax_cat_id = { id: product.tax_cat_id, name: product.tax_cat_name };
            this.ProductFields.vendor     = { id: product.vendor, name: product.vendor_name };
            this.ProductFields.salePrice  = product.sale_price;
            this.ProductFields.costPrice  = product.cost_price;
            this.isDisabled               = true;
        }

        this.loaded();
    },

    methods: {
        saveProduct() {
            if (!this.checkForm()) {
                return false;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            var type, url;

            if (!this.product) {
                type = 'post';
                url  = 'products';
            } else {
                type = 'put';
                url  = 'products/' + this.ProductFields.id;
            }

            var data = {
                name           : this.ProductFields.name,
                product_type_id: this.ProductFields.type,
                category_id    : this.ProductFields.categories,
                tax_cat_id     : this.ProductFields.tax_cat_id,
                vendor         : this.ProductFields.vendor,
                cost_price     : this.ProductFields.costPrice,
                sale_price     : this.ProductFields.salePrice
            };

            HTTP[type](url, data).then(response => {
                this.$parent.$emit('close');
                this.$parent.getProducts();
                this.resetForm();
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', type === 'put' ? 'Product Updated!' : 'Product Created!');
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
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
                    for (const i in response.data) {
                        var vendor = response.data[i];
                        var object = { id: vendor.id, name: vendor.first_name + ' ' + vendor.last_name };
                        this.vendors.push(object);
                    }
                }
            });
        },

        getCategories() {
            HTTP.get('product-cats').then(response => {
                this.categories = response.data;
            });
        },

        getTaxCategories() {
            HTTP.get('tax-cats').then(response => {
                this.tax_cats = response.data;
            });
        },

        getProductTypes() {
            HTTP.get('products/types').then(response => {
                this.productType = response.data;

                this.ProductFields.type = { id: parseInt(response.data[0].id), name: response.data[0].name };
            });
        },

        resetForm() {
            this.ProductFields.id         = null;
            this.ProductFields.name       = '';
            this.ProductFields.type       = [];
            this.ProductFields.categories = [];
            this.ProductFields.vendor     = [];
            this.ProductFields.costPrice  = '';
            this.ProductFields.salePrice  = '';
        },

        checkForm() {
            this.error_msg = [];

            if (
                this.ProductFields.name &&
                    this.ProductFields.type &&
                    this.ProductFields.vendor &&
                    this.ProductFields.salePrice
            ) {
                return true;
            }

            if (!this.ProductFields.name) {
                this.error_msg.push('Product name is required');
            }

            if (!this.ProductFields.type) {
                this.error_msg.push('Product type is required');
            }

            if (this.ProductFields.salePrice <= 0) {
                this.error_msg.push('Product sale price should be greater than 0');
            }

            if (!this.ProductFields.vendor) {
                this.error_msg.push('Vendor is required');
            }

            return false;
        }
    }
};
</script>

<style lang="less">
    #wperp-product-modal {
        .wperp-modal-header {
            padding: 30px 20px 20px !important;
        }

        .modal-close {
            top: 20px !important;
            .flaticon-close {
                font-size: inherit;
            }
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
