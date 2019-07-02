<template>
    <div class="with-multiselect">
        <multi-select placeholder="Select Account" v-model="selectedAccount" :options="accounts" />
        <span class="balance mt-10 display-inline-block">Balance: {{transformBalance(balance)}}</span>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {

        name: 'SelectAccounts',

        components: {
            MultiSelect
        },

        props: {
            value: {
                type: [String, Object, Array],
                default: ''
            },

            override_accts: {
                type: [Object, Array]
            },

            reset: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                selectedAccount: null,
                balance        : 0,
                accounts       : [],
            }
        },

        watch: {
            value(newVal) {
                let val = this.accounts.find(account => newVal.id === account.id);
                if ( typeof newVal === 'undefined' || typeof val === 'undefined' ) {
                    return newVal;
                }
                this.selectedAccount = val;
                this.balance = val.balance;
            },

            selectedAccount() {
                this.balance = 0;
                this.$emit('input', this.selectedAccount);
            },

            override_accts() {
                this.accounts = [];

                for ( let acct of this.override_accts ) {
                    if ( ! acct.hasOwnProperty('name') ) {
                        continue;
                    }

                    this.accounts.push( acct );
                }
            },

            reset() {
                this.selectedAccount = [];
                this.balance         = 0;
            },
        },

        created() {
            this.$root.$on( 'account-changed', () => {
                this.selectedAccount = [];
            });
            if (this.override_accts && this.override_accts.length) {
                this.accounts = this.override_accts;
            } else {
                this.fetchAccounts();
            }
        },

        methods: {
            fetchAccounts() {
                HTTP.get('/accounts').then(response => {
                    this.accounts = response.data;
                });
            },

            transformBalance( val ) {
                let currency = '$';
                if ( val < 0 ){
                    return `Cr. ${currency} ${Math.abs(val)}`;
                }

                return `Dr. ${currency} ${val}`;
            },
        }
    }
</script>
