<template>
    <div class="wperp-modal-dialog sales-report">
        <div class="wperp-modal-content">
            <div class="wperp-modal-header">
                <h4>Invoice</h4>
                <div class="d-print-none">
                    <a href="#" class="wperp-btn btn--default print-btn" @click.prevent="printPopup">
                        <i class="flaticon-printer-1"></i>
                        &nbsp; Print
                    </a>
                    <!-- todo: more action has some dropdown and will implement later please consider as planning -->
                    <a href="#" class="wperp-btn btn--default">
                        <i class="flaticon-settings-work-tool"></i>
                        &nbsp; More Action
                    </a>
                </div>
            </div>

            <sales-single-content 
                v-if="null !== invoice"
                :content="invoice"
                content-type="Invoice" />

            <sales-single-content 
                v-if="null !== payment"
                :content="payment"
                content-type="Payment" />

        </div>
    </div>
</template>

<script>
    import HTTP from 'admin/http';
    import SalesSingleContent from 'admin/components/transactions/sales/SalesSingleContent.vue';

    export default {
        name: 'SalesSingle',

        data() {
            return {
                isWorking: false,
                invoice  : null,
                payment  : null,
                type     : null,
                acct_var : erp_acct_var,
            }
        },

        components: {
            SalesSingleContent
        },

        created() {
            /* If this page load directly, 
            then we don't have the type or type is `undefined`
            thats why wee need to load the type from database */
            let params = this.$route.params;

            if ( typeof params.type === 'undefined' ) {
                this.getSalesType(params.id);
            } else {
                this.loadData(params.type);
            }
        },

        methods: {
            getSalesType(id) {
                HTTP.get(`/transactions/type/${id}`).then(response => {
                    this.loadData(response.data);
                }).then( (e) => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            loadData(type) {
                this.type = type;

                if ( 'sales_invoice' === type ) {
                    this.getInvoice();
                } else if ( 'payment' === type ) {
                    this.getPayment();
                }
            },

            getInvoice() {
                this.isWorking = true;

                HTTP.get(`/invoices/${this.$route.params.id}`).then(response => {                    
                    this.invoice = response.data;
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            getPayment() {
                this.isWorking = true;

                HTTP.get(`/payments/${this.$route.params.id}`).then(response => {
                    console.log(response.data);

                    this.payment = response.data;                    
                }).then( e => {} ).then(() => {
                    this.isWorking = false;
                });
            },

            printPopup() {
                window.print();
            }
        },

    }
</script>

<style lang="less">
    .sales-report {
        width: 800px;
        margin: 40px 0;
    }

    @media print {
        .erp-nav-container {
            display: none;
        }
    }
</style>

