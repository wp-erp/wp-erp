<template>
    <base-layout
        section_id="erp-hr"
        sub_section_id="leave"
        :enable_content="true"
    />
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "HRLeave",

    components: {
        BaseLayout,
    },
};
</script>
