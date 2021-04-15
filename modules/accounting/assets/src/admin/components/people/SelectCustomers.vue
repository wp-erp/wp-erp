<template>
    <div class="wperp-form-group invoice-customers with-multiselect">
        <people-modal v-if="showModal" title="Add new customer" type="customer"></people-modal>
        <label>{{ __('Customer', 'erp') }}<span class="wperp-required-sign">*</span></label>
        <multi-select v-model="selected" :options="options" />

        <a href="#" class="add-new-customer" @click="showModal = true">
            <i class="flaticon-add-plus-button"></i>{{ __('Add new', 'erp') }}
        </a>
    </div>
</template>

<script>
import { mapState } from 'vuex';

import HTTP from 'admin/http';
import MultiSelect from 'admin/components/select/MultiSelect.vue';
import PeopleModal from 'admin/components/people/PeopleModal.vue';

export default {
    name: 'SelectCustomers',

    components: {
        MultiSelect,
        PeopleModal
    },

    props: {
        value: {
            type: [String, Object, Array],
            default: ''
        },

        reset: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            selected: null,
            showModal: false
        };
    },

    watch: {
        value(newVal) {
            this.selected = newVal;
        },

        selected() {
            this.$emit('input', this.selected);
        },

        reset() {
            this.selected = [];
        }
    },

    computed: mapState({
        options: state => state.sales.customers
    }),

    created() {
        this.$store.dispatch('sales/fetchCustomers');

        this.$root.$on('options-query', query => {
            if (query) {
                this.getCustomers(query);
            }
        });

        this.$on('modal-close', () => {
            this.showModal = false;
            this.people = null;
        });

        this.$root.$on('peopleUpdate', () => {
            this.showModal = false;
        });
    },

    methods: {
        getCustomers(query) {
            HTTP.get('/people', {
                params: {
                    type: 'customer',
                    search: query
                }
            }).then(response => {
                this.$store.dispatch('sales/fillCustomers', response.data);
            });
        }
    }

};
</script>

<style lang="less">
    .invoice-customers.with-multiselect {
        .multiselect__input,
        .multiselect__single {
            min-height: 30px;
            line-height: 30px;
            margin-bottom: 0;
        }

        .multiselect__placeholder {
            margin: 4px 0 0 7px !important;
        }
    }
</style>
