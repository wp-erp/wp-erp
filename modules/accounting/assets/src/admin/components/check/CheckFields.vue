<template>
    <div class="wperp-col-sm-8">
            <div class="check-fields">
                <label>Check Name</label>
                <input type="text" @change="updateCheckData" v-model="check_fields.payer_name"/>
            </div>
            <div class="check-fields">
                <label>Check No</label>
                <input type="text" @change="updateCheckData" v-model="check_fields.check_no"/>
            </div>
    </div>
</template>

<script>
    export default {
        name: "CheckFields",

        props: {

        },

        data() {
            return {
                check_fields: {
                    payer_name: '',
                    check_no: ''
                }
            }
        },

        methods: {
            updateCheckData() {
                this.$emit( 'updateCheckFields', this.check_fields );
            }
        },
    }
</script>

<style scoped>
    .check-fields {
        margin-bottom: 20px;
        width: 46%;
        float: left;
        margin-right: 25px;
    }
</style>
