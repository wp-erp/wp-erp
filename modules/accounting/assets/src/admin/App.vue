<template>
    <div id="erp-accounting">
        <ERPMenu></ERPMenu>
        <router-view/>
    </div>
</template>

<script>
    import ERPMenu from 'admin/components/menu/ERPMenu.vue';
    import VueSweetalert2 from 'vue-sweetalert2';

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
</style>
