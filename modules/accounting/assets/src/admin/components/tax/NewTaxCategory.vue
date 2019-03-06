<template>
    <div id="wperp-tax-category-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>Add Tax Category</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>Tax Category Name</label>
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
                            <submit-button v-else text="Add New Tax Category" @click.native.prevent="addNewTaxCat" :working="isWorking"></submit-button>
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
        name: 'NewTaxCategory',

        components: {
            HTTP,
            MultiSelect,
            SubmitButton
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
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.post('/tax-cats', {
                    name: this.category,
                    description: this.desc,
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
                this.$store.dispatch( 'spinner/setSpinner', true );
                HTTP.put(`/tax-cats/${this.cat_id}`, {
                    name: this.category,
                    description: this.desc,
                }).then(res => {
                    this.$store.dispatch( 'spinner/setSpinner', false );
                    this.showAlert( 'success', 'Tax Category Updated!' );
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
