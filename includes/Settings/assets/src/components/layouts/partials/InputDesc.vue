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
            return this.input.desc.replace(/\\"/g, '"');
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
