<template>
    <div class="wperp-col-sm-12">
        <div class="check-fields">
            <label>{{ __('Bank Name', 'erp') }}</label>
            <input type="text" class="wperp-form-field" @input="updateCheckData" v-model="check_fields.bank_name"/>
        </div>
        <div class="check-fields">
            <label>{{ __('Check Name', 'erp') }}</label>
            <input type="text" class="wperp-form-field" @input="updateCheckData" v-model="check_fields.payer_name"/>
        </div>
        <div class="check-fields">
            <label>{{ __('Check No', 'erp') }}</label>
            <input type="text" class="wperp-form-field" @input="updateCheckData" v-model="check_fields.check_no"/>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CheckFields',

    data() {
        return {
            check_fields: {
                bank_name : '',
                payer_name: '',
                check_no  : ''
            }
        };
    },

    methods: {
        updateCheckData() {
            this.$emit('updateCheckFields', this.check_fields);
        }
    }
};
</script>

<style scoped>
    .check-fields {
        margin-bottom: 20px;
        width: 30%;
        float: left;
        margin-right: 25px;
    }
</style>
