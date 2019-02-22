<template>
    <div class="with-multiselect">
        <multi-select placeholder="Select Account" v-model="selectedAccount" :options="accounts" />
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from "admin/components/select/MultiSelect.vue";

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

            reset: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                selectedAccount: null,
                accounts       : []
            }
        },

        watch: {
            value(newVal) {
                let val = this.accounts.find(account => newVal.id === account.id);
                this.selectedAccount = val;
            },

            selectedAccount() {
                this.$emit('input', this.selectedAccount);
            },

            reset() {
                this.selectedAccount = [];
            }
        },

        created() {
            this.fetchAccounts();
        },

        methods: {
            fetchAccounts(){
                HTTP.get('accounts').then(response => {
                    this.accounts = response.data;
                } );
            },
        }
    }
</script>

<style scoped>

</style>
