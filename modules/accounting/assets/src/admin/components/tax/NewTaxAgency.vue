<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ is_update ? 'Edit' : 'Add' }} {{ __('Tax Agency', 'erp') }}</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>

                <show-errors :error_msgs="form_errors" />
                <!-- end modal body title -->
                <form action="" method="post" class="modal-form edit-customer-modal" @submit.prevent="taxAgencyFormSubmit">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>{{ __('Tax Agency Name', 'erp') }}<span class="wperp-required-sign">*</span></label>
                            <!--<multi-select v-model="agency" :options="agencies" />-->
                            <input type="text" v-model="agency" class="wperp-form-field">
                        </div>

                    </div>

                    <div class="wperp-modal-footer pt-0">
                        <!-- buttons -->
                        <div class="buttons-wrapper text-right">
                            <submit-button v-if="is_update" :text="__( 'Update', 'erp' )" :working="isWorking"></submit-button>
                            <submit-button v-else :text="__( 'Save', 'erp' )" :working="isWorking"></submit-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>

<script>
import HTTP from 'admin/http';
import SubmitButton from 'admin/components/base/SubmitButton.vue';
import ShowErrors from 'admin/components/base/ShowErrors.vue';

export default {
    name: 'NewTaxAgency',

    components: {
        SubmitButton,
        ShowErrors
    },

    props: {
        agency_id: {
            type: [Number, String]
        },
        is_update: {
            type: Boolean
        }
    },

    data() {
        return {
            agencies   : [],
            agency     : null,
            isWorking  : false,
            form_errors: []
        };
    },

    created() {
        if (this.is_update) {
            this.getAgency();
        }
    },

    methods: {
        closeModal: function() {
            this.$emit('close');
            this.$root.$emit('modal_closed');
        },

        getAgency() {
            HTTP.get(`/tax-agencies/${this.agency_id}`).then((response) => {
                this.agency = response.data.name;
            });
        },

        taxAgencyFormSubmit() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });

                return;
            }

            var rest, url, msg;

            if (this.is_update) {
                rest = 'put';
                url = `/tax-agencies/${this.agency_id}`;
                msg = 'Tax Agency Updated!';
            } else {
                rest = 'post';
                url = `/tax-agencies`;
                msg = 'Tax Agency Created!';
            }

            this.$store.dispatch('spinner/setSpinner', true);

            HTTP[rest](url, {
                agency_name: this.agency
            }).catch(error => {
                this.$store.dispatch('spinner/setSpinner', false);
                throw error;
            }).then(res => {
                this.$store.dispatch('spinner/setSpinner', false);
                this.showAlert('success', msg);
            }).then(() => {
                this.resetData();
                this.isWorking = false;
                this.$emit('close');
                this.$root.$emit('refetch_tax_data');
            });
        },

        validateForm() {
            this.form_errors = [];

            if (!this.agency) {
                this.form_errors.push('Agency Name is required.');
            }
        },

        resetData() {
            Object.assign(this.$data, this.$options.data.call(this));
        }

    }
};
</script>
<style lang="less" scoped>
    .modal-close {
        .flaticon-close {
            font-size: inherit;
        }
    }
</style>
