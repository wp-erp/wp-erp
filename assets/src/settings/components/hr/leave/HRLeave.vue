
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
                        <input v-model="fields.enable_extra_leave" type="checkbox" class="form-check-input" >
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
                <label>{{ inputItems[2].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_pro_accrual_leave" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ inputItems[2].desc }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="wperp-form-group">
                <label>{{ inputItems[3].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_pro_carry_encash_leave" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ inputItems[3].desc }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="wperp-form-group">
                <label>{{ inputItems[4].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_pro_half_leave" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ inputItems[4].desc }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="wperp-form-group">
                <label>{{ inputItems[5].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_pro_multilevel_approval" type="checkbox" class="form-check-input" >
                        <span class="form-check-sign">
                            <span class="check"></span>
                        </span>
                        <span class="form-check-label-light">
                            {{ inputItems[5].desc }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="wperp-form-group">
                <label>{{ inputItems[6].title }}</label>
                <div class="form-check">
                    <label class="form-check-label">
                        <input v-model="fields.erp_pro_seg_leave" type="checkbox" class="form-check-input" >
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
                        <input v-model="fields.erp_pro_sandwich_leave" type="checkbox" class="form-check-input" >
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
  name: "HRLeave",

  data(){
        return {
            fields: {
                enable_extra_leave: true,
                erp_pro_accrual_leave: true,
                erp_pro_carry_encash_leave: true,
                erp_pro_half_leave: true,
                erp_pro_multilevel_approval: true,
                erp_pro_seg_leave: true,
                erp_pro_sandwich_leave: false
            },
            inputItems: erp_settings_var.settings_hr_data['leave']
        }
  },

  components: {
      SettingsSubMenu,
      SubmitButton
  },

  created() {
    console.log(`inputItems`, this.inputItems);
  },

  methods: {
      submitHRLeaveForm() {
        this.$store.dispatch('spinner/setSpinner', true);

        this.showAlert('success', 'Leave saved successfully !');

        this.$store.dispatch('spinner/setSpinner', false);
      }
  },

};
</script>
