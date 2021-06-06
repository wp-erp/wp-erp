<template>
    <base-layout
        section_id="erp-hr"
        sub_section_id="leave"
        :enable_content="true"
    />
</template>

<script>
import BaseLayout from "settings/components/layouts/BaseLayout.vue";

export default {
    name: "HRLeave",

    components: {
        BaseLayout,
    },
};
</script>
