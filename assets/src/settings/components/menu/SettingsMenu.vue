<template>
    <div class="settings-navbar">
        <h2>Settings</h2>
        <ul class="settings-menu">
            <li class="active">
                <a href="/general">General</a>
            </li>
            <li>
                <a href="/hr">HR</a>
            </li>
            <li>
                <a href="/crm">CRM</a>
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
    name: 'SettingsMenu',

    props: {},

    data() {
        return {
            // menuItems: erp_settings_var.erp_settings_menus
        };
    },

    created() {
        this.init();
    },

    methods: {
        init() {
            this.$store.dispatch('spinner/setSpinner', true);
        }
    }
};
</script>
