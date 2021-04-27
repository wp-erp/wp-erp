<template>
    <div class="erp-nav-container">
        <h2>Settings Demo</h2>
    </div>
</template>

<script>
export default {
    name: 'SettingsMenu',

    props: {},
    data() {
        /* global __ */
        return {
            // menuItems: erp_settings_var.erp_settings_menus, /* global erp_settings_var */
            menuItems: [

            ],
            module_name: ''
        };
    },

    created() {
        this.init();
    },

    methods: {
        init() {

        }
    }
};
</script>
