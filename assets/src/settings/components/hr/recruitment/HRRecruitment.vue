
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __('Recruitment', 'erp') }}</h3>
        <p class="sub-section-description">{{ __('HRM Recruitment settings.', 'erp') }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRRecruitmentForm">

            <div class="wperp-form-group">
                 <label> {{ __("Global Api", 'erp') }}</label>
                 <input v-model="fields.recruitment_api_url" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __('Third party API', 'erp') }}</p>
            </div>

            <div class="wperp-form-group">
                <submit-button text="Save Changes" />
            </div>

        </form>
    </div>
  </div>
</template>

<script>
import SettingsSubMenu from 'settings/components/menu/SettingsSubMenu.vue';
import SubmitButton from 'settings/components/base/SubmitButton.vue';

export default {
  name: "HRRecruitment",

  data(){
        return {
            fields: {
                recruitment_api_url: '',
            },
            inputItems: erp_settings_var.settings_hr_data
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  methods: {
      submitHRRecruitmentForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.showAlert('success', 'Recruitment settings saved successfully !');

        this.$store.dispatch('spinner/setSpinner', false);
      }
  },

};
</script>
