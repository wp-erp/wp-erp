<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="contacts"
        :enable_content="true"
        :single_option="false"
    />
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "CrmContacts",

    components: {
        BaseLayout,
    },
};
</script>
