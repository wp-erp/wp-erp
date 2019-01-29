<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>Add Tax Agency</h3>
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
                            <submit-button text="Add New Tax Agency" @click.native.prevent="addNewTaxAgency" :working="isWorking"></submit-button>
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

        data() {
            return {
                agencies: [{}],
                agency: '',
                isWorking: false,
            };
        },

        created() {
            // this.getAgencies();
        },

        methods: {
            closeModal: function(){
                this.$emit('close');
            },

            getAgencies() {
                HTTP.get('/tax-agencies').then((response) => {
                    response.data.forEach(element => {
                        this.agencies.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                });
            },

            addNewTaxAgency() {
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
                });
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
                this.basic_fields.user = '';
            },

        },
    }
</script>
<style lang="less">

</style>
