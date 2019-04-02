<template>
    <div :class="['bank-accounts-section', 'wperp-panel', 'wperp-panel-default', ( isEditSettingsEnabled ? 'open-edit':'' )]">
        <div class="wperp-panel-heading wperp-bg-white">
            <h4>Accounts</h4>
            <!--<a href="#" @click.prevent="editSettings" id="bank-account-edit" class="panel-badge">-->
                <!--<span v-if="isEditSettingsEnabled" @click.prevent="saveDashboardBanks">Save</span>-->
                <!--<i v-else class="flaticon-quick-edit"></i>-->
            <!--</a>-->
        </div>
        <div class="wperp-panel-body pb-0">
            <ul v-if="accounts.length" class="wperp-list-unstyled list-table-content list-table-content--border">
                <li :key="key" v-for="(data,key) in accounts">
                    <div class="left">
                        <i class="flaticon-menu-1"></i>
                        <details v-if="data.additional" open>
                            <summary>{{ data.name }}</summary>
                            <p :key="additional.id" v-for="additional in data.additional">
                                {{ additional.name }}
                                {{ getCurrencySign() + Math.abs(additional.balance) }}
                            </p>
                        </details>
                        <span v-else>{{ data.name }}</span>
                    </div>
                    <div class="right">
                        <span v-if="undefined === data.balance" class="price">{{formatDBAmount(0)}}</span>
                        <span v-else class="price">{{formatDBAmount(data.balance)}}</span>
                    </div>
                </li>
            </ul>
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
                accounts             : [],
                edit_accounts        : []
            }
        },

        computed: {
            totalAmount() {
                if ( ( typeof this.accounts === "object" && null === this.accounts ) ) {
                    return;
                }
                let amount = 0;
                this.accounts.forEach( element => {
                    if ( null === element.balance ) {
                        element.balance = 0;
                    }
                    amount += parseFloat(element.balance);
                });

                if ( isNaN( parseFloat(amount) )) {
                    amount = 0;
                }

                return this.formatAmount(amount);
            },
        },

        created() {
            this.fetchAccounts();
        },


        mounted() {
            // Add filter test
            hooks.addFilter( 'dashboardTitle', 'acct', function(title) {
                console.log('Accounts.vue : line 89');

                return title.toUpperCase();
            } );

            // Add action test
            hooks.addAction( 'todayDate', 'acct', function(today) {
                console.log('Accounts.vue : line 96');

                console.log( today );
            } );
        },

        methods: {
            fetchAccounts() {
                HTTP.get('/accounts/cash-at-bank').then( response => {
                    this.accounts = response.data;
                });
            },

            // saveDashboardBanks() {
            //     HTTP.post('/accounts/cash-at-bank', {
            //         accounts: this.edit_accounts
            //     }).then(() => {
            //         this.fetchAccounts();
            //         this.isEditSettingsEnabled = false;
            //     });
            // },
            //
            // removeAccount(key) {
            //     this.$delete(this.accounts, key);
            // },
            //
            // removeEditAccount(key) {
            //     this.$delete(this.edit_accounts, key);
            // },
            //
            // editSettings() {
            //     this.fetchAccounts();
            //     this.isEditSettingsEnabled = !this.isEditSettingsEnabled;
            // }
        },
    }
</script>

<style scoped>
</style>
