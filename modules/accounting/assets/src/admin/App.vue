<template>
    <div id="erp-accounting">
        <accounting-menu></accounting-menu>
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
    import { mapState, mapActions } from 'vuex'

    import AccountingMenu from 'admin/components/menu/AccountingMenu.vue'
    import Loading from 'vue-loading-overlay'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import 'vue-loading-overlay/dist/vue-loading.css'
    import '../../font/flaticon.css'
    import '../../css/master.css'

    export default {
        name: 'Accounting',

        components: {
            AccountingMenu,
            Loading
        },

        computed: mapState({
            loader: state => state.spinner.loader
        }),
    }
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

    .content-header-section .wperp-col {
        width: 100%;
        display: block !important;
    }
</style>
