<template>
    <base-layout
        section_id="erp-ac"
        sub_section_id="customers"
        :enable_content="true"
    />
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "AcCustomer",

    components: {
        BaseLayout,
    },
};
</script>
