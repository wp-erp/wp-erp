<template>
  <div class="settings-submenu-navbar">
    <ul class="settings-sub-menu">
      <li>
        <router-link tag="li" :to="'/hr/workdays'" class="active">
          <a href="#">
            <span class="menu-name">{{ __("Workdays", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/hr/leave'" >
          <a href="#">
            <span class="menu-name">{{ __("Leave", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/hr/leave-years'" >
          <a href="#">
            <span class="menu-name">{{ __("Leave Years", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/hr/miscellaneous'" >
          <a href="#">
            <span class="menu-name">{{ __("Miscellaneous", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/hr/hr-frontend'" >
          <a href="#">
            <span class="menu-name">{{ __("HR Frontend", "erp") }}</span>
          </a>
        </router-link>

      <li>
        <router-link tag="li" :to="'/hr/recruitment'" >
          <a href="#">
            <span class="menu-name">{{ __("Recruitment", "erp") }}</span>
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
