<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ tax_rate.tax_name }}</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>

                <div class="wperp-invoice-table">
                    <table class="wperp-table wperp-form-table invoice-table">
                        <thead>
                        <tr>
                            <th>Agency</th>
                            <th>Tax Rate</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr :key="index" v-for="(component, index) in tax_rate.tax_components">
                            <td>{{ component.agency_id }}</td>
                            <td>{{ component.tax_rate }}</td>
                        </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'

    export default {
        name: 'SingleTaxRateModal',

        components: {
            HTTP,
            MultiSelect,
            SubmitButton
        },

        props: {
            tax_id: Number
        },

        data() {
            return {
                tax_rate: {},
                agency: '',
                isWorking: false,
            };
        },

        created() {
            this.getTaxRate();
        },

        methods: {
            closeModal: function(){
                this.$emit('close');
            },

            getTaxRate() {
                let taxid = this.tax_id;
                HTTP.get(`/taxes/${taxid}`).then((response) => {
                   this.tax_rate = response.data;
                });

                console.log( this.tax_rate );
            },

            UpdateTaxRateName() {
                HTTP.post('/tax-agencies', {
                    agency_name: this.agency,
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Tax Agency Created!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                    this.$emit('close');
                    this.$root.$emit('refetch_tax_data');
                });
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },

        },
    }
</script>
<style lang="less">

</style>
