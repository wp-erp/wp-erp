<template>
    <div id="erp-settings">
        <div class="settings-area">
            <div class="settings-sidebar">
                <settings-menu />
            </div>
            <div class="settings-content">
                <h2 role="erp-wp-notice" data-text="Don't remove me, I am super important for admin notice"></h2>

                <search-bar />

                <router-view />

                <loading
                    :active.sync="loader"
                    loader="spinner"
                    color="#1a9ed4"
                    :opacity="0.8"
                    :isFullPage="false"
                    :width="45">
                </loading>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';

import SettingsMenu from './components/menu/SettingsMenu.vue';
import SearchBar from './components/searchbar/SearchBar.vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

import '../../../../assets/font/flaticon.css';

export default {
    name: 'Settings',

    components: {
        SettingsMenu,
        SearchBar,
        Loading
    },

    computed: mapState({
        loader: state => state.spinner.loader
    })
};
</script>
