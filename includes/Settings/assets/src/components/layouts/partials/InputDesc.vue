<template>
    <p class="erp-form-input-hint" v-if="isEnableDescription(input)" v-html="desc"></p>
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

    computed: {
        desc() {
            // Descriptions arrive slash-escaped. Only double quotes were being
            // unescaped, so any description containing an apostrophe rendered the
            // backslash — "every user\'s own choice" on the HR settings screen.
            return this.input.desc.replace(/\\(["'])/g, '$1');
        }
    },

    methods: {
        /**
         * Check if description will be enable for input
         */
        isEnableDescription ( input ) {
            return input.desc && input.desc.length > 0 && ! input.tooltip && input.type !== 'checkbox'
        }
    },
}
</script>
