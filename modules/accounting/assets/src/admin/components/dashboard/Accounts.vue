<template>
    <div :class="['bank-accounts-section', 'wperp-panel', 'wperp-panel-default', ( isEditSettingsEnabled ? 'open-edit':'' )]">
        <div class="wperp-panel-heading wperp-bg-white">
            <h4>Bank Accounts</h4>
            <a href="#" @click.prevent="editSettings" id="bank-account-edit" class="panel-badge">
                <span v-if="isEditSettingsEnabled" @click.prevent="saveDashboardBanks">Save</span>
                <i v-else class="flaticon-quick-edit"></i>
            </a>
        </div>
        <div class="wperp-panel-body pb-0">
            <ul v-if="!isEditSettingsEnabled" class="wperp-list-unstyled list-table-content list-table-content--border">
                <li v-if="accounts.length" :key="key" v-for="(item,key) in accounts">
                    <div class="left">
                        <i class="flaticon-menu-1"></i>
                        <a href="#" class="title">{{item.name}}</a>
                    </div>
                    <div class="right">
                        <span v-if="undefined === item.balance" class="price">{{formatDBAmount(0)}}</span>
                        <span v-else class="price">{{formatDBAmount(item.balance)}}</span>
                        <i class="flaticon-trash" @click.prevent="removeAccount(key)"></i>
                    </div>
                </li>
            </ul>
            <ul v-else class="wperp-list-unstyled list-table-content list-table-content--border">
                <li :key="key" v-for="(item,key) in edit_accounts">
                    <div class="left">
                        <i class="flaticon-menu-1"></i>
                        <a href="#" class="title">{{item.name}}</a>
                    </div>
                    <div class="right">
                        <span v-if="undefined === item.balance" class="price">{{formatDBAmount(0)}}</span>
                        <span v-else class="price">{{formatDBAmount(item.balance)}}</span>
                        <i class="flaticon-trash" @click.prevent="removeEditAccount(key)"></i>
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
                accounts: [],
                edit_accounts: []
            }
        },

        methods: {
            fetchAccounts() {
                if ( !this.isEditSettingsEnabled ) {
                    HTTP.get('/accounts/cash-at-bank').then( (response) => {
                        this.accounts = response.data;
                    });

                    if ( !this.accounts.length ) {
                        this.edit_accounts = [];
                        HTTP.get('/accounts/bank-accounts').then( (response) => {
                            this.accounts = response.data;
                            this.edit_accounts = response.data;
                        });
                    }
                } else {
                    HTTP.get('/accounts/bank-accounts').then( (response) => {
                        this.accounts = response.data;
                        this.edit_accounts = response.data;
                    });
                }

            },

            saveDashboardBanks() {
                HTTP.post('/accounts/cash-at-bank', {
                    accounts: this.edit_accounts
                }).then(() => {
                    this.fetchAccounts();
                    this.isEditSettingsEnabled = false;
                });
            },

            removeAccount(key) {
                this.$delete(this.accounts, key);
            },

            removeEditAccount(key) {
                this.$delete(this.edit_accounts, key);
            },

            editSettings() {
                this.fetchAccounts();
                this.isEditSettingsEnabled = !this.isEditSettingsEnabled;
            }
        },

        mounted() {
            this.fetchAccounts();
        },

        computed: {
            totalAmount() {
                if ( ( typeof this.accounts === "object" && null === this.accounts ) || !this.accounts.length ) {
                    return;
                }
                let total = this.accounts.reduce((amount, item) => {
                    return amount + parseFloat(item.balance);
                }, 0);

                if ( isNaN( parseFloat(total) )) {
                    total = 0;
                }

                return this.formatAmount(total);
            },
        },

    }
</script>

<style scoped>
</style>
