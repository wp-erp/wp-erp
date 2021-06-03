
<template>
    <base-layout section_id="erp-hr" sub_section_id="miscellaneous" :onFormSubmit="submitMiscellaneousForm">
        <div class="wperp-form-group">
            <label>{{ inputItems[1].title }}</label>
            <div class="form-check">
                <label class="form-check-label">
                    <input v-model="fields.erp_hrm_remove_wp_user" type="checkbox" class="form-check-input" :id="inputItems[1].id">
                    <span class="form-check-sign">
                        <span class="check"></span>
                    </span>
                    <span class="form-check-label-light">
                        {{ inputItems[1].desc }}
                    </span>
                </label>
            </div>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from 'settings/components/layouts/BaseLayout.vue';
import { generateFormDataFromObject } from 'settings/utils/FormDataHandler';

var $ = jQuery;

export default {
  name: "HRMiscellaneous",

  data(){
        return {
            fields: {
                erp_hrm_remove_wp_user: false,
            },
            inputItems: erp_settings_var.settings_hr_data['miscellaneous']
        }
  },

  components: {
      BaseLayout
  },

  created() {
    this.getSettingsMiscellaneousData();
  },

  methods: {
      submitMiscellaneousForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            erp_hrm_remove_wp_user: ! this.fields.erp_hrm_remove_wp_user ? null : true,
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-save',
            module: 'hrm',
            section: 'miscellaneous'
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

      getSettingsMiscellaneousData() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-miscellaneous-get-data'
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
                    that.fields = response.data;
                }
            }
        });
      }
  },

};
</script>
