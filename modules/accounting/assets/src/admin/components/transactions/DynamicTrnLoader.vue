<template></template>

<script>
import HTTP from 'admin/http';

export default {
    name: 'DynamicTrnLoader',

    data() {
        return {
            voucher_no: null,
            voucher_type: null
        };
    },

    mounted() {
        this.voucher_no = this.$route.params.id;

        HTTP.get(`/transactions/voucher-type/${this.voucher_no}`).then((response) => {
            this.voucher_type = response.data;

            if (this.voucher_type === 'invoice' || this.voucher_type === 'estimate' || this.voucher_type === 'payment' || this.voucher_type === 'return_payment') {
                this.$router.push({ name: 'SalesSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'bill') {
                this.$router.push({ name: 'BillSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'pay_bill') {
                this.$router.push({ name: 'PayBillSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'purchase' || this.voucher_type === 'purchase_order') {
                this.$router.push({ name: 'PurchaseSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'pay_purchase' || this.voucher_type === 'receive_pay_purchase') {
                this.$router.push({ name: 'PayPurchaseSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'expense') {
                this.$router.push({ name: 'ExpenseSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'check') {
                this.$router.push({ name: 'CheckSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'journal') {
                this.$router.push({ name: 'JournalSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'transfer_voucher') {
                this.$router.push({ name: 'SingleTransfer', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'tax_payment') {
                this.$router.push({ name: 'PayTaxSingle', params: { id: this.voucher_no } });
            }
            if (this.voucher_type === 'people_trn') {
                this.$router.push({ name: 'PeopleTrnSingle', params: { id: this.voucher_no } });
            }
        });
    }
};
</script>
