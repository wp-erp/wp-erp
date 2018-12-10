<template>
    <tr>
        <th scope="row" class="col--check">
            <select name="pen-holder" id="pen-holder" class="wperp-form-field wperp-is-select2">
                <option value="0">Select</option>
                <option value="1">Pen Holder</option>
                <option value="2">Pen Holder</option>
            </select>
        </th>
        <td class="col--qty column-primary">
            <input type="number" name="qty" id="qty" class="wperp-form-field" value="1">
        </td>
        <td class="col--uni_price" data-colname="Unit Price">
            <input type="text" name="unit-price" id="unit-price" class="wperp-form-field" value="5000">
        </td>
        <td class="col--discount" data-colname="Discount">
            <div class="wperp-has-addon">
                <input type="text" name="discount" id="discount" class="wperp-form-field" value="1">
                <span class="wperp-addon">%</span>
            </div>
        </td>
        <td class="col--penholder" data-colname="Tax(%)">
            <div class="wperp-custom-select">
                <select name="pen-holder" id="pen-holder" class="wperp-form-field">
                    <option value="0">Select</option>
                    <option value="1">Gov</option>
                    <option value="2">Private</option>
                </select>
                <i class="flaticon-arrow-down-sign-to-navigate"></i>
            </div>
        </td>
        <td class="col--tax-amount" data-colname="Tax Amount">
            <input type="text" name="tax-amount" id="tax-amount" class="wperp-form-field" value="$240.00">
        </td>
        <td class="col--amount" data-colname="Amount">
            <input type="text" name="amount" id="amount" class="wperp-form-field" value="$24022">
        </td>
        <td class="col--actions delete-row" data-colname="Action">
            <span class="wperp-btn"><i class="flaticon-trash"></i></span>
        </td>
    </tr>
</template>

<script>
    export default {
        name: 'TransactionRow',

        components: {

        }
    }
</script>

<style lang="less">

</style>