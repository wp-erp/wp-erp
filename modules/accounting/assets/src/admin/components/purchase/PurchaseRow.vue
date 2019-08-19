<template>
    <tr>
        <th scope="row" class="col--check with-multiselect column-primary product-select">
            <multi-select v-model="line.selectedProduct" :options="products" @input="setProductInfo" />
        </th>
        <td class="col--qty">
            <input min="0" type="number"
                   v-model="line.qty"
                   @keyup="calculateAmount"
                   name="qty"
                   class="wperp-form-field" :required="line.selectedProduct ? true : false">
        </td>
        <td class="col--uni_price" data-colname="Unit Price">
            <input min="0" type="number" v-model="line.unitPrice"
                @keyup="calculateAmount"
                class="wperp-form-field text-right" :required="line.selectedProduct ? true : false">
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="number" min="0" step="0.01" v-model="line.amount" class="wperp-form-field text-right" readonly>
        </td>
        <td class="col--actions delete-row" data-colname="Action">
            <span class="wperp-btn" @click="removeRow"><i class="flaticon-trash"></i></span>
        </td>
    </tr>
</template>

<script>
import MultiSelect from 'admin/components/select/MultiSelect.vue';

export default {
    name: 'PurchaseRow',

    props: {
        products: {
            type: Array,
            default: () => []
        },

        line: {
            type: Object,
            default: () => {}
        }
    },

    components: {
        MultiSelect
    },

    created() {
        // check if editing
        if (this.$route.params.id) {
            this.prepareRowEdit(this.line);
        }
    },

    methods: {
        prepareRowEdit(row) {
            row.unitPrice = row.cost_price;
            row.selectedProduct = { id: parseInt(row.product_id), name: row.name };

            this.calculateAmount();
        },

        setProductInfo() {
            this.line.qty = 1;

            if (this.$route.params.id) {
                this.line.unitPrice = this.line.selectedProduct.cost_price;
            } else {
                this.line.unitPrice = this.line.selectedProduct.unitPrice;
            }

            this.calculateAmount();
        },

        getAmount() {
            if (!this.line.qty) {
                this.line.qty = 0;
            }

            if (!this.line.qty || !this.line.unitPrice) {
                this.line.amount = 0;

                return false;
            }

            // Set Amount
            return parseInt(this.line.qty) * parseFloat(this.line.unitPrice);
        },

        calculateAmount() {
            const amount = this.getAmount();
            if (!amount) return;

            this.line.amount = amount.toFixed(2);

            // Send amount to parent for total calculation
            this.$root.$emit('total-updated', this.line.amount);
            this.$forceUpdate(); // why? should use computed? or vue.set()?
        },

        removeRow() {
            this.$root.$emit('remove-row', this.$vnode.key);
        }
    }
};
</script>

<style lang="less" scoped>
    .product-select {
        font-weight: normal !important;
    }
</style>
