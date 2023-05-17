<template>
    <div class="particulars" v-if="particulars">
        <h4>{{ __('Particulars', 'erp') }}</h4>
        <p v-for="(particular, par) in particulars.split(/\r?\n/)" :key="par" v-html="shouldRenderHTML(particular)"></p>
    </div>
</template>

<script>
export default {
    name: 'TransParticulars',

    props: {
        particulars: String
    },
    methods: {
        shouldRenderHTML(particular) {
            // Check if the particular string contains HTML tags
            const hasHTML = /<[a-z][\s\S]*>/i.test(particular);

            if (hasHTML) {
                return particular;
            } else {
                // Escape the HTML tags to prevent rendering them as HTML
                return this.escapeHTML(particular);
            }
        },
        escapeHTML(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
};
</script>

<style lang="less" scoped>
.particulars {
    padding: 15px 0 15px 0;
    border: 1px solid rgba(38,50,56, .1);
    border-left: 0;
    border-right: 0;

    p {
        margin-bottom: 0;
    }
}
</style>
