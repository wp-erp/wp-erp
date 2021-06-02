
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ inputItems[0].title }}</h3>
        <p class="sub-section-description">{{ inputItems[0].desc }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRFrontendForm">

            <div class="wperp-form-group">
                 <label> {{ inputItems[1].title }}</label>
                 <input v-model="fields[inputItems[1].id]" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ inputItems[1].desc  }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ inputItems[2].title }}</label>
                 <input v-model="fields[inputItems[2].id]" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ inputItems[2].desc  }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ inputItems[3].title }}</label>
                 <input type="file" class="wperp-form-field" />
            </div>

            <div class="wperp-form-group">
                <label>{{ inputItems[4].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields[inputItems[4].id]" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ inputItems[4].desc }}
                        </span>
                    </label>
                </div>
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
            inputItems: erp_settings_var.settings_hr_data['hr_frontend']
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
