<template>
    <div :class="['bank-accounts-section', 'wperp-panel', 'wperp-panel-default', ( isEditSettingsEnabled ? 'open-edit':'' )]">
        <div class="wperp-panel-heading wperp-bg-white">
            <h4>Bank Accounts</h4>
            <!--<a href="#" @click.prevent="isEditSettingsEnabled = !isEditSettingsEnabled" id="bank-account-edit" class="panel-badge">-->
                <!--<span v-if="isEditSettingsEnabled">Save</span>-->
                <!--<i v-else class="flaticon-quick-edit"></i>-->
            <!--</a>-->
        </div>
        <div class="wperp-panel-body pb-0">
            <ul class="wperp-list-unstyled list-table-content list-table-content--border list-table-content--bg-space">
                <li v-for="item in accounts">
                    <div class="left">
                        <i class="flaticon-menu-1"></i>
                        <a href="#" class="title">{{item.name}}</a>
                    </div>
                    <div class="right">
                        <span class="price">{{formatAmount(item.balance)}}</span>
                        <i class="flaticon-trash"></i>
                    </div>
                </li>
            </ul>

            <!--<dropdown placement="bottom-start">-->
                <!--<template slot="button">-->
                    <!--<a href="#" class="dropdown-trigger wperp-btn btn&#45;&#45;default">-->
                        <!--<i class="flaticon-add-plus-button"></i>-->
                        <!--<span>Add New</span>-->
                    <!--</a>-->
                <!--</template>-->
                <!--<template slot="dropdown">-->
                    <!--<ul slot="action-items" role="menu">-->
                        <!--<li><a href="#">Master Card</a></li>-->
                        <!--<li><a href="#">Paypal Account</a></li>-->
                        <!--<li><a href="#">Skrill</a></li>-->
                        <!--<li><a href="#">Payza Account</a></li>-->
                        <!--<li><a href="#">Master Card</a></li>-->
                    <!--</ul>-->
                <!--</template>-->
            <!--</dropdown>-->

        </div>
        <div class="wperp-panel-footer mt-50">
            <div class="bank-accounts-total">
                <span class="title">Total Balance</span>
                <span class="price">{{totalAmount}}</span>
            </div>
        </div>
    </div>

</template>

<script>
    import HTTP from 'admin/http';
    import Dropdown from 'admin/components/base/Dropdown.vue'
    export default {
        name: "Accounts",
        components : {
            Dropdown
        },

        data() {
            return {
                isEditSettingsEnabled: false,
                accounts: [],
            }
        },

        methods: {
            fetchAccounts(){
                HTTP.get('accounts').then( (response) => {
                    this.accounts = response.data;
                } );
            },
        },

        created(){
            this.fetchAccounts();
        },

        computed: {

            totalAmount(){
                let total = this.accounts.reduce( ( amount, item ) => {
                                return amount + parseFloat(item.balance);
                            }, 0 );

                return this.formatAmount(total);
            }

        }
    }
</script>

<style scoped>
    .list-table-content--border li:last-child{
        border-bottom: 0px;
    }
</style>
