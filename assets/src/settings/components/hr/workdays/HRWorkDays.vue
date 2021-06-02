
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ inputItems[0].title }}</h3>
        <p class="sub-section-description">{{ inputItems[0].desc }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRWorkDaysForm">

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[1].title }}
                </label>
                <select v-model="fields.mon" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[1].options"
                        :value="key">{{item}}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[2].title }}
                </label>
                <select v-model="fields.tue" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[2].options"
                        :value="key">{{item}}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[3].title }}
                </label>
                <select v-model="fields.wed" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[3].options"
                        :value="key">{{item}}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[4].title }}
                </label>
                <select v-model="fields.thu" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[4].options"
                        :value="key">{{item}}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[5].title }}
                </label>
                <select v-model="fields.fri" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[5].options"
                        :value="key">{{item}}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[6].title }}
                </label>
                <select v-model="fields.sat" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[6].options"
                        :value="key">{{item}}
                    </option>
                </select>
            </div>

            <div class="wperp-form-group">
                <label>
                    {{ inputItems[7].title }}
                </label>
                <select v-model="fields.sun" class="wperp-form-field">
                    <option :key="index"
                        v-for="(item, key, index ) in inputItems[7].options"
                        :value="key">{{item}}
                    </option>
                </select>
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
  name: "HRWorkDays",

  data(){
        return {
            fields: {
                mon: '8',
                tue: '8',
                wed: '8',
                thu: '8',
                fri: '8',
                sat: '0',
                sun: '0'
            },
            inputItems: erp_settings_var.settings_hr_data['workdays']
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  created() {
    this.getSettingsWorkDaysData();
  },

  methods: {
      submitHRWorkDaysForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestDataPost = {};

        this.inputItems.forEach(item => {
            requestDataPost[item.id] = this.fields[item.id];
        });

        let requestData = {
            ...requestDataPost,
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-save',
            module: 'hrm',
            section: 'workdays'
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

      getSettingsWorkDaysData() {
        this.$store.dispatch('spinner/setSpinner', true);

        let requestData = window.settings.hooks.applyFilters('requestData', {
            _wpnonce: erp_settings_var.nonce,
            action: 'erp-settings-workdays-get-data'
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
