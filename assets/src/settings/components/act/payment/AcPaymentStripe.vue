<template>
    <ac-payment
        :single_option="true"
        sub_sub_section="stripe"
    />
</template>

<script>
import AcPayment from "settings/components/act/payment/AcPayment.vue";

export default {
    name: "AcPaymentStripe",

    components: {
        AcPayment
    }
};
</script>
