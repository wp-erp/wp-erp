<template>
    <div id="wperp-tax-category-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ is_update ? 'Edit' : 'Add' }} Tax Category</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>

                <show-errors :error_msgs="form_errors" ></show-errors>
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>Tax Category Name<span class="wperp-required-sign">*</span></label>
                                <!--<multi-select v-model="category" :options="categories" />-->
                                <input type="text" v-model="category" />
                        </div>

                        <div class="wperp-form-group mb-0">
                            <label>Description</label>
                            <textarea v-model="desc" rows="4" class="wperp-form-field"></textarea>
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button v-if="is_update" text="Update Tax Category" @click.native.prevent="updateTaxCat" :working="isWorking"></submit-button>
                            <submit-button v-else text="Save" @click.native.prevent="addNewTaxCat" :working="isWorking"></submit-button>
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
    import ShowErrors from 'admin/components/base/ShowErrors.vue'

    export default {
        name: 'NewTaxCategory',

        components: {
            HTTP,
            MultiSelect,
            SubmitButton,
            ShowErrors
        },

        props: {
            cat_id: {
                type: [ Number, String ]
            },
            is_update: {
                type: Boolean
            }
        },

        data() {
            return {
                categories: [],
                category: null,
                desc: null,
                isWorking: false,
                form_errors: [],
            };
        },

        created() {
            if ( this.is_update ) {
                this.getCategory();
            }
        },

        methods: {
            closeModal: function(){
                this.$emit('close');
            },

            getCategory() {
                HTTP.get(`/tax-cats/${this.cat_id}`).then((response) => {
                    this.category = response.data.name;
                    this.desc = response.data.description;
                });
            },

            addNewTaxCat() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.post('/tax-cats', {
                    name: this.category,
                    description: this.desc,
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Category Created!' );
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                    this.$emit('close');
                    this.$root.$emit('refetch_tax_data');
                });
            },

            updateTaxCat() {
                this.validateForm();

                if ( this.form_errors.length ) {
                    window.scrollTo({
                        top: 10,
                        behavior: 'smooth'
                    });
                    return;
                }

                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.put(`/tax-cats/${this.cat_id}`, {
                    name: this.category,
                    description: this.desc,
                }).catch( error => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Category Updated!' );
                }).then(() => {
                    this.resetData();
                    this.isWorking = false;
                    this.$emit('close');
                    this.$root.$emit('refetch_tax_data');
                });
            },

            validateForm() {
                this.form_errors = [];

                if ( !this.category ) {
                    this.form_errors.push('Tax Category Name is required.');
                }
            },

            resetData() {
                Object.assign(this.$data, this.$options.data.call(this));
            },

        },
   	}
</script>
<style lang="less">

</style>
