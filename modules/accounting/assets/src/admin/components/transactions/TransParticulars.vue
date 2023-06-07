<template>
    <div class="particulars" v-if="particulars">
        <h4>{{ heading ? heading : __('Particulars', 'erp') }}</h4>
        <p v-for="(particular, par) in particulars.split(/\r?\n/)" :key="par" v-html="shouldRenderHTML(particular)"></p>
    </div>
</template>

<script>
export default {
    name: 'TransParticulars',

    props: {
        particulars: String,
        heading: String
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

<style lang="less">
.particulars {
    padding: 15px 0 15px 0;
    border: 1px solid rgba(38,50,56, .1);
    border-left: 0;
    border-right: 0;

    p {
        margin-bottom: 0;
    }
    pre {
        background: #f4f4f4;
        border: 1px solid #ddd;
        border-left: 3px solid #f36d33;
        color: #666;
        page-break-inside: avoid;
        font-family: monospace;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 1.6em;
        max-width: 100%;
        overflow: auto;
        padding: 1em 1.5em;
        display: block;
        word-wrap: break-word;
    }
    blockquote {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-left: 10px solid #ccc;
        color: #666;
        page-break-inside: avoid;
        font-family: monospace;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 1.6em;
        max-width: 100%;
        overflow: auto;
        padding: 1em 1.5em;
        display: block;
        word-wrap: break-word;
    }
    ul {
        list-style-type: decimal;
        margin-left: 2em;
    }
    ul li {
        list-style-type: circle;
    }
}
</style>
