<template>
    <p class="erp-form-input-hint" v-if="isEnableDescription(input)" v-html="input.desc"></p>
</template>

<script>
export default {
    name: "InputDesc",

    props: {
        input: {
            type    : Object,
            required: true
        }
    },

    methods: {
        /**
         * Check if description will be enable for input
         */
        isEnableDescription ( input ) {
            return input.desc && input.desc.length > 0 && ! input.tooltip
        },
    },
}
</script>
