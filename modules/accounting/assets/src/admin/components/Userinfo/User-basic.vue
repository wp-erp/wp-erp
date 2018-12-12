<template>
    <div class="wperp-panel wperp-panel-default mt-20">
        <div class="wperp-panel-body wperp-customer-panel">
            <!-- edit customers info trigger -->
            <span class="edit-badge" data-toggle="wperp-modal" data-target="wperp-edit-customer-modal">
                <i class="flaticon-edit"></i>
            </span>
            <div class="wperp-row">
                <div class="wperp-col-lg-3 wperp-col-md-4 wperp-col-sm-4 wperp-col-xs-12">
                    <div class="customer-identity">
                        <img :src=user.img_url :alt=user.name>
                        <div class="">
                            <h3>{{user.name}}</h3>
                            <span>{{user.email}}</span>
                        </div>
                    </div>
                </div>
                <div class="wperp-col-lg-9 wperp-col-md-8 wperp-col-sm-8 wperp-col-xs-12">
                    <ul class="customer-meta">
                        <li v-for="(val,key) in user.meta">
                            <strong>{{camelCase(key)}}:</strong>
                            <span>{{val}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "User-basic-info",
        props: {
            userData:{
                type: Object,
                required: true,
                default: () => {
                    return {
                        'id' : '',
                        'name': 'Full Name',
                        'email': 'email@mail.com',
                        'img_url' : erp_acct_var.acc_aaset_url + '/images/dummy-user.png',
                        'meta': {
                            'phone': '+ 88101230123',
                            'mobile': '+ 999999999',
                            'website': 'www.website.com',
                            'fax': '+99898989898',
                            'address': 'House#1005, Block#B, Avenue#9, Mirpur DOHS',
                        }
                    }
                }
            }
        },

        computed: {
            user(){
                return this.userData;
            }
        },

        methods: {
            camelCase(str){
                return str.toLowerCase().replace(/(?:(^.)|(\s+.))/g, function(match) {
                    return match.charAt(match.length-1).toUpperCase();
                });
            }
        }
    }

</script>

<style lang="less" scoped>

</style>
