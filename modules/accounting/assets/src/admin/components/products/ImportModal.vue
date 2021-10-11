<template>
    <div id="wperp-product-import-modal">
        <div class="wperp-container">
            <div id="wperp-import-customer-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
                <div class="wperp-modal-dialog">
                    <div class="wperp-modal-content">
                        <div class="wperp-modal-header">
                            <h3>{{ __('Import Products', 'erp') }}</h3>

                            <span class="modal-close">
                                <i class="flaticon-close" @click="$parent.$emit('close')"></i>
                            </span>
                        </div>

                        <form action="" enctype="multipart/form-data" method="post" class="modal-form edit-customer-modal" id="import_form" @submit.prevent="importCsv">
                            <div class="wperp-modal-body" id="erp-import-modal-body">
                                <div v-if="showError" class="notice notice-error erp-error-notice" id="erp-csv-import-error">
                                    <ul class="erp-list" v-if="isObject(errors)">
                                        <li v-for="(error, index) in errors" :key="index" v-html="error"></li>
                                    </ul>
                                    <span v-else>{{ errors }}</span>
                                </div>

                                <div :class="processingClass" id="erp-import-processing">
                                    <span v-if="isWorking" class="erp-loader"></span>
                                    <span v-if="isWorking" class="loading-text">{{ workingText }}...</span>
                                </div>

                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th>
                                                <label for="csv_file">{{ __( 'CSV File', 'erp' ) }} <span class="required">*</span></label>
                                            </th>
                                            <td>
                                                <input type="file" name="csv_file" id="csv_file" @change="processFile" required />

                                                <p class="description">
                                                    {{ __('Upload a csv file.', 'erp') }}
                                                    <span class="erp-help-tip .erp-tips" :title="__('Make sure CSV meets the sample CSV format exactly.', 'erp')"></span>
                                                </p>

                                                <p id="download_sample_wrap" v-if="sampleUrl">
                                                    <button class="wperp-btn btn--primary"
                                                        id="erp-employee-sample-csv"
                                                        @click.prevent="downloadSample">
                                                        {{ __( 'Download Sample CSV', 'erp' ) }}
                                                    </button>
                                                </p>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <label for="update_existing">{{ __( 'Update Existing Products', 'erp' ) }}</label>
                                            </th>

                                            <td>
                                                <input type="checkbox" v-model="updateExisting" name="update_existing" id="update_existing">
                                                {{ __('Existing products with same name will be updated', 'erp') }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <label for="default_product_cat">
                                                    {{ __( 'Default Product Category', 'erp' ) }}
                                                    <span class="required"> *</span>
                                                    <span
                                                        class="erp-help-tip .erp-tips"
                                                        :title="__('If product category is null or not found, this default category will be assigned.', 'erp')">
                                                    </span>
                                                </label>
                                            </th>

                                            <td>
                                                <multi-select
                                                    v-model="defaultProductCat"
                                                    required="required"
                                                    :options="productCategories"
                                                    :multiple="false"/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <label>{{ __( 'Default Product Type', 'erp' ) }}
                                                    <span class="required"> *</span>
                                                    <span
                                                        class="erp-help-tip .erp-tips"
                                                        :title="__('If product type is null or not found, this default type will be assigned.', 'erp')">
                                                    </span>
                                                </label>
                                            </th>

                                            <td>
                                                <multi-select
                                                    v-model="defaultProductType"
                                                    required="required"
                                                    :options="productTypes"
                                                    :multiple="false"/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <label for="default_vendor">{{ __( 'Default Vendor', 'erp' ) }}
                                                    <span class="erp-help-tip .erp-tips" :title="__('If product type is null or not found, this default type will be assigned.', 'erp')"></span>
                                                </label>
                                            </th>

                                            <td>
                                                <multi-select
                                                    v-model="defaultVendor"
                                                    :options="vendors"
                                                    :multiple="false"/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                <label for="default_tax_cat">{{ __( 'Default Tax category', 'erp' ) }}
                                                    <span class="erp-help-tip .erp-tips" :title="__('If product type is null or not found, this default type will be assigned.', 'erp')"></span>
                                                </label>
                                            </th>

                                            <td>
                                                <multi-select
                                                    v-model="defaultTaxCat"
                                                    :options="taxCategories"
                                                    :multiple="false"/>
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tbody v-if="showFieldMapping" id="erp-csv-fields-container">
                                        <tr v-for="(field, index) in fields" :key="index">
                                            <th>
                                                <label class="csv_field_labels">
                                                    {{ strTitleCase(field) }} <span v-if="isRequired(field)" class="required">*</span>
                                                </label>
                                            </th>
                                            <td>
                                                <multi-select
                                                    v-model="mappedValues[field]"
                                                    :required="isRequired(field)"
                                                    :options="fieldOptions"
                                                    :multiple="false"/>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="wperp-modal-footer pt-0">
                                <!-- buttons -->
                                <div class="buttons-wrapper text-right">
                                    <button class="wperp-btn btn--default modal-close" @click="$parent.$emit('close')" type="reset">{{ __('Cancel', 'erp') }}</button>
                                    <button class="wperp-btn btn--primary" type="submit">{{ __('Import', 'erp') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import HTTP from 'admin/http';
import MultiSelect from '../select/MultiSelect.vue'

export default {
    name: 'ImportModal',

    components: {
        MultiSelect,
    },

    data() {
        return {
            type               : 'product',
            errors             : '',
            showError          : false,
            productCategories  : [],
            productTypes       : [],
            taxCategories      : [],
            vendors            : [],
            defaultProductCat  : {
                id   : '',
                name : '',
            },
            defaultProductType : {
                id   : '',
                name : '',
            },
            defaultTaxCat      : {
                id   : '',
                name : '',
            },
            defaultVendor      : {
                id   : '',
                name : '',
            },
            csvFile            : '',
            fields             : [],
            reqFields          : [],
            mappedValues       : {},
            fieldOptions       : [],
            showFieldMapping   : false,
            updateExisting     : '',
            sampleUrl          : '',
            isWorking          : false,
            workingText        : '',
            processingClass    : ''
        };
    },

    created() {
        this.mapFields()
        this.getVendors();
        this.getCategories();
        this.getTaxCategories();
        this.getProductTypes();
        this.generateCsvUrl();
    },

    methods: {
        importCsv() {
            this.errors    = '';
            this.showError = false;
            let formData   = new FormData();

            if ( ! this.defaultProductCat.id ) {
                return this.showAlert('error', __('Please select a default product category', 'erp') );
            }

            if ( ! this.defaultProductType.id ) {
                return this.showAlert('error', __('Please select a default product type', 'erp') );
            }

            this.manageProgressStatus( __('Validating data', 'erp') );

            formData.append( 'csv_file', this.csvFile );
            formData.append( 'type', this.type );
            formData.append( 'category_id', this.defaultProductCat.id );
            formData.append( 'product_type_id', this.defaultProductType.id );
            formData.append( 'tax_cat_id', this.defaultTaxCat.id );
            formData.append( 'vendor', this.defaultVendor.id );
            formData.append( 'update_existing', this.updateExisting ? 1 : 0 );

            Object.keys( this.mappedValues ).forEach(key => {
                formData.append( `fields[${key}]`, this.mappedValues[key].id );
            })

            HTTP.post(
                'products/csv/validate',
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data;' + 'boundary=' + Math.random().toString().substr(2)
                    }
                }
            ).then(response => {
                this.manageProgressStatus( __('Importing data', 'erp') );

                HTTP.post(
                    'products/csv/import',
                    {
                        items  : response.data.data,
                        total  : response.data.total,
                        update : response.data.update,
                    }
                ).then(response => {
                    this.$root.$emit('imported-products');
                    this.$store.dispatch('spinner/setSpinner', false);
                    this.showAlert('success', __(`${response.data} products have been imported successfully`, 'erp') );

                    this.manageProgressStatus();
                }).catch(error => {
                    this.manageProgressStatus();
                    this.showImportError( error.response.data.message );

                    jQuery( "#import_form > div" ).animate( {
                        scrollTop: 0
                    }, 'fast' );
                });
            }).catch(error => {
                this.manageProgressStatus();
                this.showImportError( error.response.data.message );

                jQuery( "#import_form > div" ).animate( {
                    scrollTop: 0
                }, 'fast' );
            });
        },

        processFile(event) {
            this.errors    = '';
            this.showError = false;
            this.csvFile   = event.target.files[0];
            let reader     = new FileReader();

            reader.readAsText(this.csvFile.slice(0, 5000));

            reader.onload = (e) => {
                let csv             = reader.result;
                let lines           = csv.split('\n');
                let columnNamesLine = lines[0];
                let columnNames     = columnNamesLine.split(',');

                columnNames.forEach((name, index) => {
                    this.fieldOptions[index] = {
                        id: index,
                        name: name.replace(/"/g, ""),
                    };
                });

                this.showFieldMapping = true;
            }
        },

        mapFields() {
            let erpFields = erp_acct_var.erp_fields;

            if ( erpFields[this.type] !== undefined ) {
                this.fields    = erpFields[this.type].fields;
                this.reqFields = erpFields[this.type].required_fields;

                this.fields.forEach((field, index) => {
                    this.mappedValues[field] = {
                        id: index,
                        name: this.strTitleCase(field),
                    }
                });
            }
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
                this.productCategories = response.data;
            });
        },

        getTaxCategories() {
            HTTP.get('tax-cats').then(response => {
                this.taxCategories = response.data;
            });
        },

        getProductTypes() {
            HTTP.get('products/types').then(response => {
                this.productTypes = response.data;
            });
        },

        strTitleCase(string) {
            var str = string.replace(/_/g, ' ');

            return str.toLowerCase().split(' ').map(function (word) {
                return (word.charAt(0).toUpperCase() + word.slice(1));
            }).join(' ');
        },

        isRequired(field) {
            return this.reqFields.includes(field);
        },

        generateCsvUrl() {
            this.sampleUrl = `${erp_acct_var.admin_url}?page=erp-accounting&action=download_sample&type=product&_wpnonce=${erp_acct_var.export_import_nonce}#${this.$route.path}`;
        },

        downloadSample() {
            window.location.href = this.sampleUrl;
        },

        showImportError(error) {
            this.errors    = error;
            this.showError = true;

            document.getElementById('erp-import-modal-body').scrollIntoView({ behavior: "smooth" });
        },

        manageProgressStatus(text) {
            document.getElementById('erp-import-processing').scrollIntoView();

            this.processingClass = text ? 'import-processing' : '';
            this.isWorking       = text ? true : false;
            this.workingText     = text;
        },

        isObject(item) {
            return typeof item === 'object' || typeof item === 'array';
        },
    },
};
</script>

<style lang="less" scoped>
    .modal-close {
        .flaticon-close {
            font-size: inherit;
            line-height: 1.8;
        }
    }

    .errors {
        margin: 0 20px;
        color: #f44336;

        li {
            background: #f3f3f3;
            padding: 2px 10px;
        }
    }

    select {
        width: -webkit-fill-available !important;
    }

    #erp-csv-import-error {
        font-size: 16px !important;
        padding: 5px !important;
    }

    .erp-loader {
        height: 30px;
        width: 30px;
        background-size: 30px;
    }

    .import-processing {
        background-color: rgba(255, 255, 146, 0.911);
        padding: 7px;
        margin: 5px 0;
        border-radius: 4px;
        position: fixed;
        max-width: 656px;
        width: 80%;
        z-index: 1000;

        .loading-text {
            margin-left: 50px;
            font-size: 28px;
            font-weight: 200;
            line-height: 1.1;
        }
    }

    #update_existing {
        border: 0.5px solid rgb(219, 219, 219) !important;
        height: 20px !important;
        width: 20px !important;

        &::before {
            height: 2rem !important;
            width: 1.55rem !important;
        }
    }
</style>
