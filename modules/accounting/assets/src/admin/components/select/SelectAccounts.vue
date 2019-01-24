<template>
    <div class="with-multiselect">
        <multi-select :placeholder="`Select Account`" v-model="selectedAccount" :options="accounts" />
    </div>
</template>

<script>
    import MultiSelect from "admin/components/select/MultiSelect.vue";
    import HTTP from 'admin/http'
    export default {

        name: "SelectAccounts",

        components: { MultiSelect, HTTP },

        data(){
            return {
                selectedAccount: '',
                accounts:[]
            }
        },

        created() {
            this.fetchAccounts();
        },

        methods: {
            fetchAccounts(){
                HTTP.get('accounts').then( (response) => {
                    this.accounts = response.data;
                } );
            },
        },

        watch: {
            selectedAccount() {
                this.$emit('input', this.selectedAccount);
            },
        }
    }
</script>

<style scoped>

</style>
