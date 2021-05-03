<template>
  <div class="settings-navbar">
    <h2>Settings</h2>
    <ul class="settings-menu">

      <li>
        <router-link tag="li" :to="'/general'" class="active">
          <a href="#">
            <i class="flaticon-settings-work-tool icon-general"></i>
            <span class="menu-name">{{ __("General", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/hr'" >
          <a href="#">
            <span class="dashicons dashicons-admin-users icon-hr"></span>
            <span class="menu-name">{{ __("HR", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <router-link tag="li" :to="'/crm'" >
          <a href="#">
            <span class="dashicons dashicons-groups icon-crm"></span>
            <span class="menu-name">{{ __("CRM", "erp") }}</span>
          </a>
        </router-link>
      </li>

      <li>
        <a href="/accounting">Accounting</a>
      </li>

      <li>
        <a href="/woocommerce">WooCommerce</a>
      </li>

      <li>
        <a href="/email">Email</a>
      </li>

      <li>
        <a href="/integrations">Integrations</a>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: "SettingsMenu",

  props: {},

  data() {
    return {
      menuItems: erp_settings_var.erp_settings_menus
    };
  },

  created() {
    this.init();
  },

  mounted() {
    // this.setAddOnNavByHook();
    this.$store.dispatch("spinner/setSpinner", false);
  },

  methods: {
    // setAddOnNavByHook() {
    //   window.erpHrHooks.doAction("addRouter");
    //   this.addOnNav = this.$store.state.addOnNav;
    // },

    init() {
      this.$store.dispatch("spinner/setSpinner", true);
    },
  },
};
</script>
