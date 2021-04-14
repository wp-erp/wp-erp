<template>
    <div id="wperp-tax-rate-modal" class="wperp-modal has-form wperp-modal-open" role="dialog">
        <div class="wperp-modal-dialog">
            <div class="wperp-modal-content">
                <!-- modal body title -->
                <div class="wperp-modal-header">
                    <h3>{{tax_rate.tax_rate_name}}</h3>
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
import HTTP from 'admin/http';
import TaxRateRow from 'admin/components/tax/TaxRateRow.vue';

export default {
    name: 'TaxRateLineEdit',

    components: {
        TaxRateRow
    },

    props: {
        tax_id: {
            type: [Number, String]
        },
        row_data: {
            type: [Object]
        }
    },

    data() {
        return {
            tax_rate: {},
            agency: '',
            category: '',
            isWorking: false,
            agencies: [],
            categories: []
        };
    },

    created() {
        this.fetchData();
    },

    methods: {
        closeModal: function() {
            this.$emit('close');
        },

        fetchData() {
            const taxid = this.tax_id;

            HTTP.get(`/taxes/${taxid}`).then((response) => {
                this.tax_rate = response.data;
            }).catch(error => {
                throw error;
            });

            HTTP.get('/tax-agencies').then((response) => {
                this.agencies = [];
                this.agencies = response.data;
            }).catch(error => {
                throw error;
            });

            HTTP.get('/tax-cats').then((response) => {
                this.categories = [];
                this.categories = response.data;
            }).catch(error => {
                throw error;
            });
        }
    }
};
</script>

<style lang="less" scoped>
    .wperp-modal-dialog {
        max-width: 900px!important;
        margin: 50px auto;
    }

    .wperp-modal .wperp-modal-content  {
       min-height: 50vh !important;
    }

    .wperp-modal-header {
        padding: 30px 0 20px 40px !important;
    }

    .wperp-modal span.modal-close {
       line-height: 3 !important;
   }
</style>
