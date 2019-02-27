<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>Add Tax Rate Name</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>Tax Rate Name</label>
                            <input type="text" v-model="rate_name" />
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button v-if="is_update" text="Update" @click.native.prevent="updateTaxRateName" :working="isWorking"></submit-button>
                            <submit-button v-else text="Add New" @click.native.prevent="addNewTaxRateName" :working="isWorking"></submit-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import SubmitButton from 'admin/components/base/SubmitButton.vue'

    export default {
        name: 'NewTaxRateName',

        components: {
            HTTP,
            MultiSelect,
            SubmitButton
        },

        props: {
            rate_name_id: {
                type: [ Number, String ]
            },
            is_update: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                rate_names: [{}],
                rate_name: '',
                isWorking: false,
            };
        },

        created() {
            if ( this.is_update ) {
                this.getRateName();
            }
        },

        methods: {
            closeModal() {
                this.$emit('close');
            },

            getRateName() {
                HTTP.get(`/tax-rate-names/${this.rate_name_id}`).then((response) => {
                    this.rate_name = response.data.name;
                });
            },

            addNewTaxRateName() {
                HTTP.post('/tax-rate-names', {
                    name: this.rate_name,
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Tax Rate Name Created!',
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

            updateTaxRateName() {
                HTTP.put(`/tax-rate-names/${this.rate_name_id}`, {
                    name: this.rate_name,
                }).then(res => {
                    console.log(res.data);
                    this.$swal({
                        position: 'center',
                        type: 'success',
                        title: 'Tax Rate Name Update!',
                        showConfirmButton: false,
                        timer: 1000
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
