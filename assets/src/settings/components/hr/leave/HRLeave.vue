
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ inputItems[0].title }}</h3>
        <p class="sub-section-description">{{ inputItems[0].desc }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRLeaveForm">
            <div class="wperp-form-group" v-for="(item, index) in inputItems" :key="index">
                <div v-if="(index > 0) && (index <= 1)">
                    <label>{{ item.title }}</label>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input v-model="fields.enable_extra_leave" type="checkbox" class="form-check-input" :name="item.id">
                            <span class="form-check-sign">
                                <span class="check"></span>
                            </span>
                            <span class="form-check-label-light">
                                {{ item.desc }}
                            </span>
                        </label>
                    </div>
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
import { generateFormDataFromObject } from 'settings/utils/FormDataHandler';

var $ = jQuery;

export default {
  name: "HRLeave",

  data(){
        return {
            fields: {
                enable_extra_leave: false,
                erp_pro_accrual_leave: false,
                erp_pro_carry_encash_leave: false,
                erp_pro_half_leave: false,
                erp_pro_multilevel_approval: false,
                erp_pro_seg_leave: false,
                erp_pro_sandwich_leave: false
            },
            inputItems: erp_settings_var.settings_hr_data['leave']
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  created () {
      this.getSettingsLeavesData();
  },

  methods: {
      submitHRLeaveForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            enable_extra_leave: this.fields.enable_extra_leave,
            erp_pro_accrual_leave: this.fields.erp_pro_accrual_leave,
            erp_pro_carry_encash_leave: this.fields.erp_pro_carry_encash_leave,
            erp_pro_half_leave: this.fields.erp_pro_half_leave,
            erp_pro_multilevel_approval: this.fields.erp_pro_multilevel_approval,
            erp_pro_seg_leave: this.fields.erp_pro_seg_leave,
            erp_pro_sandwich_leave: this.fields.erp_pro_sandwich_leave,
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-leave-save'
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

      getSettingsLeavesData() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-leave-get-data'
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
                console.log('response: ', response.data);
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
