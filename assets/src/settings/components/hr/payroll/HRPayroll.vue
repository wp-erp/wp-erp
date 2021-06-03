
<template>
      <base-layout section_id="erp-hr" sub_section_id="payroll" :onFormSubmit="submitHRPayrollForm">
      </base-layout>
</template>

<script>
import BaseLayout from 'settings/components/layouts/BaseLayout.vue';

export default {
  name: "HRPayroll",

  data(){
        return {
            fields: {
                hr_frontend_slug: '',
            },
            inputItems: erp_settings_var.settings_hr_data
        }
  },

  components: {
      BaseLayout
  },

  methods: {
      submitHRPayrollForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.showAlert('success', 'HR Payroll saved successfully !');

        this.$store.dispatch('spinner/setSpinner', false);
      }
  },

};
</script>
