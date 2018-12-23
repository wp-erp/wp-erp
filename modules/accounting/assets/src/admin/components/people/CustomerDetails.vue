<template>
    <div class="wperp-container">

        <!-- Start .header-section -->
            <UserBasicInfo :userData="userData"></UserBasicInfo>
        <!-- End .header-section -->
    </div>
</template>

<script>
    import HTTP from 'admin/http.js'
    import UserBasicInfo from 'admin/components/userinfo/UserBasic.vue'

    export default {
        name: 'CustomerDetails',

        components: {
            UserBasicInfo
        },

        data(){
            return {
                userId : '',
                resData: {},
                userData : {
                    'id': '',
                    'name': '************',
                    'email': '************',
                    'img_url': erp_acct_var.acct_assets + '/images/dummy-user.png',
                    'meta': {
                        'company': '**********',
                        'website': '**********',
                        'phone': '**********',
                        'mobile': '*************',
                        'address': '*********',
                    }
                }
            }
        },

        created(){
            this.userId = this.$route.params.id;
            this.fetchItem( this.userId );
        },

        computed: {

        },

        methods: {
            fetchItem( id ) {
                HTTP.get('customers/'+id, {
                    params: {}
                })
                    .then((response) => {
                        this.resData = response.data;
                        this.mapData();
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .then(() => {
                        //ready
                    });
            },
            mapData(){
                let billing_address = this.resData.billing;
                this.userData = {
                    'id': this.resData.id,
                    'name': this.resData.first_name + ' ' + this.resData.last_name,
                    'email': this.resData.email,
                    'img_url': this.userData.img_url,
                    'meta': {
                        'company': this.resData.company,
                        'website': this.resData.website,
                        'phone': this.resData.phone,
                        'mobile': billing_address.phone,
                        'address': `${billing_address.street_1} ${billing_address.street_2}  ${billing_address.city}  ${billing_address.state}  ${billing_address.country}`
                    }
                }
            },
        }
    }
</script>

<style lang="less">

</style>
