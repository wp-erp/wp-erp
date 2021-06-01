<template>
  <div class="settings-submenu-navbar">
    <ul class="settings-sub-menu">
      <li>
        <router-link tag="li" :to="'/erp-hr/workdays'" :class="(this.$route.name === 'HRWorkDays') ? 'router-link-active' : ''">
          <a href="#">
            <span class="menu-name">{{ __("Workdays", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/erp-hr/leave'" >
          <a href="#">
            <span class="menu-name">{{ __("Leave", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/erp-hr/leave-years'" >
          <a href="#">
            <span class="menu-name">{{ __("Leave Years", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/erp-hr/miscellaneous'" >
          <a href="#">
            <span class="menu-name">{{ __("Miscellaneous", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/erp-hr/hr-frontend'" >
          <a href="#">
            <span class="menu-name">{{ __("HR Frontend", "erp") }}</span>
          </a>
        </router-link>

      <li>
        <router-link tag="li" :to="'/erp-hr/recruitment'" >
          <a href="#">
            <span class="menu-name">{{ __("Recruitment", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <!-- <li class="dropdown-nav">
        <a href="#" class="">{{ __('More', 'erp') }}</a>
        <ul class="erp-nav-dropdown">
            <li><a href="#/erp-hr/payroll" class="">Payroll</a></li>
        </ul>
     </li> -->

      <!-- <li>
        <router-link tag="li" :to="'/erp-hr/payroll'" >
          <a href="#">
            <span class="menu-name">{{ __("Payroll", "erp") }}</span>
          </a>
        </router-link>
      </li> -->

      <li>
        <router-link tag="li" :to="'/erp-hr/attendance'" >
          <a href="#">
            <span class="menu-name">{{ __("Attendance", "erp") }}</span>
          </a>
        </router-link>
      </li>

    </ul>
  </div>
</template>

<script>
export default {
  name: "SettingsSubMenu",

  props: {},

  data() {
    return {

    };
  },

  created() {

  },

  mounted() {

  },

  methods: {

  },
};
</script>
