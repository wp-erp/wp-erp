<template>
    <div id="wperp-tax-rate-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{tax_rate.tax_name}}</h3>
                    <span class="modal-close" @click.prevent="closeModal"><i class="flaticon-close"></i></span>
                </div>

                <div class="wperp-invoice-table">
                    <div class="wperp-panel-body">
                        <tax-rate-row
                            :index="row_data.id"
                            :component_line="row_data"
                            :agencies="agencies"
                            :categories="categories"
                        />
                    </div>

                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import HTTP from 'admin/http'
    import MultiSelect from 'admin/components/select/MultiSelect.vue'
    import TaxRateRow from 'admin/components/tax/TaxRateRow.vue'

    export default {
        name: 'TaxRateLineEdit',

        components: {
            HTTP,
            MultiSelect,
            TaxRateRow
        },

        props: {
            tax_id: {
                type: [ Number, String ]
            },
            row_data: {
                type: [ Object ]
            },
        },

        data() {
            return {
                tax_rate: {},
                agency: '',
                category: '',
                isWorking: false,
                agencies: [],
                categories: [],
            };
        },

        created() {
            this.fetchData();
        },

        methods: {
            closeModal: function(){
                this.$emit('close');
            },

            fetchData() {
                let taxid = this.tax_id;

                HTTP.get(`/taxes/${taxid}`).then((response) => {
                    this.tax_rate = response.data;
                }).catch((error) => {
                    console.log(error);
                });

                HTTP.get('/tax-agencies').then((response) => {
                    this.agencies = [];
                    response.data.forEach(element => {
                        this.agencies.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                }).catch((error) => {
                    console.log(error);
                });

                HTTP.get('/tax-cats').then((response) => {
                    this.categories = [];
                    response.data.forEach(element => {
                        this.categories.push({
                            id: element.id,
                            name: element.name
                        });
                    });
                }).catch((error) => {
                    console.log(error);
                });
            },
        },
    }
</script>
<style lang="less">
    .wperp-modal-dialog {
        max-width: 1000px !important;
    }
</style>
