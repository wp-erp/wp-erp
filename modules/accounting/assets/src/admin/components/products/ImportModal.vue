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
}
