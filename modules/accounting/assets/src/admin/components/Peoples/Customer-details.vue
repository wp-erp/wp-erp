<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
            <UserBasicInfo :userData="userData"></UserBasicInfo>
        <!-- End .header-section -->
    </div>
</template>

<script>
    import UserBasicInfo from "admin/components/Userinfo/User-basic.vue";

    export default {
        name: "Customer-details",
        components: {UserBasicInfo},

        data(){
            return {
                userData: {
                    'name': 'Mishu Alam',
                    'email': 'mishu@dishkao.com',
                    'meta': {
                        'phone number': '+ 88101230123',
                        'website': 'www.cholejai.com',
                        'mobile': '+ 999999999',
                        'fax': 'nomail@onlyvago.com',
                        'address': 'Ghor nai, Bari nai, Hisab nai re',
                    }
                }
            }
        }
    }
</script>

<style lang="less">

</style>
