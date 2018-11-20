<template>
    <div :class="classes" :style="styles">

        <ul class='subsubsub'>\n

        <slot name="listheader-content"></slot>

            implode( " |</li>\n", $views ) . "</li>\n";
        </ul>;


    </div>

</template>

<script>
    export default{
        name: 'ListHeader',
        props: {
            title : String,
            closable: Boolean
        },
        data () {
            return {
                closed: false,
            };
        },
        computed: {
            classes () {
                return [
                    'postbox',
                    this.closed ? 'closed' : ''
                ];
            },
            styles () {
                return 'display: block;';
            }
        },
        methods: {
            handleToggle(event) {
                this.closed = !this.closed;
                this.$emit('metaboxToggle', event);
            }
        },
    }
</script>

<style scoped>

</style>
