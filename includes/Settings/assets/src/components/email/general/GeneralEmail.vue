<template>
    <base-layout
        section_id="erp-email"
        sub_section_id="general"
        :enable_content="true"
    ></base-layout>
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: 'GeneralEmail',

    components: {
        BaseLayout,
    },
}
</script>
