
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __('Grace Time', 'erp') }}</h3>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRFrontendForm">

            <div class="wperp-form-group">
                 <label> {{ __( 'Grace Before Checkin', 'erp-pro' ) }}</label>
                 <input v-model="fields.grace_before_checkin" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __( '(in minute) this time will not counted as overtime', 'erp-pro' ) }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __( 'Grace After Checkin', 'erp-pro' ) }}</label>
                 <input v-model="fields.grace_after_checkin" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __( '(in minute) this time will not counted as late', 'erp-pro' ) }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __( 'Threshhold between checkout & checkin', 'erp-pro' ) }}</label>
                 <input v-model="fields.erp_att_diff_threshhold" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __( '(in second) this time will prevent quick checkin after making a checkout', 'erp-pro' ) }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __( 'Grace Before Checkout', 'erp-pro' ) }}</label>
                 <input v-model="fields.grace_before_checkout" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __( '(in minute) this time will not counted as early left', 'erp-pro' ) }}</p>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __( 'Grace After Checkout', 'erp-pro' ) }}</label>
                 <input v-model="fields.grace_after_checkout" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __( '(in minute) this time will not counted as overtime', 'erp-pro' ) }}</p>
            </div>

            <div class="wperp-form-group">
                <label>{{ __( 'Self Attendance', 'erp-pro' ) }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.enable_self_att" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ __( 'Enable self attendance service for employees?', 'erp-pro' ) }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="wperp-form-group">
                <label>{{ __( 'IP Restriction', 'erp-pro' ) }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_at_enable_ip_restriction" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ __( 'Enable IP restriction for checkin/checkout', 'erp-pro' ) }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="wperp-form-group">
                 <label> {{ __( 'Grace After Checkout', 'erp-pro' ) }}</label>
                 <textarea cols="45" rows="4" v-model="fields.erp_at_whitelisted_ips" class="wperp-form-field" />
                 <p class="erp-form-input-hint">{{ __( 'Employees from this IP addresss will be able to self check-in. Put one IP in each line', 'erp-pro' ) }}</p>
            </div>

            <div class="wperp-form-group">
                <label>{{ __( 'Attendance Reminder', 'erp-pro' ) }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.attendance_reminder" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ __( 'Send email notification to remind Checking-in', 'erp-pro' ) }}
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
  name: "HRAttendance",

  data(){
        return {
            fields: {
                grace_before_checkin: 15,
                grace_after_checkin: 15,
                erp_att_diff_threshhold: 60,
                grace_before_checkout: 15,
                grace_after_checkout: 15,
                enable_self_att: false,
                erp_at_enable_ip_restriction: false,
                erp_at_whitelisted_ips: '',
                attendance_reminder: true
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
