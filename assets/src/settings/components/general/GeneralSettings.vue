<template>
    <div>
        <h2>General Settings</h2>
        <!-- <settings-menu></settings-menu> -->
    </div>
</template>

<script>
// import SettingsMenu from 'settings/components/menu/SettingsMenu.vue';

export default {
    name: 'GeneralSettings',

    components: {
        // SettingsMenu
    },

    props: {},

    created() {
        this.$store.dispatch('spinner/setSpinner', false);
    },

    methods: {

    }
};
</script>
