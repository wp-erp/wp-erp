<template>
    <div id="erp-accounting">
        <ERPMenu></ERPMenu>
        <router-view/>
    </div>
</template>

<script>
    import ERPMenu from 'admin/components/Menu/ERPMenu.vue'
    import VueSweetalert2 from 'vue-sweetalert2';

    import '../../css/plugins/select2.min.css';
    import '../../font/flaticon.css'
    import '../../css/master.css'

    export default {
        name: 'Accounting',
        components: { ERPMenu }
    }
</script>

<style>

</style>
