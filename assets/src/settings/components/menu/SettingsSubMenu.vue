<template>
  <div class="settings-submenu-navbar">
    <ul class="settings-sub-menu">

      <li v-for="(menu, key, index) in menus" :key="key">
        <router-link
            tag="li"
            :to="'/' + parent_id + '/' + key"
            :class="($route.name === 'HRWorkDays' && index === 0) ? 'router-link-active' : ''"
            >
          <a href="#">
            <span class="menu-name">{{ menu }}</span>
          </a>
        </router-link>
      </li>

    </ul>
  </div>
</template>

<script>
export default {
  name: "SettingsSubMenu",

  props: [ 'parent_id', 'menus' ]
};
</script>
