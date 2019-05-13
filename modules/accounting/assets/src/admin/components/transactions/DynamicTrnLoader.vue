<template>
    
</template>

<script>
    import HTTP from 'admin/http'

    export default {
        name: "DynamicTrnLoader",

        components: {
            HTTP
        },

        data() {
            return {
                voucher_no: null,
                voucher_type: null
            }
        },

        mounted() {
            this.voucher_no = this.$route.params.id;

            HTTP.get( `/transactions/voucher-type/${this.voucher_no}` ).then( (response) => {
                this.voucher_type = response.data;

                if ( 'invoice' === this.voucher_type || 'estimate' === this.voucher_type || 'payment' === this.voucher_type ) {
                    this.$router.push({name: 'SalesSingle', params: {id: this.voucher_no}});
                }
                if ( 'bill' === this.voucher_type ) {
                    this.$router.push({name: 'BillSingle', params: {id: this.voucher_no}});
                }
                if ( 'pay_bill' === this.voucher_type ) {
                    this.$router.push({name: 'PayBillSingle', params: {id: this.voucher_no}});
                }
                if ( 'purchase' === this.voucher_type || 'purchase_order' === this.voucher_type) {
                    this.$router.push({name: 'PurchaseSingle', params: {id: this.voucher_no}});
                }
                if ( 'pay_purchase' === this.voucher_type ) {
                    this.$router.push({name: 'PayPurchaseSingle', params: {id: this.voucher_no}});
                }
                if ( 'expense' === this.voucher_type ) {
                    this.$router.push({name: 'ExpenseSingle', params: {id: this.voucher_no}});
                }
                if ( 'check' === this.voucher_type ) {
                    this.$router.push({name: 'CheckSingle', params: {id: this.voucher_no}});
                }
                if ( 'journal' === this.voucher_type ) {
                    this.$router.push({name: 'JournalSingle', params: {id: this.voucher_no}});
                }
                if ( 'transfer_voucher' === this.voucher_type ) {
                    this.$router.push({name: 'SingleTransfer', params: {id: this.voucher_no}});
                }
                if ( 'tax_payment' === this.voucher_type ) {
                    this.$router.push({name: 'PayTaxSingle', params: {id: this.voucher_no}});
                }
            });
        },
    }
</script>

<style scoped>

</style>
