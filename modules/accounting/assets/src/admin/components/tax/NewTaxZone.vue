<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ is_update ? 'Edit' : 'Add' }} Tax Zone</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>Tax Zone Name</label>
                            <input type="text" v-model="rate_name" />
                        </div>

                        <div class="wperp-form-group">
                            <label>Tax Number</label>
                            <input type="text" v-model="tax_number" class="wperp-form-field" placeholder="Enter Tax Number">
                        </div>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" v-model="is_default" class="form-check-input">
                                <span class="form-check-sign"></span>
                                <span class="field-label">Is this tax default?</span>
                            </label>
                        </div>
                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button v-if="is_update" text="Update" @click.native.prevent="updateTaxRateName" :working="isWorking"></submit-button>
                            <submit-button v-else text="Add New" @click.native.prevent="addNewTaxZone" :working="isWorking"></submit-button>
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
        name: 'NewTaxZone',

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
                tax_number: '',
                is_default: false,
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
                    this.rate_name  = response.data.tax_rate_name;
                    this.is_default = ('1' === response.data.default) ? true : false;
                    this.tax_number = response.data.tax_number;
                });
            },

            addNewTaxZone() {
                this.$store.dispatch( 'spinner/setSpinner', true );

                HTTP.post('/tax-rate-names', {
                    tax_rate_name: this.rate_name,
                    tax_number   : this.tax_number,
                    default      : this.is_default,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Zone Created!' );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
                    this.resetData();
                    this.isWorking = false;
                    this.$emit('close');
                    this.$root.$emit('refetch_tax_data');
                });
            },

            updateTaxRateName() {
                HTTP.put(`/tax-rate-names/${this.rate_name_id}`, {
                    tax_rate_name: this.rate_name,
                    tax_number   : this.tax_number,
                    default      : this.is_default,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Zone Updated !' );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
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
