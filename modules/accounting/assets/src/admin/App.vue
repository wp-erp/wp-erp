<template>
    <div id="erp-accounting">
        <ERPMenu></ERPMenu>
        <router-view/>
    </div>
</template>

<script>
    import ERPMenu from 'admin/components/menu/ERPMenu.vue'
    import VueSweetalert2 from 'vue-sweetalert2';

    import '../../font/flaticon.css'
    import '../../css/master.css'

    export default {
        name: 'Accounting',
        components: { ERPMenu }
    }
</script>

<style>
    .has-err {
        border-color: #f44336 !important;
    }
</style>
