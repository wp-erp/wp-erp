<template>
    <div id="wperp-tax-agency-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{ is_update ? 'Edit' : 'Add' }} {{ __('Tax Zone', 'erp') }}</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>
                <!-- end modal body title -->
                <show-errors :error_msgs="form_errors"></show-errors>

                <form method="post" class="modal-form edit-customer-modal" @submit.prevent="taxZoneFormSubmit">
                    <div class="wperp-modal-body">

                        <div class="wperp-form-group">
                            <label>{{ __('Tax Zone Name', 'erp') }} <span class="wperp-required-sign">*</span></label>
                            <input type="text" v-model="rate_name" class="wperp-form-field" required>
                        </div>

                        <div class="wperp-form-group">
                            <label>{{ __('Tax Number', 'erp') }}</label>
                            <input type="text" v-model="tax_number" class="wperp-form-field" :placeholder="__('Enter Tax Number', 'erp')">
                        </div>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" v-model="is_default" class="form-check-input">
                                <span class="form-check-sign"></span>
                                <span class="field-label">{{ __('Is this tax default', 'erp') }}?</span>
                            </label>
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
    name: 'NewTaxZone',

    components: {
        SubmitButton,
        ShowErrors
    },

    props: {
        rate_name_id: {
            type: [Number, String]
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
            form_errors: []
        };
    },

    created() {
        if (this.is_update) {
            this.getRateName();
        }
    },

    methods: {
        closeModal() {
            this.$emit('close');
            this.$root.$emit('modal_closed');
        },

        getRateName() {
            HTTP.get(`/tax-rate-names/${this.rate_name_id}`).then((response) => {
                this.rate_name  = response.data.tax_rate_name;
                this.is_default = (response.data.default === '1');
                this.tax_number = response.data.tax_number;
            });
        },

        taxZoneFormSubmit() {
            this.validateForm();

            if (this.form_errors.length) {
                window.scrollTo({
                    top: 10,
                    behavior: 'smooth'
                });

                return;
            }

            this.$store.dispatch('spinner/setSpinner', true);

            var rest, url, msg;

            if (this.is_update) {
                rest = 'put';
                url = `/tax-rate-names/${this.rate_name_id}`;
                msg = 'Tax Zone Updated!';
            } else {
                rest = 'post';
                url = `/tax-rate-names`;
                msg = 'Tax Zone Created!';
            }

            HTTP[rest](url, {
                tax_rate_name: this.rate_name,
                tax_number   : this.tax_number,
                default      : this.is_default
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

            if (!this.rate_name) {
                this.form_errors.push('Tax Zone Name is required.');
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
