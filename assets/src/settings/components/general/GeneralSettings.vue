<template>
    <div>
        <h2 class="section-title">General</h2>

        <div class="settings-box">
            <h3 class="sub-section-title">General Options</h3>

             <form action="" class="wperp-form" method="post" @submit.prevent="submitGeneralForm">
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
                    <!-- <select v-model="general_fields.erp_country" class="wperp-form-field">
                        <option :key="index"
                            v-for="(item, key, index ) in inputItems[5].options"
                            :value="key">{{item}}
                        </option>
                    </select> -->
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

                <div class="wperp-form-group">
                    <submit-button text="Save Changes" />
                </div>

            </form>
        </div>
    </div>
</template>

<script>
import DatePicker from 'settings/components/base/DatePicker.vue';
import Tooltip from 'settings/components/base/Tooltip.vue';
import SubmitButton from 'settings/components/base/SubmitButton.vue';
import MultiSelect from 'settings/components/select/MultiSelect.vue';
import HTTP from 'settings/http';

var $ = jQuery;

export default {
    name: 'GeneralSettings',

    components: {
        DatePicker,
        Tooltip,
        SubmitButton,
        MultiSelect
    },

    data(){
        return {
            general_fields: {
                gen_com_start: '',
                gen_financial_month: '',
                date_format: '',
                erp_currency: '1',
                erp_country: '',
                role_based_login_redirection: '0',
                erp_debug_mode: '0'
            },
            inputItems: erp_settings_var.settings_general_data
        }
    },

    created() {
        this.$store.dispatch('spinner/setSpinner', false);
    },

    methods: {
        submitGeneralForm() {

            this.$store.dispatch('spinner/setSpinner', true);

            let requestData = window.settings.hooks.applyFilters('requestData', {
                gen_com_start: this.general_fields.gen_com_start,
                gen_financial_month: this.general_fields.gen_financial_month,
                date_format: this.general_fields.date_format,
                erp_currency: this.general_fields.erp_currency ? this.general_fields.erp_currency.id : null,
                erp_country: this.general_fields.erp_country ? this.general_fields.erp_country.id : null,
                role_based_login_redirection: this.general_fields.role_based_login_redirection,
                erp_debug_mode: this.general_fields.erp_debug_mode,
                _wpnonce: erp_settings_var.nonce
            });

            var formData = new FormData();
            formData.append('gen_com_start', requestData.gen_com_start);
            formData.append('gen_financial_month', requestData.gen_financial_month);
            formData.append('date_format', requestData.date_format);
            formData.append('erp_currency', requestData.erp_currency);
            formData.append('erp_country', requestData.erp_country);
            formData.append('role_based_login_redirection', requestData.role_based_login_redirection);
            formData.append('erp_debug_mode', requestData.erp_debug_mode);
            formData.append('_wpnonce', requestData._wpnonce);
            formData.append('action', erp_settings_var.action);

            $.ajax({
                url: erp_settings_var.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(`response data`, response);

                    if (response.success) {
                        // Success
                        // console.log('Success response:', response);
                        alert('success');
                    } else {
                        // Errors
                        // console.log('Error response:', response);
                        alert('error');
                    }
                }
            });
            this.$store.dispatch('spinner/setSpinner', false);
        }
    },

    computed: {
        currenciesOptions : function () {
            var currencies = this.inputItems[4].options;
            var keys       = Object.keys(currencies);
            var data       = [];

            keys.forEach((key) => {
                data.push({
                    id  : key,
                    name: currencies[key]
                });
            });

            return data;
        },

        countriesOptions : function () {
            var countries = this.inputItems[5].options;
            var keys       = Object.keys(countries);
            var data       = [];

            keys.forEach((key) => {
                data.push({
                    id  : key,
                    name: countries[key]
                });
            });

            return data;
        }
  }
};
</script>
