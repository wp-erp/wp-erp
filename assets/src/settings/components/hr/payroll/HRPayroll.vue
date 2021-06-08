<template>
    <base-layout
        section_id="erp-hr"
        sub_section_id="payroll"
        :enable_content="true"
    />
</template>

<script>
import BaseLayout from "settings/components/layouts/BaseLayout.vue";

export default {
    name: "HRPayroll",

    components: {
        BaseLayout,
    },
};
</script>
