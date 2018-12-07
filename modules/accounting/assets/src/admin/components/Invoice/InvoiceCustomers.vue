<template>
    <div class="wperp-col-sm-4">
        <div class="wperp-form-group with-multiselect">
            <label for="customer">Customer<span class="wperp-required-sign">*</span></label>
            <multi-select v-model="selected" :options="options" />

            <a href="#" class="add-new-customer"><i class="flaticon-add-plus-button"></i>Add new</a>
        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/Select/MultiSelect.vue'

    export default {
        name: 'InvoiceCustomers',

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
                    this.getCustomers(query);
                }
            } );
        },

        methods: {
            getCustomers(query) {
                HTTP.get('/customers', {
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
            }
        }
    }
</script>

<style lang="less">
    .with-multiselect {
        .multiselect__input,
        .multiselect__single {
            min-height: 30px;
            line-height: 30px;
            margin-bottom: 0;
        }

        .multiselect__placeholder {
            margin: 4px 0 0 4px !important;
        }
    }
</style>