
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ inputItems[0].title }}</h3>
        <p class="sub-section-description">{{ inputItems[0].desc }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitMiscellaneousForm">

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
      SettingsSubMenu,
      SubmitButton
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
