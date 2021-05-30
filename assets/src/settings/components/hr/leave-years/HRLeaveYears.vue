
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __("Leave Years", "erp") }}</h3>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRLeaveYearsForm">

            <div class="wperp-row">
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12">
                    <label> {{ __("Name", 'erp') }}</label>
                    <input v-model="fields.name" class="wperp-form-field" />
                </div>
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12">
                    <label> {{ __("Start Date", 'erp') }}</label>
                    <date-picker class="wperp-form-field" :placeholder="__( 'Start date', 'erp' )"
                                v-model="fields.start_date" />
                </div>
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12">
                    <label> {{ __("End Date", 'erp') }}</label>
                    <date-picker class="wperp-form-field" :placeholder="__( 'End date', 'erp' )"
                                v-model="fields.end_date" />
                </div>
                <div class="wperp-form-group wperp-col-sm-12 wperp-col-xs-12">
                    <button class="wperp-btn btn-outline-default" type="button">+ Add New</button>
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
import DatePicker from 'settings/components/base/DatePicker.vue';
import SettingsSubMenu from 'settings/components/menu/SettingsSubMenu.vue';
import SubmitButton from 'settings/components/base/SubmitButton.vue';

export default {
  name: "HRLeaveYears",

  data(){
        return {
            fields: {
                name: '',
                start_date: '',
                end_date: '',
                years: []
            }
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton,
      DatePicker
  },

  methods: {
      submitHRLeaveYearsForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.showAlert('success', 'Leave years saved successfully !');

        this.$store.dispatch('spinner/setSpinner', false);
      }
  },

};
</script>
