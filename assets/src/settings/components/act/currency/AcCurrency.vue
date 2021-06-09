<template>
    <base-layout
        section_id="erp-ac"
        sub_section_id="currency_option"
        :enable_content="true"
    />
</template>

<script>
import BaseLayout from "settings/components/layouts/BaseLayout.vue";

export default {
    name: "AcCurrency",

    components: {
        BaseLayout,
    },
};
</script>
