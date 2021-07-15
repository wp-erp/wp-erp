<template>
    <base-layout
        section_id="general"
        sub_section_id="general"
        :enable_content="true"
        >
    </base-layout>
</template>

<script>
import BaseLayout from '../layouts/BaseLayout.vue';

export default {
    name: 'GeneralSettings',

    components: {
        BaseLayout
    }
}
</script>
