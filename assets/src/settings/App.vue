<template>
    <div id="erp-settings">
        <settings-menu></settings-menu>
        <h2 role="erp-wp-notice" data-text="Don't remove me, I am super important for admin notice"></h2>

        <router-view/>
        <loading
            :active.sync="loader"
            loader="spinner"
            color="#1a9ed4"
            :opacity="0.8"
            :isFullPage="false"
            :width="45">
        </loading>
    </div>
</template>

<script>
import { mapState } from 'vuex';

import SettingsMenu from 'settings/components/menu/SettingsMenu.vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

// import '../../font/flaticon.css';
// import '../../css/master.css';

export default {
    name: 'Settings',

    components: {
        SettingsMenu,
        Loading
    },

    computed: mapState({
        loader: state => state.spinner.loader
    })
};
</script>
