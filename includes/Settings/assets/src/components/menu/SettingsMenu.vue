<template>
  <div class="settings-navbar">
    <h2>{{ __("Settings", "erp") }}</h2>
    <ul class="settings-menu">

      <li v-for="(menu, index) in menus" :key="index">
        <router-link tag="li" :to="menu.slug" :class="($route.name === 'GeneralSettings' && index === 0) ? 'router-link-active' : ''">
          <a href="#">
            <img :src="menu.icon" alt="" />
            <span class="menu-name">{{ menu.label }}</span>
            <span v-if="menu.extra.pro_label !== undefined && menu.extra.pro_label" class="pro-label">PRO</span>
          </a>
        </router-link>
      </li>

    </ul>
  </div>
</template>

<script>
export default {
  name: "SettingsMenu",

  data() {
    return {
      menus: erp_settings_var.erp_settings_menus
    }
  },
};
</script>
