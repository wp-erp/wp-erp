<template>
    <ac-payment
        :single_option="true"
        sub_sub_section="paypal"
    />
</template>

<script>
import AcPayment from "settings/components/act/payment/AcPayment.vue";

export default {
    name: "AcPaymentPaypal",

    components: {
        AcPayment
    }
};
</script>
