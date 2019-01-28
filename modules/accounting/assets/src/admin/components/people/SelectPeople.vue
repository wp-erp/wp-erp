<template>
    <div class="wperp-form-group bill-people with-multiselect">
        <label>People<span class="wperp-required-sign">*</span></label>
        <multi-select v-model="selected" :options="options" />
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import axios from 'axios'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {
        name: 'SelectPeople',

        components: {
            MultiSelect
        },

        data() {
            return {
                selected: [],
                options: [],
            }
        },

        created() {
            this.$root.$on( 'options-query', query => {
                if ( query ) {
                    this.getPeople(query);
                }
            } );
        },

        watch: {
            selected() {
                this.$emit('input', this.selected);
            }
        },

        methods: {
            getPeople(query) {
                let response = {};

                HTTP.get('/people', {
                    params: {
                        search: query
                    }
                }).then(response => {
                    this.options = [];

                    response.data.forEach(item => {
                        this.options.push({
                            id: item.id,
                            name: item.first_name + ' ' + item.last_name
                        });
                    });
                });

            },

        }
    }
</script>

<style lang="less">
    .bill-people.with-multiselect {
        .multiselect__input,
        .multiselect__single {
            min-height: 24px;
            line-height: 24px;
            margin-bottom: 0;
        }

        .multiselect__tags {
            padding: 5px 0;
        }

        .multiselect__placeholder {
            margin: 4px 0 0 7px !important;
        }

        .multiselect__select {
            height: 24px;
        }

        .multiselect__option {
            display: block;
            padding: 12px;
            min-height: 24px;
            line-height: 16px;
            text-decoration: none;
            text-transform: none;
            vertical-align: middle;
            position: relative;
            cursor: pointer;
            white-space: nowrap;
        }
    }
</style>
