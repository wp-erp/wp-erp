
<template>
    <base-layout section_id="erp-hr" sub_section_id="recruitment" :onFormSubmit="submitHRRecruitmentForm">
        <div class="wperp-form-group">
            <label> {{ inputItems[1].title }}</label>
            <input v-model="fields[inputItems[1].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[1].desc }}</p>
        </div>
    </base-layout>
</template>

<script>
import BaseLayout from 'settings/components/layouts/BaseLayout.vue';
import { generateFormDataFromObject } from 'settings/utils/FormDataHandler';

var $ = jQuery;

export default {
  name: "HRRecruitment",

  data(){
        return {
            fields: {
                recruitment_api_url: '',
            },
            inputItems: erp_settings_var.settings_hr_data['recruitment']
        }
  },

  mounted() {
      this.fields.recruitment_api_url = this.inputItems[1].default
  },

  created() {
    this.getSettingsRecruitmentData();
  },

  components: {
      BaseLayout
  },

  methods: {
      submitHRRecruitmentForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            recruitment_api_url: this.fields.recruitment_api_url,
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-save',
            module: 'hrm',
            section: 'recruitment'
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

      getSettingsRecruitmentData() {
          this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-rec-get-settings'
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
