<template>
    <div id="erp-accounting">
        <accounting-menu></accounting-menu>
        <h2 role="erp-wp-notice" data-text="Don't remove me, I am super important for admin notice"></h2>

        <router-view/>
        <loading
            :active.sync="loader"
            loader="spinner"
            color="#1a9ed4"
            :opacity="0.8"
            :width="45">
        </loading>
    </div>
</template>

<script>
import { mapState } from 'vuex';

import AccountingMenu from 'admin/components/menu/AccountingMenu.vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

import '../../font/flaticon.css';
import '../../css/master.css';

export default {
    name: 'Accounting',

    components: {
        AccountingMenu,
        Loading
    },

    computed: mapState({
        loader: state => state.spinner.loader
    })
};
</script>

<style>
    .wrap {
        margin: 0 20px 0 0 !important;
    }

    .has-err {
        border-color: #f44336 !important;
    }

    .error-message {
        color: #d32f2f;
    }

    .attachment-container .attachment-placeholder {
        padding: 20px 3px !important;
    }

    .attachment-container form {
        margin-bottom: 0;
    }

    .wperp-table tfoot tr td:first-child {
        padding-left: 20px;
    }

    .wperp-table th {
        font-weight: 300;
    }

    .router-link-active {
        color: #1a9ed4 !important;
    }
</style>
