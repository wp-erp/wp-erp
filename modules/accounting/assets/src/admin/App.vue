<template>
    <div id="erp-accounting">
        <ERPMenu></ERPMenu>
        <router-view/>
    </div>
</template>

<script>
    import ERPMenu from 'admin/components/menu/ERPMenu.vue';

    import 'vue-loading-overlay/dist/vue-loading.css';
    import '../../font/flaticon.css'
    import '../../css/master.css'

    export default {
        name: 'Accounting',
        components: { ERPMenu }
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
</style>
