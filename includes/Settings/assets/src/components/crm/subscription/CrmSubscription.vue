<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="subscription"
        :enable_content="true"
        :single_option="false"
    />
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "CrmSubscription",

    components: {
        BaseLayout,
    },
};
</script>
