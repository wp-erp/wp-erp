
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __("Leave Years", "erp") }}</h3>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRLeaveYearsForm">
            <div class="wperp-row">
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("Name", 'erp') }}</label>
                </div>
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("Start Date", 'erp') }}</label>
                </div>
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <label> {{ __("End Date", 'erp') }}</label>
                </div>
            </div>

            <div class="wperp-row" v-for="(year, index) in years_data" :key="index">
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <input v-model="year.fy_name" class="wperp-form-field" />
                </div>
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <date-picker class="wperp-form-field" :placeholder="__( 'Start date', 'erp' )" v-model="year.start_date" />
                </div>
                <div class="wperp-form-group wperp-col-sm-4 wperp-col-xs-12 margin-bottom-10">
                    <date-picker class="wperp-form-field" :placeholder="__( 'End date', 'erp' )" v-model="year.end_date" />
                    <span v-if="(year.id === null || year.id === '' )" class="settings-btn-cancel" @click="deleteYear(index)">x</span>
                </div>
            </div>

            <div class="wperp-form-group">
                <button class="wperp-btn wperp-btn-default" type="button" @click="addNewYear">+ Add New</button>
            </div>

            <div class="wperp-form-group">
                <submit-button :text="__( 'Save Changes', 'erp' )" />
            </div>

        </form>
    </div>
  </div>
</template>

<script>
import DatePicker from 'settings/components/base/DatePicker.vue';
import SettingsSubMenu from 'settings/components/menu/SettingsSubMenu.vue';
import SubmitButton from 'settings/components/base/SubmitButton.vue';
import { generateFormDataFromObject } from 'settings/utils/FormDataHandler';

var $ = jQuery;

export default {
  name: "HRLeaveYears",

  data(){
        return {
            years_data: [
                {
                    fy_name: '',
                    description: 'Year for leave',
                    start_date: '',
                    end_date: ''
                }
            ]
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton,
      DatePicker
  },

  created() {
      this.getFinancialYearsData();
  },

  methods: {
      submitHRLeaveYearsForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            fyears: this.years_data,
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-financial-years-save'
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
                    that.getFinancialYearsData();
                } else {
                    that.showAlert('error', __(response.data, 'erp'));
                }
            }
        });
      },

      addNewYear() {
          this.years_data.push({
            fy_name: '',
            description: 'Year for leave',
            start_date: '',
            end_date: '',
            id: null
          });
      },

      deleteYear( index ) {
          this.years_data.splice(index, 1);
      },

      getFinancialYearsData() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-get-hr-financial-years'
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
                    if(response.data.length > 0) {
                        that.years_data = [];
                        response.data.forEach((item) => {
                            item.start_date = that.formatDateFromTimestamp(item.start_date);
                            item.end_date = that.formatDateFromTimestamp(item.end_date);

                            that.years_data.push(item);
                        });
                    }
                }
            }
        });
      },

      formatDateFromTimestamp( timestamp ) {
          if( timestamp === null || timestamp === "") {
            return "";
          }

          return new Date(timestamp * 1e3).toISOString().slice(0, 10);
      }
  },

};
</script>
