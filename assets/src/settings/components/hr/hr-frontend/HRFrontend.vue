
<template>
  <base-layout section_id="erp-hr" sub_section_id="hr_frontend" :onFormSubmit="submitHRFrontendForm">
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
  </base-layout>
</template>

<script>
import BaseLayout from 'settings/components/layouts/BaseLayout.vue';
import { generateFormDataFromObject } from 'settings/utils/FormDataHandler';

var $ = jQuery;

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
      BaseLayout
  },

  created() {
    //   console.log('inputItems', this.inputItems);
  },

  methods: {
      submitHRFrontendForm() {
        this.$store.dispatch('spinner/setSpinner', true);
        let requestDataPost = {};

        this.inputItems.forEach(item => {
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
            section: 'hr_frontend'
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
  },

};
</script>
