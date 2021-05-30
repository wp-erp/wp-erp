
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ inputItems[0].title }}</h3>
        <p class="sub-section-description">{{ inputItems[0].desc }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRLeaveForm">

            <div class="wperp-form-group">
                <label>{{ inputItems[1].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_hrm_remove_wp_user" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ inputItems[1].desc }}
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
  name: "HRMiscellaneous",

  data(){
        return {
            fields: {
                enable_extra_leave: false,
            },
            inputItems: erp_settings_var.settings_hr_data['miscellaneous']
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  methods: {
      submitHRLeaveForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.showAlert('success', 'Miscellaneous saved successfully !');

        this.$store.dispatch('spinner/setSpinner', false);
      }
  },

};
</script>
