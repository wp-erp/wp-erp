
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __('Payroll', 'erp') }}</h3>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRFrontendForm">

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
  name: "HRPayroll",

  data(){
        return {
            fields: {
                hr_frontend_slug: '',
            },
            inputItems: erp_settings_var.settings_hr_data
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  methods: {
      submitHRFrontendForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.showAlert('success', 'HR Frontend saved successfully !');

        this.$store.dispatch('spinner/setSpinner', false);
      }
  },

};
</script>
