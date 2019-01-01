<template>
    <div class="wperp-form-group invoice-customers with-multiselect">
        <label for="vendor">vendor<span class="wperp-required-sign">*</span></label>
        <multi-select v-model="selected" :options="options" />

        <a href="#" class="add-new-customer"><i class="flaticon-add-plus-button"></i>Add new</a>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'

    export default {
        name: 'SelectVendors',

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
                this.options = [];

                if ( query ) {
                    this.getvendors(query);
                }
            } );
        },

        watch: {
            selected() {
                this.$emit('input', this.selected);
            }
        },

        methods: {
            getvendors(query) {
                HTTP.get('/vendors', {
                    params: {
                        search: query
                    }
                }).then((response) => {
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
    .invoice-customers.with-multiselect {
        .multiselect__input,
        .multiselect__single {
            min-height: 30px;
            line-height: 30px;
            margin-bottom: 0;
        }

        .multiselect__tags {
            padding: 8px 0;
        }

        .multiselect__placeholder {
            margin: 4px 0 0 7px !important;
        }

        .multiselect__select {
            height: 41px;
        }
    }
</style>
