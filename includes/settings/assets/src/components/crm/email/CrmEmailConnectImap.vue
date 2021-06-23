<template>
    <base-layout
        section_id="erp-crm"
        sub_section_id="email_connect"
        sub_sub_section_id="imap"
        :enable_content="true"
        :single_option="false"
    />
</template>

<script>
import BaseLayout from "../../layouts/BaseLayout.vue";

export default {
    name: "CrmEmailConnectImap",

    components: {
        BaseLayout
    }
};
</script>
