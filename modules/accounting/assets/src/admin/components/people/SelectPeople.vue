<template>
    <div class="wperp-form-group expense-people with-multiselect">
        <people-modal v-if="showModal" title="Add new people" type="all"></people-modal>
        <label>{{label}}<span class="wperp-required-sign">*</span></label>
        <multi-select v-model="selected" :options="options" />

        <!--<a href="#" class="add-new-people" @click="showModal = true"><i class="flaticon-add-plus-button"></i>Add new</a>-->
    </div>
</template>

<script>
import { mapState } from 'vuex';

import HTTP from 'admin/http';
import PeopleModal from 'admin/components/people/PeopleModal.vue';
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'SelectPeople',

    components: {
        PeopleModal,
        MultiSelect
    },

    props: {
        value: {
            type: [String, Object, Array],
            default: ''
        },

        reset: {
            type: Boolean,
            default: false
        },

        label: {
            type: String,
            default: 'Pay to'
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
        options: state => state.expense.people
    }),

    created() {
        this.$store.dispatch('expense/fetchPeople');

        this.$root.$on('options-query', query => {
            if (query) {
                this.getPeople(query);
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
        getPeople(query) {
            HTTP.get('/people', {
                params: {
                    type: [],
                    search: query
                }
            }).then(response => {
                this.$store.dispatch('expense/fillPeople', response.data);
            });
        }
    }

};
</script>

<style lang="less">
    .expense-people.with-multiselect {
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
