
<template>
    <base-layout section_id="erp-hr" sub_section_id="attendance" :onFormSubmit="submitHRAttendanceForm">
        <div class="wperp-form-group">
            <label> {{ inputItems[1].title }}</label>
            <input v-model="fields[inputItems[1].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[1].desc }}</p>
        </div>

        <div class="wperp-form-group">
            <label> {{ inputItems[2].title }}</label>
            <input v-model="fields[inputItems[2].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[2].desc }}</p>
        </div>

        <div class="wperp-form-group">
            <label> {{ inputItems[3].title }}</label>
            <input v-model="fields[inputItems[3].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[3].desc }}</p>
        </div>

        <div class="wperp-form-group">
            <label> {{ inputItems[4].title }}</label>
            <input v-model="fields[inputItems[4].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[4].desc }}</p>
        </div>

        <div class="wperp-form-group">
            <label> {{ inputItems[5].title }}</label>
            <input v-model="fields[inputItems[5].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[5].desc }}</p>
        </div>

        <div class="wperp-form-group">
            <label>{{ inputItems[6].title }}</label>
            <div class="form-check">
                <label class="form-check-label">
                    <input v-model="fields[inputItems[6].id]" type="checkbox" class="form-check-input" >
                    <span class="form-check-sign">
                        <span class="check"></span>
                    </span>
                    <span class="form-check-label-light">
                        {{ inputItems[6].desc }}
                    </span>
                </label>
            </div>
        </div>

        <div class="wperp-form-group">
            <label>{{ inputItems[7].title }}</label>
            <div class="form-check">
                <label class="form-check-label">
                    <input v-model="fields[inputItems[7].id]" type="checkbox" class="form-check-input" >
                    <span class="form-check-sign">
                        <span class="check"></span>
                    </span>
                    <span class="form-check-label-light">
                        {{ inputItems[7].desc }}
                    </span>
                </label>
            </div>
        </div>

        <div class="wperp-form-group">
            <label> {{ inputItems[8].title }}</label>
            <textarea cols="45" rows="4" v-model="fields[inputItems[8].id]" class="wperp-form-field" />
            <p class="erp-form-input-hint">{{ inputItems[8].desc }}</p>
        </div>

        <div class="wperp-form-group">
            <label>{{ inputItems[9].title }}</label>
            <div class="form-check">
                <label class="form-check-label">
                    <input v-model="fields[inputItems[9].id]" type="checkbox" class="form-check-input" >
                    <span class="form-check-sign">
                        <span class="check"></span>
                    </span>
                    <span class="form-check-label-light">
                        {{ inputItems[9].desc }}
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
  name: "HRAttendance",

  data(){
        return {
            fields: {
                grace_before_checkin: '',
                grace_after_checkin: '',
                erp_att_diff_threshhold: '',
                grace_before_checkout: '',
                grace_after_checkout: '',
                enable_self_att: false,
                erp_at_enable_ip_restriction: false,
                erp_at_whitelisted_ips: '',
                attendance_reminder: false
            },
            inputItems: erp_settings_var.settings_hr_data['attendance']
        }
  },

  components: {
      BaseLayout
  },

  created () {
      this.getSettingsAttendanceData();
  },

  methods: {
      submitHRAttendanceForm() {
        this.$store.dispatch('spinner/setSpinner', true);
        let requestDataPost = {};

        this.inputItems.forEach((item) => {
            requestDataPost[item.id] = this.fields[item.id];
            if ( requestDataPost[item.id] === false ) {
                requestDataPost[item.id] = null;
            }
        });

        let requestData = {
            ...requestDataPost,
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-save',
            module: 'hrm',
            section: 'attendance'
        }

        requestData = window.settings.hooks.applyFilters('requestData', requestData);

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

      getSettingsAttendanceData() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-att-get-settings-data'
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
  }

};
</script>
