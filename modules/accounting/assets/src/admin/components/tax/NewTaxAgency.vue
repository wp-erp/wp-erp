<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ is_update ? 'Edit' : 'Add' }} Tax Agency</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>Tax Agency Name</label>
                            <!--<multi-select v-model="agency" :options="agencies" />-->
                            <input type="text" v-model="agency" />
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button v-if="is_update" text="Update Tax Agency" @click.native.prevent="UpdateTaxAgency" :working="isWorking"></submit-button>
                            <submit-button v-else text="Add New Tax Agency" @click.native.prevent="addNewTaxAgency" :working="isWorking"></submit-button>
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
        name: 'NewTaxAgency',

        components: {
            HTTP,
            MultiSelect,
            SubmitButton
        },

        props: {
            agency_id: {
                type: [ Number, String ]
            },
            is_update: {
                type: Boolean
            }
        },

        data() {
            return {
                agencies: [],
                agency: null,
                isWorking: false,
            };
        },

        created() {
            if ( this.is_update ) {
                this.getAgency();
            }
        },

        methods: {
            closeModal: function(){
                this.$emit('close');
            },

            getAgency() {
                HTTP.get(`/tax-agencies/${this.agency_id}`).then((response) => {
                    this.agency = response.data.name;
                });
            },

            addNewTaxAgency() {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.post('/tax-agencies', {
                    agency_name: this.agency,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Agency Created!' );
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                } ).then(() => {
                    this.resetData();
                    this.isWorking = false;
                    this.$emit('close');
                    this.$root.$emit('refetch_tax_data');
                });
            },

            UpdateTaxAgency() {
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.put(`/tax-agencies/${this.agency_id}`, {
                    agency_name: this.agency,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Agency Created!' );
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
