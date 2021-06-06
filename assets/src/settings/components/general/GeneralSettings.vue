<template>
    <base-layout section_id="general" sub_section_id="general" :onFormSubmit="submitGeneralForm">
        <div class="wperp-form-group">
            <div>
                <label>
                    {{ inputItems[1].title }}
                    <tooltip :text="inputItems[1].desc" />
                </label>
                <date-picker class="wperp-form-field" :placeholder="__( 'Select date', 'erp' )"
                        v-model="general_fields.gen_com_start" />
            </div>
        </div>

        <div class="wperp-form-group">
            <label>
                {{ inputItems[2].title }}
                <tooltip :text="inputItems[2].desc" />
            </label>
            <select v-model="general_fields.gen_financial_month" class="wperp-form-field">
                <option :key="index"
                    v-for="(item, key, index ) in inputItems[2].options"
                    :value="key">{{item}}
                </option>
            </select>
        </div>

        <div class="wperp-form-group">
            <label>
                {{ inputItems[3].title }}
                <tooltip :text="inputItems[3].desc" />
            </label>
            <select v-model="general_fields.date_format" class="wperp-form-field">
                <option :key="index"
                    v-for="(item, key, index ) in inputItems[3].options"
                    :value="key">{{item}}
                </option>
            </select>
        </div>

        <div class="wperp-form-group">
            <label>{{ inputItems[4].title }}</label>
            <multi-select
                v-model="general_fields.erp_currency"
                :options="currenciesOptions"
                >
            </multi-select>
        </div>

        <div class="wperp-form-group">
            <label>{{ inputItems[5].title }}</label>
            <multi-select
                v-model="general_fields.erp_country"
                :options="countriesOptions"
                >
            </multi-select>
        </div>

        <div class="wperp-form-group">
            <label>
                {{ inputItems[6].title }}
                <tooltip :text="inputItems[6].desc" />
            </label>
            <select v-model="general_fields.role_based_login_redirection" class="wperp-form-field">
                <option :key="index"
                    v-for="(item, key, index ) in inputItems[6].options"
                    :value="key">{{item}}
                </option>
            </select>
        </div>

        <div class="wperp-form-group">
            <label>
                {{ inputItems[7].title }}
                <tooltip :text="inputItems[7].desc" />
            </label>
            <select v-model="general_fields.erp_debug_mode" class="wperp-form-field">
                <option :key="index"
                    v-for="(item, key, index ) in inputItems[7].options"
                    :value="key">{{item}}
                </option>
            </select>
        </div>
    </base-layout>
</template>

<script>
import DatePicker from 'settings/components/base/DatePicker.vue';
import Tooltip from 'settings/components/base/Tooltip.vue';
import BaseLayout from 'settings/components/layouts/BaseLayout.vue';
import MultiSelect from 'settings/components/select/MultiSelect.vue';
import { generateFormDataFromObject } from 'settings/utils/FormDataHandler';

var $ = jQuery;

export default {
    name: 'GeneralSettings',

    components: {
        DatePicker,
        Tooltip,
        BaseLayout,
        MultiSelect
    },

    data(){
        return {
            general_fields: {
                gen_com_start: '',
                gen_financial_month: '',
                date_format: '',
                erp_currency: '',
                erp_country: '',
                role_based_login_redirection: '0',
                erp_debug_mode: '0'
            },
            inputItems: erp_settings_var.settings_general_data
        }
    },

    created() {
        this.getSettingsGeneralData();
    },

    methods: {
        submitGeneralForm() {

            this.$store.dispatch('spinner/setSpinner', true);

            this.general_fields.erp_currency = this.general_fields.erp_currency ? this.general_fields.erp_currency.id : null;
            this.general_fields.erp_country = this.general_fields.erp_country ? this.general_fields.erp_country.id : null;

            let requestData = window.settings.hooks.applyFilters('requestData', {
                ...this.general_fields,
                _wpnonce: erp_settings_var.nonce,
                action: 'erp-settings-save',
                module: 'general',
                section: ''
            });

            const postData = generateFormDataFromObject( requestData );
            const that     = this;

            $.ajax({
                url: erp_settings_var.ajax_url,
                type: 'POST',
                data: postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    that.$store.dispatch('spinner/setSpinner', false);

                    if (response.success) {
                        that.showAlert('success', response.data.message);
                    } else {
                        that.showAlert('error', __('Something went wrong !', 'erp'));
                    }
                }
            });
        },

        getSettingsGeneralData() {
            this.$store.dispatch('spinner/setSpinner', true);

            let requestData = window.settings.hooks.applyFilters('requestData', {
                _wpnonce: erp_settings_var.nonce,
                action: 'erp-settings-get-general-data'
            });

            const postData = generateFormDataFromObject( requestData );
            const that     = this;

            $.ajax({
                url: erp_settings_var.ajax_url,
                type: 'POST',
                data: postData,
                processData: false,
                contentType: false,
                success: function (response) {
                    that.$store.dispatch('spinner/setSpinner', false);

                    if (response.success) {
                        that.general_fields = response.data;
                    }
                }
            });
        },
    },

    computed: {
        currenciesOptions : function () {
            var currencies = this.inputItems[4].options;
            var keys       = Object.keys(currencies);
            var data       = [];

            keys.forEach((key) => {
                const currency = {
                    id  : key,
                    name: currencies[key]
                }

                data.push(currency);

                if( key === this.general_fields.erp_currency ) {
                    this.general_fields.erp_currency = currency;
                }
            });
            return data;
        },

        countriesOptions : function () {
            var countries = this.inputItems[5].options;
            var keys      = Object.keys(countries);
            var data      = [];

            keys.forEach((key) => {
                const country = {
                    id  : key,
                    name: countries[key]
                };

                data.push(country);

                if( key === this.general_fields.erp_country ) {
                    this.general_fields.erp_country = country;
                }
            });

            return data;
        }
    }
}
</script>
