
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __('HR Frontend', 'erp') }}</h3>
        <p class="sub-section-description">{{ __('HRM frontend settings.', 'erp') }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRFrontendForm">

            <div class="wperp-form-group">
                 <label> {{ __("HR Dashbord Slug", 'erp') }}</label>
                 <input v-model="fields.hr_frontend_slug" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __('Your custom slug', 'erp') }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __("HR Dashbord Title", 'erp') }}</label>
                 <input v-model="fields.hr_frontend_title" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __('Your custom title', 'erp') }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __("HR Frontend Logo", 'erp') }}</label>
                 <input type="file" class="wperp-form-field" />
            </div>

            <div class="wperp-form-group">
                <label>{{ __('Redirect to frontend', 'erp') }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.hr_frontend_redirect" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ __('Check if you want to redirect to frontend', 'erp') }}
                        </span>
                    </label>
                </div>
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
  name: "HRFrontend",

  data(){
        return {
            fields: {
                hr_frontend_slug: '',
                hr_frontend_dashboard_title: '',
                hr_frontend_logo: '',
                hr_frontend_redirect: false
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
