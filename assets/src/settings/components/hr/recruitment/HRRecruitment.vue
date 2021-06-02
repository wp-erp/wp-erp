
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ inputItems[0].title }}</h3>
        <p class="sub-section-description">{{ inputItems[0].desc }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRRecruitmentForm">

            <div class="wperp-form-group">
                 <label> {{ inputItems[1].title }}</label>
                 <input v-model="fields[inputItems[1].id]" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ inputItems[1].desc }}</p>
            </div>

            <div class="wperp-form-group">
                <submit-button :text="__( 'Save Changes', 'erp' )" />
            </div>

        </form>
    </div>
  </div>
</template>

<script>
import SettingsSubMenu from 'settings/components/menu/SettingsSubMenu.vue';
import SubmitButton from 'settings/components/base/SubmitButton.vue';
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
      SettingsSubMenu,
      SubmitButton
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
