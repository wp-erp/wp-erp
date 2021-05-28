
<template>
  <div>
    <h2 class="section-title">{{ __("HR Management", "erp") }}</h2>
    <settings-sub-menu></settings-sub-menu>

    <div class="settings-box">
        <h3 class="sub-section-title">{{ __("Work Days", "erp") }}</h3>
        <p class="sub-section-description">{{ __("Week day settings for this company.", "erp") }}</p>

        <form action="" class="wperp-form" method="post" @submit.prevent="submitHRWorkDaysForm">
            <div class="wperp-form-group">
                <div>
                    <label>
                        {{ __("Monday", "erp") }}
                    </label>

                </div>
            </div>
        </form>
    </div>
  </div>
</template>

<script>
import SettingsSubMenu from 'settings/components/menu/SettingsSubMenu.vue';

export default {
  name: "HRWorkDays",

  props: {},

  components: {
      SettingsSubMenu
  },

  methods: {
      submitHRWorkDaysForm() {

      }
  },

};
</script>
